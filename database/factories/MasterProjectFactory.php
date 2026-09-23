<?php

namespace Database\Factories;

use App\Models\MasterProject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MasterProject>
 */
class MasterProjectFactory extends Factory
{
    protected $model = MasterProject::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $soValue = fake()->randomFloat(2, 1_000_000_000, 50_000_000_000);
        $collection = round($soValue * fake()->randomFloat(2, 0.1, 0.8), 2);
        $target = round($soValue * 0.5, 2);

        return [
            'year' => 2026,
            'account' => fake()->randomElement(MasterProject::ACCOUNTS),
            'project' => fake()->randomElement(MasterProject::PROJECTS),
            'pid' => fake()->bothify('PID-####'),
            'status_so' => 'SO',
            'department' => fake()->randomElement(['DMS', 'DIS', 'DNS']),
            'customer' => fake()->company(),
            'name' => fake()->sentence(3),
            'so_value' => $soValue,
            'collection' => $collection,
            'outstanding' => round($soValue - $collection, 2),
            'target' => $target,
            'remaining' => round($target - $collection, 2),
        ];
    }
}
