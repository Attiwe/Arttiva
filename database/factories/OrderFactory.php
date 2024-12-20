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
            'first_name'=>fake()->firstName,
            'last_name'=>fake()->lastName,
            'email'=>fake()->email,
            'phone'=>fake()->phoneNumber,
            'country'=>fake()->country,
            'address'=>fake()->address,
            'status'=>1,
            'invoice_number' => fake()->unique()->numberBetween(1000, 1000000),

        ];
    }
}
