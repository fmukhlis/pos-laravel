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
            'cash_amount' => fake()->randomNumber(2, true) * 5000,
            'note' => fake()->sentence(7),
            'order_type' => fake()->randomElement(['Dine In', 'Take Away']),
            'status' => fake()->randomElement(['Paid', 'Billed']),
            'table_number' => fake()->numerify('T#####')
        ];
    }
}
