<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PriceTypePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions for price types
        $permissions = [
            'view price types',
            'create price types',
            'edit price types',
            'delete price types',
            'manage price types',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $superAdmin = Role::where('name', 'SuperAdmin')->first();
        $admin = Role::where('name', 'Admin')->first();
        $moderator = Role::where('name', 'Moderator')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        if ($admin) {
            $admin->givePermissionTo([
                'view price types',
                'create price types',
                'edit price types',
                'delete price types',
                'manage price types',
            ]);
        }

        if ($moderator) {
            $moderator->givePermissionTo([
                'view price types',
            ]);
        }

        $this->command->info('Price type permissions have been created and assigned to roles.');
    }
}