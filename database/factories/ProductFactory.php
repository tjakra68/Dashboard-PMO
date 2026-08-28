<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement(['Elektronik', 'Pakaian', 'Makanan', 'Peralatan', 'Aksesoris']),
            'price' => fake()->randomFloat(2, 1000, 5000000),
            'stock' => fake()->numberBetween(0, 500),
            'description' => fake()->sentence(10),
        ];
    }
}
