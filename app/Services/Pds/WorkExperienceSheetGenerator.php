<?php

declare(strict_types=1);

namespace App\Services\Pds;

use App\Models\ApplicantProfile;
use Illuminate\Support\Carbon;
use RuntimeException;
use ZipArchive;

/**
 * Renders the "CS Form No. 212 Attachment - Work Experience Sheet".
 *
 * Unlike the PDS, this is a Word document rather than a gridded spreadsheet, so
 * there are no cells to address. The template's table has four rows: title,
 * instructions, then two rows holding worked *sample* entries. The applicant's
 * entries replace the samples, keeping the table, borders and fonts intact.
 *
 * The document is edited as an OPC package — only word/document.xml is
 * rewritten, so styles, numbering and every other part stay byte-identical.
 */
class WorkExperienceSheetGenerator
{
    public const TEMPLATE = 'templates/CS_Form_212_Work_Experience_Sheet.docx';

    /** Arial 12pt, matching the runs already in the template. */
    private const FONT = '<w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/><w:sz w:val="24"/><w:szCs w:val="24"/>';

    public function __construct(
        private readonly PdsDataAssembler $assembler,
    ) {}

    public function generate(ApplicantProfile $profile, string $destination): string
    {
        $entries = $this->entries($profile);

        if ($entries === []) {
            throw new RuntimeException(
                'The Work Experience Sheet needs at least one work experience. '
                .'Add your work history to your profile first.'
            );
        }

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        if (! copy($this->templatePath(), $destination)) {
            throw new RuntimeException("Unable to stage the Work Experience Sheet at {$destination}.");
        }

        $this->rewriteDocument($destination, $entries);

        return $destination;
    }

    public function templatePath(): string
    {
        $path = resource_path(self::TEMPLATE);

        if (! is_file($path)) {
            throw new RuntimeException("Work Experience Sheet template missing at {$path}.");
        }

        return $path;
    }

    /**
     * Work experience, most recent first, with the sheet-only detail attached.
     *
     * @return list<array<string, string>>
     */
    private function entries(ApplicantProfile $profile): array
    {
        return array_values(array_filter(
            $this->assembler->assemble($profile)['work_experience'],
            fn (array $row) => trim($row['position_title']) !== ''
                && $row['position_title'] !== PdsDataAssembler::NOT_APPLICABLE
        ));
    }

    private function rewriteDocument(string $path, array $entries): void
    {
        for ($attempt = 1; ; $attempt++) {
            if ($this->attempt($path, $entries)) {
                return;
            }

            if ($attempt >= 3) {
                throw new RuntimeException("Unable to write the Work Experience Sheet to {$path}.");
            }

            gc_collect_cycles();
            usleep(150_000);
        }
    }

    /** One open/modify/close cycle; see XlsxCheckboxWriter for why this retries. */
    private function attempt(string $path, array $entries): bool
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open the Work Experience Sheet at {$path}.");
        }

        try {
            $xml = $zip->getFromName('word/document.xml');

            if ($xml === false) {
                throw new RuntimeException('word/document.xml missing from the template.');
            }

            $zip->addFromString('word/document.xml', $this->fill($xml, $entries));
        } catch (\Throwable $e) {
            @$zip->close();

            throw $e;
        }

        return @$zip->close();
    }

    /**
     * Replace the first sample row's content with the applicant's entries and
     * drop the second sample row.
     */
    private function fill(string $xml, array $entries): string
    {
        $rows = $this->rows($xml);

        if (count($rows) < 4) {
            throw new RuntimeException('Unexpected Work Experience Sheet layout: expected four table rows.');
        }

        // Rows 0 and 1 are the title and instructions; 2 and 3 hold the samples.
        [$sampleRow, $secondSampleRow] = [$rows[2], $rows[3]];

        $tcPr = '';
        if (preg_match('/<w:tcPr>.*?<\/w:tcPr>/s', $sampleRow, $found)) {
            $tcPr = $found[0];
        }

        $body = '';
        foreach ($entries as $index => $entry) {
            $body .= $this->entryParagraphs($entry, $index > 0);
        }

        $replacement = preg_replace(
            '/<w:tc>.*<\/w:tc>/s',
            '<w:tc>'.$tcPr.$body.'</w:tc>',
            $sampleRow,
            1
        );

        // Order matters: drop the spare sample row first, then swap the filled
        // one in, so the second replace cannot match inside new content.
        $xml = str_replace($secondSampleRow, '', $xml);

        return str_replace($sampleRow, (string) $replacement, $xml);
    }

    /** @return list<string> */
    private function rows(string $xml): array
    {
        preg_match_all('/<w:tr\b.*?<\/w:tr>/s', $xml, $matches);

        return $matches[0];
    }

    private function entryParagraphs(array $entry, bool $separate): string
    {
        $xml = $separate ? $this->paragraph('') : '';

        $agency = trim(implode(', ', array_filter([
            $this->plain($entry['department_agency']),
            $entry['agency_location'],
        ])));

        $xml .= $this->paragraph('Duration: '.$this->duration($entry));
        $xml .= $this->paragraph('Position: '.$this->plain($entry['position_title']));
        $xml .= $this->paragraph('Name of Office/Unit: '.$entry['office_unit']);
        $xml .= $this->paragraph('Immediate Supervisor: '.$entry['immediate_supervisor']);
        $xml .= $this->paragraph('Name of Agency/Organization and Location: '.$agency);
        $xml .= $this->paragraph('');

        $xml .= $this->paragraph('List of Accomplishments and Contributions (if any)', bold: true);
        $xml .= $this->paragraph('');

        foreach ($this->lines($entry['accomplishments']) as $line) {
            $xml .= $this->paragraph($line);
        }

        $xml .= $this->paragraph('');
        $xml .= $this->paragraph('Summary of Actual Duties', bold: true);
        $xml .= $this->paragraph('');
        $xml .= $this->paragraph($entry['duties']);

        return $xml;
    }

    /**
     * "The duration should include start and finish dates … month in abbreviated
     * form … and year in full. For the current position, use the word Present."
     */
    private function duration(array $entry): string
    {
        $from = $this->longDate($entry['date_from']);
        $to   = $entry['date_to'] === 'PRESENT'
            ? 'Present'
            : $this->longDate($entry['date_to']);

        return trim($from.($to !== '' ? ' – '.$to : ''));
    }

    /** The assembler hands over dd/mm/yyyy; the sheet wants "February 11, 2011". */
    private function longDate(string $value): string
    {
        if ($this->plain($value) === '') {
            return '';
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->format('F j, Y');
        } catch (\Throwable) {
            return $value;
        }
    }

    /** @return list<string> */
    private function lines(string $value): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $value) ?: []),
            fn (string $line) => $line !== ''
        ));
    }

    private function paragraph(string $text, bool $bold = false): string
    {
        $runProps = '<w:rPr>'.($bold ? '<w:b/>' : '').self::FONT.'</w:rPr>';

        return '<w:p><w:pPr><w:spacing w:after="0" w:line="240" w:lineRule="auto"/>'
            .'<w:ind w:left="720"/><w:jc w:val="both"/>'.$runProps.'</w:pPr>'
            .'<w:r>'.$runProps.'<w:t xml:space="preserve">'
            .htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            .'</w:t></w:r></w:p>';
    }

    /** The PDS prints N/A for blanks; the sheet is prose, so drop it. */
    private function plain(string $value): string
    {
        return $value === PdsDataAssembler::NOT_APPLICABLE ? '' : trim($value);
    }
}
