<?php

namespace Tests\Feature;

use App\Models\ProjectDistribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_project_distribution_chart_and_table(): void
    {
        $user = User::factory()->create();

        ProjectDistribution::factory()->create([
            'name' => 'TLKM',
            'total_so' => 570_540_000_000,
            'taxation' => 276_090_000_000,
            'collection' => 176_670_000_000,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Collection of Project Distribution');
        $response->assertSee('TLKM');
        $response->assertSee('570.54B');
        $response->assertSee('99.42B'); // remaining = taxation - collection
        $response->assertSee('Grand Total');
        $response->assertSee('distributionChart', false);
    }

    public function test_dashboard_renders_without_project_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Belum ada data proyek.');
    }

    public function test_amounts_are_formatted_with_b_and_m_suffixes(): void
    {
        $this->assertSame('570.54B', ProjectDistribution::formatAmount(570_540_000_000));
        $this->assertSame('996.41M', ProjectDistribution::formatAmount(996_410_000));
        $this->assertSame('1,343.90B', ProjectDistribution::formatAmount(1_343_900_000_000));
        $this->assertSame('0.00', ProjectDistribution::formatAmount(0));
    }
}
