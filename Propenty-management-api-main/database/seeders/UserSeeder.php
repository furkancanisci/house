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
            ['email' => 'superadmin@property.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('SuperAdmin@123456'),
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
        // Use raw SQL to insert the first user with proper boolean values
        DB::statement("INSERT INTO users (first_name, last_name, email, password, phone, user_type, is_verified, is_active, email_verified_at, bio, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, true, true, ?, ?, NOW(), NOW())",
            [
                'John',
                'Smith',
                'property.owner@example.com',
                Hash::make('password123'),
                '+1-555-0123',
                'property_owner',
                now(),
                'Experienced property owner with over 10 years in real estate investment.'
            ]
        );

        // Create demo general user with raw SQL
        DB::statement("INSERT INTO users (first_name, last_name, email, password, phone, user_type, is_verified, is_active, email_verified_at, bio, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, true, true, ?, ?, NOW(), NOW())",
            [
                'Sarah',
                'Johnson',
                'user@example.com',
                Hash::make('password123'),
                '+1-555-0456',
                'general_user',
                now(),
                'Looking for the perfect home for my family.'
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
            DB::statement("INSERT INTO users (first_name, last_name, email, password, phone, user_type, is_verified, is_active, email_verified_at, bio, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'property_owner', true, true, ?, ?, NOW(), NOW())",
                [
                    $owner[0], // first_name
                    $owner[1], // last_name
                    $owner[2], // email
                    Hash::make('password123'),
                    '+1-555-' . rand(1000, 9999),
                    now(),
                    $owner[3] // bio
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
            DB::statement("INSERT INTO users (first_name, last_name, email, password, phone, user_type, is_verified, is_active, email_verified_at, bio, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, 'general_user', true, true, ?, ?, NOW(), NOW())",
                [
                    $user[0], // first_name
                    $user[1], // last_name
                    $user[2], // email
                    Hash::make('password123'),
                    '+1-555-' . rand(1000, 9999),
                    now(),
                    $user[3] // bio
                ]
            );
        }

        // Create inactive user with raw SQL
        DB::statement("INSERT INTO users (first_name, last_name, email, password, user_type, is_verified, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'general_user', false, false, NOW(), NOW())",
            [
                'Test',
                'Inactive',
                'inactive@example.com',
                Hash::make('password123')
            ]
        );

        // Create unverified user with raw SQL
        DB::statement("INSERT INTO users (first_name, last_name, email, password, user_type, is_verified, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'property_owner', false, true, NOW(), NOW())",
            [
                'Test',
                'Unverified',
                'unverified@example.com',
                Hash::make('password123')
            ]
        );
    }
}
