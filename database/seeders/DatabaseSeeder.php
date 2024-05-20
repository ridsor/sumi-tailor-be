<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Order;
use App\Models\Role;
use App\Models\MonthlyTemp;
use App\Models\Temp;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $admin = Role::create([
            'name' => 'admin',
        ]);
        $super_admin = Role::create([
            'name' => 'super admin',
        ]);

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'sadmin@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => $super_admin->id,
        ]);
        // User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('password'),
        //     'role_id' => $admin->id,
        // ]);

        Order::factory(50)->create();


        // MonthlyTemp::create([
        //     'order_total' => 2,
        //     'total_income' => 20000,
        //     "created_at" =>  \Carbon\Carbon::now()->subMonth(),
        //     "updated_at" => \Carbon\Carbon::now()->subMonth()
        // ]);
        MonthlyTemp::create([]);
    }
}
