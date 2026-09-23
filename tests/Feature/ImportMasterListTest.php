<?php

namespace Tests\Feature;

use App\Models\MasterProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class ImportMasterListTest extends TestCase
{
    use RefreshDatabase;

    protected string $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->file = tempnam(sys_get_temp_dir(), 'master-list').'.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }

        parent::tearDown();
    }

    public function test_it_imports_projects_with_normalised_accounts_and_monthly_values(): void
    {
        $this->writeWorkbook([
            ['account' => 'Sisindokom', 'project' => 'TLKM', 'so' => 100.0, 'january' => [60.0, 55.0, 50.0]],
            ['account' => 'Astel', 'project' => 'XL', 'so' => 40.0, 'january' => [20.0, 20.0, 10.0]],
            ['account' => 'Kosong', 'project' => 'TLKM', 'so' => 999.0, 'january' => [1.0, 1.0, 1.0]],
        ]);

        $this->artisan('master-list:import', ['file' => $this->file, '--year' => 2026])
            ->assertSuccessful();

        $this->assertSame(2, MasterProject::query()->count());
        $this->assertEqualsCanonicalizing(['SIS', 'ASTEL'], MasterProject::query()->pluck('account')->all());

        $sis = MasterProject::query()->where('account', 'SIS')->firstOrFail();

        $this->assertSame(100.0, $sis->so_value);
        $this->assertSame(12, $sis->months()->count());

        $january = $sis->months()->where('month', 1)->firstOrFail();

        $this->assertSame(60.0, $january->forecast);
        $this->assertSame(55.0, $january->target);
        $this->assertSame(50.0, $january->actual);
    }

    public function test_it_replaces_previously_imported_rows_for_the_same_year(): void
    {
        MasterProject::factory()->create(['year' => 2026, 'account' => 'AST', 'project' => 'ND']);
        MasterProject::factory()->create(['year' => 2025, 'account' => 'AST', 'project' => 'ND']);

        $this->writeWorkbook([
            ['account' => 'Sisindokom', 'project' => 'TLKM', 'so' => 10.0, 'january' => [5.0, 5.0, 5.0]],
        ]);

        $this->artisan('master-list:import', ['file' => $this->file, '--year' => 2026])
            ->assertSuccessful();

        $this->assertSame(1, MasterProject::query()->where('year', 2026)->count());
        $this->assertSame(1, MasterProject::query()->where('year', 2025)->count());
    }

    public function test_it_fails_when_the_file_is_missing(): void
    {
        $this->artisan('master-list:import', ['file' => '/tmp/does-not-exist.xlsx'])
            ->assertFailed();
    }

    /**
     * @param  list<array{account: string, project: string, so: float, january: array{0: float, 1: float, 2: float}}>  $rows
     */
    protected function writeWorkbook(array $rows): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $index => $row) {
            $line = 7 + $index;

            $sheet->setCellValue([2, $line], $row['account']);
            $sheet->setCellValue([9, $line], $row['project']);
            $sheet->setCellValue([70, $line], $row['so']);
            $sheet->setCellValue([76, $line], $row['january'][0]);
            $sheet->setCellValue([77, $line], $row['january'][1]);
            $sheet->setCellValue([78, $line], $row['january'][2]);
        }

        (new XlsxWriter($spreadsheet))->save($this->file);
    }
}
