<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Dashboard
            'view dashboard',
            'view analytics',
            
            // Properties
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'approve properties',
            'reject properties',
            'feature properties',
            'publish properties',
            'bulk manage properties',
            'export properties',
            'import properties',
            
            // Categories
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            
            // Locations
            'view cities',
            'manage cities',
            'manage neighborhoods',
            
            // Amenities
            'view amenities',
            'create amenities',
            'edit amenities',
            'delete amenities',
            
            // Features
            'view features',
            'create features',
            'edit features',
            'delete features',
            
            // Property Document Types
            'view property document types',
            'create property document types',
            'edit property document types',
            'delete property document types',
            
            // Utilities
            'view utilities',
            'create utilities',
            'edit utilities',
            'delete utilities',
            
            // Users
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            'impersonate users',
            
            // Leads
            'view leads',
            'create leads',
            'edit leads',
            'delete leads',
            'assign leads',
            'export leads',
            
            // Media
            'view media',
            'upload media',
            'delete media',
            'download media',
            
            // Moderation
            'view moderation queue',
            'moderate properties',
            
            // Settings
            'view settings',
            'edit settings',
            'manage smtp settings',
            'clear cache',
            
            // Reports
            'view reports',
            'export reports',
            
            // Roles & Permissions (SuperAdmin only)
            'manage roles',
            'manage permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // SuperAdmin - has all permissions
        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Admin - has most permissions except role/permission management
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view dashboard',
            'view analytics',
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'approve properties',
            'reject properties',
            'feature properties',
            'publish properties',
            'bulk manage properties',
            'export properties',
            'import properties',
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view cities',
            'manage cities',
            'manage neighborhoods',
            'view amenities',
            'create amenities',
            'edit amenities',
            'delete amenities',
            'view features',
            'create features',
            'edit features',
            'delete features',
            'view property document types',
            'create property document types',
            'edit property document types',
            'delete property document types',
            'view utilities',
            'create utilities',
            'edit utilities',
            'delete utilities',
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',
            'view leads',
            'create leads',
            'edit leads',
            'delete leads',
            'assign leads',
            'export leads',
            'view media',
            'upload media',
            'delete media',
            'download media',
            'view moderation queue',
            'moderate properties',
            'view settings',
            'edit settings',
            'view reports',
            'export reports',
        ]);

        // Moderator - can manage properties and moderation
        $moderatorRole = Role::firstOrCreate(['name' => 'Moderator', 'guard_name' => 'web']);
        $moderatorRole->givePermissionTo([
            'view dashboard',
            'view properties',
            'edit properties',
            'approve properties',
            'reject properties',
            'feature properties',
            'publish properties',
            'view categories',
            'view cities',
            'view amenities',
            'view features',
            'view property document types',
            'view utilities',
            'view users',
            'view leads',
            'view media',
            'view moderation queue',
            'moderate properties',
            'view reports',
        ]);

        // Agent - can manage their own properties and leads
        $agentRole = Role::firstOrCreate(['name' => 'Agent', 'guard_name' => 'web']);
        $agentRole->givePermissionTo([
            'view dashboard',
            'view properties',
            'create properties',
            'edit properties',
            'view categories',
            'view cities',
            'view amenities',
            'view features',
            'view utilities',
            'view leads',
            'edit leads',
            'view media',
            'upload media',
        ]);

        // User - basic viewing permissions
        $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
        $userRole->givePermissionTo([
            'view properties',
            'view categories',
            'view cities',
            'view amenities',
            'view features',
            'view utilities',
        ]);

        // Create SuperAdmin user if it doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@property.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('SuperAdmin');

        // Create sample Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin2@property.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Admin',
                'password' => Hash::make('Admin@123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Admin');

        // Create sample Moderator user
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@property.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Moderator',
                'password' => Hash::make('Moderator@123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $moderator->assignRole('Moderator');

        // Create sample Agent user
        $agent = User::firstOrCreate(
            ['email' => 'agent@property.com'],
            [
                'first_name' => 'Mike',
                'last_name' => 'Agent',
                'password' => Hash::make('Agent@123456'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $agent->assignRole('Agent');

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('SuperAdmin: admin@property.com / Admin@123456');
        $this->command->info('Admin: admin2@property.com / Admin@123456');
        $this->command->info('Moderator: moderator@property.com / Moderator@123456');
        $this->command->info('Agent: agent@property.com / Agent@123456');
    }
}