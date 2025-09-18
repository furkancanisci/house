<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin user first (if roles exist)
        $superAdmin = User::firstOrCreate(
            ['email' => 'kado@kado.com'],
            [
                'first_name' => 'Muhammed',
                'last_name' => 'Kado',
                'password' => Hash::make('123123123'),
                'phone' => '+1-555-0000',
                'user_type' => 'admin',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'System Super Administrator with full access to all features.',
            ]
        );
        
        // Assign SuperAdmin role if it exists
        if (Role::where('name', 'SuperAdmin')->exists()) {
            $superAdmin->assignRole('SuperAdmin');
        }
    }
}
