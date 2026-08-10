<?php

declare(strict_types=1);

namespace App\Services\Pds;

use App\Models\ApplicantProfile;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;

/**
 * Renders an applicant's PDS by filling the official CSC workbook rather than
 * rebuilding it, so every merge, border, print area and page break comes from
 * CSC's own file and the output cannot deviate from the prescribed layout.
 *
 * Checkboxes are handled separately by XlsxCheckboxWriter — see that class for
 * why they cannot be written through PhpSpreadsheet.
 */
class PdsExcelGenerator
{
    public const TEMPLATE = 'templates/CS_Form_212_Revised_2026.xlsx';

    public function __construct(
        private readonly PdsDataAssembler $assembler,
        private readonly XlsxCheckboxWriter $checkboxWriter,
    ) {}

    /** Writes the filled workbook to $destination and returns that path. */
    public function generate(ApplicantProfile $profile, string $destination): string
    {
        $data = $this->assembler->assemble($profile);
        $map  = config('pds_cell_map');

        $book = IOFactory::createReader('Xlsx')->load($this->templatePath());

        $this->writeDirect($book, $map['direct'], $data);
        $this->writeAppend($book, $map['append'], $data);
        $this->writeTables($book, $map['tables'], $data);
        $this->writeLists($book, $map['lists'], $data);
        $this->writeEducation($book, $map['education_rows'], $data);

        $directory = dirname($destination);
        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        IOFactory::createWriter($book, 'Xlsx')->save($destination);
        $book->disconnectWorksheets();

        $this->checkboxWriter->tick($destination, $this->checkboxAnchors($data));

        return $destination;
    }

    public function templatePath(): string
    {
        $path = resource_path(self::TEMPLATE);

        if (! is_file($path)) {
            throw new RuntimeException("PDS template missing at {$path}.");
        }

        return $path;
    }

    // --- Writers ------------------------------------------------------------

    private function writeDirect(Spreadsheet $book, array $cells, array $data): void
    {
        foreach ($cells as $path => $address) {
            $this->setCell($book, $address, $this->value($data, $path));
        }
    }

    /**
     * The form prints a ruled line inside the label for these, so the value is
     * appended to the label rather than written to a cell of its own.
     */
    private function writeAppend(Spreadsheet $book, array $cells, array $data): void
    {
        foreach ($cells as $path => $address) {
            $value = $this->value($data, $path);

            if ($this->isOmittable($value)) {
                continue;
            }

            [$sheet, $coordinate] = $this->split($address);
            $cell    = $book->getSheetByName($sheet)->getCell($coordinate);
            $label   = rtrim(trim((string) $cell->getValue()), '_ ');
            $cell->setValueExplicit(
                trim($label.' '.$value),
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
        }
    }

    private function writeTables(Spreadsheet $book, array $tables, array $data): void
    {
        foreach ($tables as $path => $table) {
            $rows = $this->value($data, $path) ?? [];

            foreach (array_values($rows) as $index => $row) {
                if ($index >= $table['rows']) {
                    break;
                }

                $rowNumber = $table['first_row'] + $index;

                foreach ($table['columns'] as $field => $column) {
                    $this->setCell(
                        $book,
                        $table['sheet'].'!'.$column.$rowNumber,
                        $row[$field] ?? null
                    );
                }
            }
        }
    }

    private function writeLists(Spreadsheet $book, array $lists, array $data): void
    {
        foreach ($lists as $path => $list) {
            $values = $this->value($data, $path) ?? [];

            foreach (array_values($values) as $index => $value) {
                if ($index >= $list['rows']) {
                    break;
                }

                $this->setCell(
                    $book,
                    $list['sheet'].'!'.$list['column'].($list['first_row'] + $index),
                    $value
                );
            }
        }
    }

    /** Section III prints one fixed row per level rather than a free list. */
    private function writeEducation(Spreadsheet $book, array $config, array $data): void
    {
        foreach ($config['levels'] as $level => $rowNumber) {
            $row = $data['educational_background'][$level] ?? [];

            foreach ($config['columns'] as $field => $column) {
                $this->setCell(
                    $book,
                    $config['sheet'].'!'.$column.$rowNumber,
                    $row[$field] ?? null
                );
            }
        }
    }

    /**
     * Collect the checkbox anchors to tick, grouped by sheet.
     *
     * Unanswered yes/no questions contribute nothing, so both boxes stay blank —
     * rendering an unanswered question as NO would put a false sworn statement
     * on the form.
     *
     * @return array<string, list<string>>
     */
    private function checkboxAnchors(array $data): array
    {
        $anchors = [];

        foreach (config('pds_checkbox_map') as $path => $entry) {
            $value = $this->value($data, $path);

            // YES/NO pair.
            if (isset($entry['yes'])) {
                if ($value === true) {
                    $anchors[$entry['sheet']][] = $entry['yes'];
                } elseif ($value === false) {
                    $anchors[$entry['sheet']][] = $entry['no'];
                }

                continue;
            }

            // Option group: match case-insensitively, ignoring the N/A filler.
            if ($this->isOmittable($value)) {
                continue;
            }

            foreach ($entry['options'] as $option => $definition) {
                if (strcasecmp((string) $value, (string) $option) === 0) {
                    $anchors[$entry['sheet']][] = $definition['anchor'];
                    break;
                }
            }
        }

        return $anchors;
    }

    // --- Helpers ------------------------------------------------------------

    private function setCell(Spreadsheet $book, string $address, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        [$sheet, $coordinate] = $this->split($address);

        // Always explicit strings: values are pre-formatted by the assembler
        // (dd/mm/yyyy dates, "21,877.00" salaries) and must not be re-parsed
        // by Excel into serials or floats.
        $book->getSheetByName($sheet)->getCell($coordinate)->setValueExplicit(
            (string) $value,
            \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
        );
    }

    /** @return array{0: string, 1: string} */
    private function split(string $address): array
    {
        [$sheet, $coordinate] = explode('!', $address, 2);

        return [$sheet, $coordinate];
    }

    private function value(array $data, string $path): mixed
    {
        return Arr::get($data, $path);
    }

    private function isOmittable(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === PdsDataAssembler::NOT_APPLICABLE;
    }
}
