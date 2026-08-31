<?php

namespace Tests\Feature;

use App\Models\ProjectDistribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectDistributionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'TLKM',
            'total_so' => 570_540_000_000,
            'taxation' => 276_090_000_000,
            'collection' => 176_670_000_000,
            'july_target' => 11_500_000_000,
            'july_actual' => 60_410_000_000,
            'forecast_aug' => 12_280_000_000,
            'forecast_sep' => 20_320_000_000,
            'forecast_oct' => 34_810_000_000,
            'forecast_nov' => 14_800_000_000,
            'forecast_dec' => 17_220_000_000,
            'sort_order' => 0,
        ], $overrides);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('project-distributions.index'))->assertRedirect(route('login'));
    }

    public function test_index_lists_distributions(): void
    {
        $distribution = ProjectDistribution::factory()->create(['name' => 'TLKM']);

        $response = $this->actingAs($this->user)->get(route('project-distributions.index'));

        $response->assertOk();
        $response->assertSee($distribution->name);
    }

    public function test_distribution_can_be_created(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('project-distributions.store'), $this->validPayload());

        $response->assertRedirect(route('project-distributions.index'));
        $this->assertDatabaseHas('project_distributions', ['name' => 'TLKM']);
    }

    public function test_creation_requires_valid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('project-distributions.create'))
            ->post(route('project-distributions.store'), [
                'name' => '',
                'total_so' => -1,
                'taxation' => 'abc',
            ]);

        $response->assertRedirect(route('project-distributions.create'));
        $response->assertSessionHasErrors(['name', 'total_so', 'taxation', 'collection']);
        $this->assertDatabaseCount('project_distributions', 0);
    }

    public function test_name_must_be_unique(): void
    {
        ProjectDistribution::factory()->create(['name' => 'TLKM']);

        $response = $this->actingAs($this->user)
            ->post(route('project-distributions.store'), $this->validPayload());

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('project_distributions', 1);
    }

    public function test_distribution_can_be_updated(): void
    {
        $distribution = ProjectDistribution::factory()->create();

        $response = $this->actingAs($this->user)->put(
            route('project-distributions.update', $distribution),
            $this->validPayload(['name' => 'IOH', 'collection' => 71_170_000_000])
        );

        $response->assertRedirect(route('project-distributions.index'));
        $this->assertDatabaseHas('project_distributions', [
            'id' => $distribution->id,
            'name' => 'IOH',
        ]);
    }

    public function test_distribution_can_be_deleted(): void
    {
        $distribution = ProjectDistribution::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('project-distributions.destroy', $distribution));

        $response->assertRedirect(route('project-distributions.index'));
        $this->assertDatabaseMissing('project_distributions', ['id' => $distribution->id]);
    }

    public function test_outstanding_and_remaining_are_computed(): void
    {
        $distribution = ProjectDistribution::factory()->create([
            'total_so' => 570_540_000_000,
            'taxation' => 276_090_000_000,
            'collection' => 176_670_000_000,
        ]);

        $this->assertEqualsWithDelta(393_870_000_000, $distribution->outstanding, 0.01);
        $this->assertEqualsWithDelta(99_420_000_000, $distribution->remaining, 0.01);
    }
}
