<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        DB::table('permissions')->insert([
            ['name' => 'manage_users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'access_dashboard', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
