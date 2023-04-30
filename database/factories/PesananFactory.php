<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pesanan;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class PesananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama' => $this->faker->name(),
            'deskripsi' => $this->faker->paragraph(),
            'harga' => $this->faker->numerify('##000'),
            'finished' => $this->faker->randomElement([0,1]),
        ];
    }
}
