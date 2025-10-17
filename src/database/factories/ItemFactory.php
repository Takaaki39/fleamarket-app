<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'price' => $this->faker->numberBetween(100, 300000),
            'brand_name' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'condition' => 1
        ];
    }
}
