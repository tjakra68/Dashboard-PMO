<?php

namespace Tests\Feature;

use App\Models\CollectionEntry;
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
        $this->seedEntry(month: 1, account: 'SIS', project: 'TLKM', soValue: 100_000_000_000, collection: 40_000_000_000, forecast: 60_000_000_000);
        $this->seedEntry(month: 2, account: 'AST', project: 'IOH', soValue: 50_000_000_000, collection: 20_000_000_000, forecast: 30_000_000_000);
        $this->seedEntry(month: 5, account: 'ASTEL', project: 'XL', soValue: 25_000_000_000, collection: 10_000_000_000, forecast: 15_000_000_000);

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
        $this->seedEntry(month: 1, account: 'SIS', project: 'TLKM', soValue: 100_000_000_000, collection: 40_000_000_000, forecast: 60_000_000_000);
        $this->seedEntry(month: 1, account: 'AST', project: 'TLKM', soValue: 80_000_000_000, collection: 30_000_000_000, forecast: 50_000_000_000);
        $this->seedEntry(month: 1, account: 'SIS', project: 'EPS', soValue: 20_000_000_000, collection: 5_000_000_000, forecast: 10_000_000_000);

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
        $this->seedEntry(month: 1, account: 'SIS', project: 'TLKM', soValue: 10_000_000_000, collection: 4_000_000_000, forecast: 6_000_000_000);
        $this->seedEntry(month: 3, account: 'SIS', project: 'TLKM', soValue: 10_000_000_000, collection: 2_000_000_000, forecast: 4_000_000_000);

        $response = $this->actingAs($this->user)->get(route('dashboard', ['year' => 2026, 'month' => 3]));

        $response->assertOk();
        $response->assertViewHas('chart', function (array $chart): bool {
            return $chart['forecast'][0] === 6_000_000_000.0
                && $chart['forecast'][2] === 4_000_000_000.0
                && $chart['cumulative_forecast'][11] === 10_000_000_000.0
                && $chart['collected_to_date'] === 6_000_000_000.0;
        });
    }

    public function test_dashboard_renders_without_any_data(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum ada data collection untuk filter ini.');
    }

    protected function seedEntry(int $month, string $account, string $project, float $soValue, float $collection, float $forecast): void
    {
        CollectionEntry::factory()->create([
            'year' => 2026,
            'month' => $month,
            'account' => $account,
            'project' => $project,
            'so_value' => $soValue,
            'collection' => $collection,
            'forecast' => $forecast,
        ]);
    }
}
