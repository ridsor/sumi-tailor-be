<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderFactory extends Factory
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
            'email' => $this->faker->freeEmail(),
            'no_hp' => $this->faker->numerify('08##########'),
            'address' => $this->faker->word(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numerify('##000'),
            'status' => $this->faker->randomElement(['isProcess','isSuccess']),
        ];
    }
}
