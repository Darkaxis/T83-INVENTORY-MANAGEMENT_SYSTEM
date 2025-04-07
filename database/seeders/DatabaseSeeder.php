<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminUser;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        AdminUser::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'), // Change this to a secure password
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created! You can log in with admin@example.com and password: admin123');
    }
    }

