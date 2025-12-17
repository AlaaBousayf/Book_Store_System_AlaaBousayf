<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->state(['type' => 'customer']),
            'total' => fake()->randomFloat(3, 20, 200),
            'payment_method_id' => 1,
            'address' => fake()->address(),
            'status' => fake()->randomElement(['pending', 'completed', 'cancelled']),
        ];
    }
}
