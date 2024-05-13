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
            'item_code' => 'ST'. $this->faker->numerify('#######'),
            'name' => $this->faker->name(), 
            'no_hp' => $this->faker->numerify('08##########'),
            'address' => $this->faker->word(),
            'note' => $this->faker->paragraph(),
            'price' => $this->faker->numerify('##000'),
            'image' => $this->faker->imageUrl(1920, 1080, 'clothes', true),
            'status' => $this->faker->randomElement(['isProcess','isFinished']),
        ];
    }
}
