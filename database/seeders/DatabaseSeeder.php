<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            vpn_ServerSeeder::class,
            ApplicationSeeder::class,
            HomePageSeeder::class,
        ]);
    }
}
