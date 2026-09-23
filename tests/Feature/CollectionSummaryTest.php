<?php

namespace Tests\Feature;

use App\Models\MasterProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionSummaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_summary_totals_are_calculated_up_to_the_selected_month(): void
    {
        $this->seedProject('SIS', 'TLKM', 100_000_000_000, [1 => [60_000_000_000, 40_000_000_000]]);
        $this->seedProject('AST', 'IOH', 50_000_000_000, [2 => [30_000_000_000, 20_000_000_000]]);
        $this->seedProject('ASTEL', 'XL', 25_000_000_000, [5 => [15_000_000_000, 10_000_000_000]]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 2026, 'month' => 2]));

        $response->assertOk();
        $response->assertViewHas('summary', [
            'so_value' => 175_000_000_000.0,
            'collection' => 60_000_000_000.0,
            'outstanding' => 115_000_000_000.0,
            'taxation' => 105_000_000_000.0,
            'remaining' => 45_000_000_000.0,
        ]);
    }

    public function test_filters_limit_the_summary_to_one_account_and_project(): void
    {
        $this->seedProject('SIS', 'TLKM', 100_000_000_000, [1 => [60_000_000_000, 40_000_000_000]]);
        $this->seedProject('AST', 'TLKM', 80_000_000_000, [1 => [50_000_000_000, 30_000_000_000]]);
        $this->seedProject('SIS', 'EPS', 20_000_000_000, [1 => [10_000_000_000, 5_000_000_000]]);

        $response = $this->actingAs($this->user)->get(route('dashboard', [
            'year' => 2026,
            'month' => 12,
            'account' => 'SIS',
            'project' => 'TLKM',
        ]));

        $response->assertOk();
        $response->assertViewHas('summary.so_value', 100_000_000_000.0);
        $response->assertViewHas('summary.collection', 40_000_000_000.0);
        $response->assertViewHas('projectRows', function (array $rows): bool {
            return count($rows) === 1 && $rows[0]['label'] === 'TLKM';
        });
    }

    public function test_chart_exposes_monthly_and_cumulative_forecast(): void
    {
        $this->seedProject('SIS', 'TLKM', 20_000_000_000, [
            1 => [6_000_000_000, 6_000_000_000],
            3 => [4_000_000_000, 0],
        ]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertViewHas('chart', function (array $chart): bool {
            return $chart['forecast'][0] === 6_000_000_000.0
                && $chart['forecast'][2] === 4_000_000_000.0
                && $chart['cumulative_forecast'][11] === 10_000_000_000.0
                && $chart['collected_to_date'] === 6_000_000_000.0;
        });
    }

    public function test_account_rows_show_monthly_forecast_per_account(): void
    {
        $this->seedProject('SIS', 'TLKM', 10_000_000_000, [1 => [6_000_000_000, 6_000_000_000]]);
        $this->seedProject('AST', 'ND', 10_000_000_000, [2 => [3_000_000_000, 0]]);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 2026, 'month' => 2]));

        $response->assertOk();
        $response->assertViewHas('accountRows', function (array $rows): bool {
            return count($rows) === 2
                && $rows[0]['label'] === 'SIS'
                && $rows[0]['values'][0] === 6_000_000_000.0
                && $rows[1]['label'] === 'AST'
                && $rows[1]['values'][1] === 3_000_000_000.0;
        });
    }

    public function test_dashboard_renders_without_any_data(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada data collection untuk filter ini.');
    }

    /**
     * @param  array<int, array{0: float|int, 1: float|int}>  $months  forecast/actual keyed by month number
     */
    protected function seedProject(string $account, string $project, float $soValue, array $months): void
    {
        $record = MasterProject::factory()->create([
            'year' => 2026,
            'account' => $account,
            'project' => $project,
            'so_value' => $soValue,
        ]);

        foreach ($months as $month => [$forecast, $actual]) {
            $record->months()->create([
                'month' => $month,
                'forecast' => $forecast,
                'target' => $forecast,
                'actual' => $actual,
            ]);
        }
    }
}
