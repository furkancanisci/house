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
                'first_name' => 'Super',
                'last_name' => 'Admin',
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
        // Create demo property owner user
        User::firstOrCreate(
            ['email' => 'property.owner@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'password' => Hash::make('password123'),
                'phone' => '+1-555-0123',
                'user_type' => 'property_owner',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'Experienced property owner with over 10 years in real estate investment.'
            ]
        );

        // Create demo general user
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'password' => Hash::make('password123'),
                'phone' => '+1-555-0456',
                'user_type' => 'general_user',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
                'bio' => 'Looking for the perfect home for my family.'
            ]
        );

        // Create additional property owners with raw SQL
        $propertyOwners = [
            [
                'Michael', 'Brown', 'michael.brown@example.com', 
                'Luxury property specialist in downtown area.'
            ],
            [
                'Emily', 'Davis', 'emily.davis@example.com',
                'Family-friendly homes and apartment rentals.'
            ],
            [
                'David', 'Wilson', 'david.wilson@example.com',
                'Commercial and residential property development.'
            ],
        ];

        foreach ($propertyOwners as $owner) {
            User::firstOrCreate(
                ['email' => $owner[2]],
                [
                    'first_name' => $owner[0],
                    'last_name' => $owner[1],
                    'password' => Hash::make('password123'),
                    'phone' => '+1-555-' . rand(1000, 9999),
                    'user_type' => 'property_owner',
                    'is_verified' => true,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'bio' => $owner[3]
                ]
            );
        }

        // Create additional general users with raw SQL
        $generalUsers = [
            [
                'Jennifer', 'Miller', 'jennifer.miller@example.com',
                'First-time home buyer looking for a starter home.'
            ],
            [
                'Robert', 'Garcia', 'robert.garcia@example.com',
                'Young professional seeking modern apartment.'
            ],
            [
                'Lisa', 'Anderson', 'lisa.anderson@example.com',
                'Relocating for work, need temporary housing.'
            ],
            [
                'James', 'Martinez', 'james.martinez@example.com',
                'Real estate investor looking for opportunities.'
            ],
        ];

        foreach ($generalUsers as $user) {
            User::firstOrCreate(
                ['email' => $user[2]],
                [
                    'first_name' => $user[0],
                    'last_name' => $user[1],
                    'password' => Hash::make('password123'),
                    'phone' => '+1-555-' . rand(1000, 9999),
                    'user_type' => 'general_user',
                    'is_verified' => true,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'bio' => $user[3]
                ]
            );
        }

        // Create inactive user
        User::firstOrCreate(
            ['email' => 'inactive@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Inactive',
                'password' => Hash::make('password123'),
                'user_type' => 'general_user',
                'is_verified' => false,
                'is_active' => false
            ]
        );

        // Create unverified user
        User::firstOrCreate(
            ['email' => 'unverified@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'Unverified',
                'password' => Hash::make('password123'),
                'user_type' => 'property_owner',
                'is_verified' => false,
                'is_active' => true
            ]
        );
    }
}
