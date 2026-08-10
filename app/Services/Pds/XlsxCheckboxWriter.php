<?php

declare(strict_types=1);

namespace App\Services\Pds;

use RuntimeException;
use ZipArchive;

/**
 * Ticks Excel form-control checkboxes in a saved .xlsx.
 *
 * PhpSpreadsheet preserves the template's 35 form controls but offers no API to
 * check them, and none of them has an fmlaLink, so there is no cell to write.
 * Their state lives in the VML shape, so this reopens the archive and injects
 * <x:Checked>1</x:Checked> into the <x:ClientData> of each target shape.
 *
 * Shapes are located by their <x:Anchor>. Sheets are resolved through their
 * legacyDrawing relationship rather than by vmlDrawingN.vml filename, because
 * PhpSpreadsheet renumbers drawing parts when it saves.
 */
class XlsxCheckboxWriter
{
    /**
     * @param  array<string, list<string>>  $anchorsBySheet  sheet name => anchors to tick
     */
    public function tick(string $path, array $anchorsBySheet): void
    {
        $anchorsBySheet = array_filter($anchorsBySheet, fn (array $anchors) => $anchors !== []);

        if ($anchorsBySheet === []) {
            return;
        }

        /**
         * ZipArchive::close() commits by renaming a temp file over the original,
         * which Windows refuses while any other handle to it is open. A failed
         * close also invalidates the object, so the whole open/modify/close
         * cycle is retried rather than just the close.
         */
        for ($attempt = 1; ; $attempt++) {
            if ($this->attempt($path, $anchorsBySheet)) {
                return;
            }

            if ($attempt >= 3) {
                throw new RuntimeException(
                    "Unable to commit checkbox changes to {$path}; the file is locked by another process."
                );
            }

            gc_collect_cycles();
            usleep(150_000);
        }
    }

    /**
     * One open/modify/close cycle. Returns false only when the commit itself
     * failed — a malformed workbook still throws, since retrying cannot help.
     *
     * @param  array<string, list<string>>  $anchorsBySheet
     */
    private function attempt(string $path, array $anchorsBySheet): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open workbook for checkbox writing: {$path}");
        }

        try {
            $sheetParts = $this->resolveSheetParts($zip);

            foreach ($anchorsBySheet as $sheetName => $anchors) {
                $vmlPart = $sheetParts[$sheetName] ?? null;

                if ($vmlPart === null) {
                    throw new RuntimeException("No VML drawing found for sheet {$sheetName}.");
                }

                $vml = $zip->getFromName($vmlPart);

                if ($vml === false) {
                    throw new RuntimeException("VML part {$vmlPart} missing from workbook.");
                }

                $zip->addFromString($vmlPart, $this->check($vml, $anchors, $sheetName));
            }
        } catch (\Throwable $e) {
            @$zip->close();

            throw $e;
        }

        return @$zip->close();
    }

    /**
     * Map each worksheet name to the VML part holding its form controls, via
     * workbook.xml -> sheet rels -> legacyDrawing.
     *
     * @return array<string, string>
     */
    private function resolveSheetParts(ZipArchive $zip): array
    {
        $workbook     = (string) $zip->getFromName('xl/workbook.xml');
        $workbookRels = (string) $zip->getFromName('xl/_rels/workbook.xml.rels');

        $relTargets = [];
        preg_match_all('/<Relationship[^>]*Id="([^"]+)"[^>]*Target="([^"]+)"/', $workbookRels, $rels, PREG_SET_ORDER);
        foreach ($rels as $rel) {
            $relTargets[$rel[1]] = ltrim($rel[2], '/');
        }

        $parts = [];
        preg_match_all('/<sheet[^>]*name="([^"]+)"[^>]*r:id="([^"]+)"/', $workbook, $sheets, PREG_SET_ORDER);

        foreach ($sheets as $sheet) {
            [$all, $name, $relId] = $sheet;

            $target = $relTargets[$relId] ?? null;
            if ($target === null) {
                continue;
            }

            $sheetPath = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
            $sheetXml  = $zip->getFromName($sheetPath);

            if ($sheetXml === false || ! preg_match('/<legacyDrawing[^>]*r:id="([^"]+)"/', $sheetXml, $legacy)) {
                continue;
            }

            $sheetRelsPath = preg_replace('#([^/]+)$#', '_rels/$1.rels', $sheetPath);
            $sheetRels     = (string) $zip->getFromName($sheetRelsPath);

            if (preg_match('/<Relationship[^>]*Id="'.preg_quote($legacy[1], '/').'"[^>]*Target="([^"]+)"/', $sheetRels, $vmlRel)) {
                $parts[$name] = $this->normalisePath(dirname($sheetPath).'/'.$vmlRel[1]);
            }
        }

        return $parts;
    }

    /**
     * Inject <x:Checked>1</x:Checked> into the ClientData of every shape whose
     * anchor is in $anchors. The blank template contains no x:Checked tags, so
     * this only ever adds.
     *
     * @param  list<string>  $anchors
     */
    private function check(string $vml, array $anchors, string $sheetName): string
    {
        $remaining = array_fill_keys($anchors, true);

        $result = preg_replace_callback(
            '/<v:shape\b.*?<\/v:shape>/s',
            function (array $match) use (&$remaining): string {
                $shape = $match[0];

                if (! preg_match('/<x:Anchor>\s*([^<]+)<\/x:Anchor>/s', $shape, $found)) {
                    return $shape;
                }

                $anchor = preg_replace('/\s+/', '', $found[1]);

                if (! isset($remaining[$anchor])) {
                    return $shape;
                }

                unset($remaining[$anchor]);

                // Already ticked (defensive; the blank template never is).
                if (str_contains($shape, '<x:Checked>')) {
                    return $shape;
                }

                return str_replace('</x:ClientData>', '<x:Checked>1</x:Checked></x:ClientData>', $shape);
            },
            $vml
        );

        if ($remaining !== []) {
            throw new RuntimeException(sprintf(
                'Checkbox anchors not found on sheet %s: %s',
                $sheetName,
                implode(' | ', array_keys($remaining))
            ));
        }

        return (string) $result;
    }

    private function normalisePath(string $path): string
    {
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        return implode('/', $segments);
    }
}
