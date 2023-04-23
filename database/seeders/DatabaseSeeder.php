<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Pesanan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'admin super',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password')
        ]);

        Pesanan::factory(5)->create();
    }
}
