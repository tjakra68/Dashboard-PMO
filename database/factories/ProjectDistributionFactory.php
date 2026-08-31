<?php

namespace Database\Factories;

use App\Models\ProjectDistribution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectDistribution>
 */
class ProjectDistributionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSo = fake()->randomFloat(2, 10_000_000_000, 600_000_000_000);
        $taxation = fake()->randomFloat(2, 0, $totalSo);
        $collection = fake()->randomFloat(2, 0, $taxation);

        return [
            'name' => strtoupper(fake()->unique()->lexify('????')),
            'total_so' => $totalSo,
            'taxation' => $taxation,
            'collection' => $collection,
            'july_target' => fake()->randomFloat(2, 0, 20_000_000_000),
            'july_actual' => fake()->randomFloat(2, 0, 20_000_000_000),
            'forecast_aug' => fake()->randomFloat(2, 0, 30_000_000_000),
            'forecast_sep' => fake()->randomFloat(2, 0, 30_000_000_000),
            'forecast_oct' => fake()->randomFloat(2, 0, 30_000_000_000),
            'forecast_nov' => fake()->randomFloat(2, 0, 30_000_000_000),
            'forecast_dec' => fake()->randomFloat(2, 0, 30_000_000_000),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
