<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdvancedDetailsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for advanced property details
        $permissions = [
            // Building Types
            'view building types',
            'create building types',
            'edit building types',
            'delete building types',
            
            // Window Types
            'view window types',
            'create window types',
            'edit window types',
            'delete window types',
            
            // Floor Types
            'view floor types',
            'create floor types',
            'edit floor types',
            'delete floor types',
            
            // View Types
            'view view types',
            'create view types',
            'edit view types',
            'delete view types',
            
            // Directions
            'view directions',
            'create directions',
            'edit directions',
            'delete directions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $superAdmin = Role::where('name', 'SuperAdmin')->first();
        $admin = Role::where('name', 'Admin')->first();
        $moderator = Role::where('name', 'Moderator')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        if ($admin) {
            $admin->givePermissionTo($permissions);
        }

        if ($moderator) {
            $moderator->givePermissionTo([
                'view building types',
                'view window types',
                'view floor types',
                'view view types',
                'view directions',
            ]);
        }

        $this->command->info('Advanced details permissions have been created and assigned to roles.');
    }
}