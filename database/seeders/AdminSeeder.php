<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('123456'),
            'active' => 1,
            'is_admin' => 1
        ]);

        $role = Role::where('name', 'admin')->first();
        if ($role) {
            $admin->roles()->attach($role);
        }
    }
}
