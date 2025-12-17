<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'price' => fake()->randomFloat(3, 10, 100),
            'publish_year' => fake()->year(),
            'isbn' => fake()->isbn13(),
            'category_id' => \App\Models\Category::factory(),
            'status' => fake()->randomElement(['draft', 'published']),
            'stock' => fake()->numberBetween(0, 100),
        ];
    }
}
