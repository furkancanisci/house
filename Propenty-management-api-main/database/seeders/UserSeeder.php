<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin/demo property owner
        User::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'property.owner@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-0123',
            'user_type' => 'property_owner',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'bio' => 'Experienced property owner with over 10 years in real estate investment.',
        ]);

        // Create demo general user
        User::create([
            'first_name' => 'Sarah',
            'last_name' => 'Johnson',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'phone' => '+1-555-0456',
            'user_type' => 'general_user',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'bio' => 'Looking for the perfect home for my family.',
        ]);

        // Create additional property owners
        $propertyOwners = [
            [
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'bio' => 'Luxury property specialist in downtown area.',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'bio' => 'Family-friendly homes and apartment rentals.',
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'email' => 'david.wilson@example.com',
                'bio' => 'Commercial and residential property development.',
            ],
        ];

        foreach ($propertyOwners as $owner) {
            User::create(array_merge($owner, [
                'password' => Hash::make('password123'),
                'phone' => '+1-555-' . rand(1000, 9999),
                'user_type' => 'property_owner',
                'is_verified' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]));
        }

        // Create additional general users
        $generalUsers = [
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Miller',
                'email' => 'jennifer.miller@example.com',
                'bio' => 'First-time home buyer looking for a starter home.',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Garcia',
                'email' => 'robert.garcia@example.com',
                'bio' => 'Young professional seeking modern apartment.',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'email' => 'lisa.anderson@example.com',
                'bio' => 'Relocating for work, need temporary housing.',
            ],
            [
                'first_name' => 'James',
                'last_name' => 'Martinez',
                'email' => 'james.martinez@example.com',
                'bio' => 'Real estate investor looking for opportunities.',
            ],
        ];

        foreach ($generalUsers as $user) {
            User::create(array_merge($user, [
                'password' => Hash::make('password123'),
                'phone' => '+1-555-' . rand(1000, 9999),
                'user_type' => 'general_user',
                'is_verified' => rand(0, 1) ? true : false,
                'is_active' => true,
                'email_verified_at' => rand(0, 1) ? now() : null,
            ]));
        }

        // Create some inactive/unverified users for testing
        User::create([
            'first_name' => 'Test',
            'last_name' => 'Inactive',
            'email' => 'inactive@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'general_user',
            'is_verified' => false,
            'is_active' => false,
        ]);

        User::create([
            'first_name' => 'Test',
            'last_name' => 'Unverified',
            'email' => 'unverified@example.com',
            'password' => Hash::make('password123'),
            'user_type' => 'property_owner',
            'is_verified' => false,
            'is_active' => true,
        ]);
    }
}
