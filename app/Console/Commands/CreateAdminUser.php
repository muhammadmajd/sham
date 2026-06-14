<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
                            {name : Admin name}
                            {email : Admin email}
                            {password : Admin password}';

    protected $description = 'Create a web admin user';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists.');
            return self::FAILURE;
        }

        User::create([
            'name' => (string) $this->argument('name'),
            'email' => $email,
            'password' => Hash::make((string) $this->argument('password')),
            'active' => true,
            'is_admin' => true,
        ]);

        $this->info('Admin created successfully.');
        return self::SUCCESS;
    }
}
