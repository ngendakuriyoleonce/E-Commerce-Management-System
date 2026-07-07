<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@ems.test',
            'password' => bcrypt('password'),
        ]);

        $admin->assignRole('Admin');
    }
}
