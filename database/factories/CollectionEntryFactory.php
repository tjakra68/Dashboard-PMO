<?php

namespace Database\Factories;

use App\Models\CollectionEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionEntry>
 */
class CollectionEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $soValue = fake()->randomFloat(2, 1_000_000_000, 50_000_000_000);

        return [
            'year' => (int) date('Y'),
            'month' => fake()->numberBetween(1, 12),
            'account' => fake()->randomElement(CollectionEntry::ACCOUNTS),
            'project' => fake()->randomElement(CollectionEntry::PROJECTS),
            'so_value' => $soValue,
            'collection' => round($soValue * fake()->randomFloat(2, 0.2, 0.9), 2),
            'forecast' => round($soValue * fake()->randomFloat(2, 0.3, 1.0), 2),
        ];
    }
}
