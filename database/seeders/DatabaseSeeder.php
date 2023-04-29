<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Pesanan;
use App\Models\Role;

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
            'password' => bcrypt('password'),
            'role_id' => 3
        ]);

        Pesanan::factory(5)->create();

        Role::create([
            'name' => 'user',
        ]);
        Role::create([
            'name' => 'admin',
        ]);
        Role::create([
            'name' => 'admin_super',
        ]);
    }
}
