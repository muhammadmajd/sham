<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'test',
            'email' => 'test@test.com',
            'password' => Hash::make('123456'),
            'active' => 1,
            'is_admin' => 0,
            'uuid' => '7d6b2066-a129-4175-b245-fe2fd125d931'
        ]);
    }
}
