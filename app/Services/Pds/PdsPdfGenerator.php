<?php

declare(strict_types=1);

namespace App\Services\Pds;

use App\Models\ApplicantProfile;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Produces the PDS as a PDF by converting the filled workbook with headless
 * LibreOffice, so the PDF and the .xlsx are the same document rendered once.
 *
 * PhpSpreadsheet's built-in PDF writers were tried and rejected: rendering via
 * HTML drops all 35 form-control checkboxes and paginates the form to 7 pages
 * instead of 4.
 */
class PdsPdfGenerator
{
    public function __construct(
        private readonly PdsExcelGenerator $excelGenerator,
    ) {}

    /** Writes the PDF to $destination and returns that path. */
    public function generate(ApplicantProfile $profile, string $destination): string
    {
        $workbook = $this->temporaryPath('xlsx');

        try {
            $this->excelGenerator->generate($profile, $workbook);

            return $this->convert($workbook, $destination);
        } finally {
            if (is_file($workbook)) {
                unlink($workbook);
            }
        }
    }

    /** Converts an existing .xlsx to PDF at $destination. */
    public function convert(string $workbook, string $destination): string
    {
        $binary = $this->sofficeBinary();

        if ($binary === null) {
            throw new RuntimeException(
                'LibreOffice was not found, so the PDS cannot be rendered as PDF. '
                .'Install LibreOffice on this host, or set PDS_SOFFICE_PATH to the soffice binary. '
                .'The .xlsx download is unaffected.'
            );
        }

        $workingDirectory = $this->temporaryDirectory();

        try {
            $process = new Process([
                $binary,
                '--headless',
                '--norestore',
                // A per-run profile lets concurrent conversions coexist; without
                // it LibreOffice serialises on a shared user profile lock.
                '-env:UserInstallation=file:///'.str_replace('\\', '/', $workingDirectory).'/profile',
                '--convert-to', 'pdf',
                '--outdir', $workingDirectory,
                $workbook,
            ]);

            $process->setTimeout((float) config('pds.conversion_timeout', 120));

            try {
                $process->run();
            } catch (ProcessTimedOutException) {
                throw new RuntimeException('LibreOffice timed out converting the PDS to PDF.');
            }

            $produced = $workingDirectory.DIRECTORY_SEPARATOR
                .pathinfo($workbook, PATHINFO_FILENAME).'.pdf';

            if (! $process->isSuccessful() || ! is_file($produced)) {
                throw new RuntimeException(
                    'LibreOffice failed to convert the PDS to PDF: '
                    .trim($process->getErrorOutput() ?: $process->getOutput())
                );
            }

            $directory = dirname($destination);
            if (! is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            if (! rename($produced, $destination)) {
                copy($produced, $destination);
                unlink($produced);
            }

            return $destination;
        } finally {
            $this->deleteDirectory($workingDirectory);
        }
    }

    public function isAvailable(): bool
    {
        return $this->sofficeBinary() !== null;
    }

    /** Resolves the LibreOffice binary, or null when it is not installed. */
    public function sofficeBinary(): ?string
    {
        $configured = config('pds.soffice_path');

        if (is_string($configured) && $configured !== '') {
            return is_file($configured) || $this->onPath($configured) ? $configured : null;
        }

        foreach ((array) config('pds.soffice_candidates', []) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }

            if (! str_contains($candidate, DIRECTORY_SEPARATOR) && ($found = $this->onPath($candidate))) {
                return is_string($found) ? $found : $candidate;
            }
        }

        return null;
    }

    private function onPath(string $binary): string|bool|null
    {
        return (new ExecutableFinder)->find($binary);
    }

    private function temporaryPath(string $extension): string
    {
        $directory = storage_path('app/pds-tmp');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        return $directory.DIRECTORY_SEPARATOR.'pds-'.bin2hex(random_bytes(8)).'.'.$extension;
    }

    private function temporaryDirectory(): string
    {
        $directory = storage_path('app/pds-tmp/'.bin2hex(random_bytes(8)));

        mkdir($directory, 0775, true);

        return $directory;
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($directory);
    }
}
