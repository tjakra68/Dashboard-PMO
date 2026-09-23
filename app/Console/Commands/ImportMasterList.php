<?php

namespace App\Console\Commands;

use App\Models\MasterProject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportMasterList extends Command
{
    protected $signature = 'master-list:import
        {file : Path to the master list xlsx file}
        {--year=2026 : Year of the columns to import}
        {--start-row=7 : First data row of the sheet}';

    protected $description = 'Import the PMO master list workbook into the dashboard tables';

    /**
     * Column indexes (1 based) of the master list sheet.
     */
    protected const COLUMNS = [
        'account' => 2,
        'status_so' => 3,
        'pid' => 4,
        'department' => 8,
        'project' => 9,
        'customer' => 10,
        'name' => 11,
        'so_value' => 70,
        'collection' => 72,
        'outstanding' => 74,
        'remaining' => 112,
        'target' => 113,
    ];

    /**
     * First monthly column (January forecast); every month uses forecast/target/actual triplets.
     */
    protected const FIRST_MONTH_COLUMN = 76;

    protected const ACCOUNT_ALIASES = [
        'sisindokom' => 'SIS',
        'sis' => 'SIS',
        'astel' => 'ASTEL',
        'ast' => 'AST',
    ];

    public function handle(): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->components->error("File not found: {$file}");

            return self::FAILURE;
        }

        $year = (int) $this->option('year');
        $startRow = (int) $this->option('start-row');

        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($file)->getActiveSheet();

        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($sheet, $year, $startRow, &$imported, &$skipped): void {
            MasterProject::query()->where('year', $year)->delete();

            foreach (range($startRow, $sheet->getHighestDataRow()) as $row) {
                $account = $this->account($this->string($sheet, $row, self::COLUMNS['account']));
                $project = strtoupper($this->string($sheet, $row, self::COLUMNS['project']));

                if ($account === null || ! in_array($project, MasterProject::PROJECTS, true)) {
                    $skipped++;

                    continue;
                }

                $record = MasterProject::query()->create([
                    'year' => $year,
                    'account' => $account,
                    'project' => $project,
                    'pid' => $this->string($sheet, $row, self::COLUMNS['pid']) ?: null,
                    'status_so' => $this->string($sheet, $row, self::COLUMNS['status_so']) ?: null,
                    'department' => $this->string($sheet, $row, self::COLUMNS['department']) ?: null,
                    'customer' => $this->string($sheet, $row, self::COLUMNS['customer']) ?: null,
                    'name' => $this->string($sheet, $row, self::COLUMNS['name']) ?: null,
                    'so_value' => $this->number($sheet, $row, self::COLUMNS['so_value']),
                    'collection' => $this->number($sheet, $row, self::COLUMNS['collection']),
                    'outstanding' => $this->number($sheet, $row, self::COLUMNS['outstanding']),
                    'target' => $this->number($sheet, $row, self::COLUMNS['target']),
                    'remaining' => $this->number($sheet, $row, self::COLUMNS['remaining']),
                ]);

                $months = [];

                foreach (range(1, 12) as $month) {
                    $column = self::FIRST_MONTH_COLUMN + (($month - 1) * 3);

                    $months[] = [
                        'month' => $month,
                        'forecast' => $this->number($sheet, $row, $column),
                        'target' => $this->number($sheet, $row, $column + 1),
                        'actual' => $this->number($sheet, $row, $column + 2),
                    ];
                }

                $record->months()->createMany($months);
                $imported++;
            }
        });

        $this->components->info("Imported {$imported} projects for {$year} ({$skipped} rows skipped).");

        return self::SUCCESS;
    }

    protected function account(string $value): ?string
    {
        return self::ACCOUNT_ALIASES[strtolower(trim($value))] ?? null;
    }

    protected function string(Worksheet $sheet, int $row, int $column): string
    {
        return trim((string) $sheet->getCell([$column, $row])->getValue());
    }

    /**
     * Read a numeric cell, falling back to the value cached by Excel for formula cells.
     */
    protected function number(Worksheet $sheet, int $row, int $column): float
    {
        $cell = $sheet->getCell([$column, $row]);
        $value = $cell->getValue();

        if (is_string($value) && str_starts_with($value, '=')) {
            $value = $cell->getOldCalculatedValue();
        }

        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }
}
