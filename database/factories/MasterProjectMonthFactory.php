<?php

namespace Database\Factories;

use App\Models\MasterProject;
use App\Models\MasterProjectMonth;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterProjectMonth>
 */
class MasterProjectMonthFactory extends Factory
{
    protected $model = MasterProjectMonth::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $forecast = fake()->randomFloat(2, 100_000_000, 5_000_000_000);

        return [
            'master_project_id' => MasterProject::factory(),
            'month' => fake()->numberBetween(1, 12),
            'forecast' => $forecast,
            'target' => $forecast,
            'actual' => $forecast,
        ];
    }
}
