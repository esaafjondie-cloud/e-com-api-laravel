<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '1234567890',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'phone' => '0987654321',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'phone' => '5555555555',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        SystemSetting::create([
            'key' => 'sham_cash_qr',
            'value' => '',
            'description' => 'Payment QR Code for Sham Cash',
        ]);

        SystemSetting::create([
            'key' => 'admin_phone',
            'value' => '+1234567890',
            'description' => 'Admin contact phone number',
        ]);
    }
}
