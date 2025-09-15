<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ContactPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create contact message permissions
        $permissions = [
            'view contact messages',
            'delete contact messages',
            'manage contact messages',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to appropriate roles
        $superAdminRole = Role::where('name', 'SuperAdmin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $moderatorRole = Role::where('name', 'Moderator')->first();

        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        if ($moderatorRole) {
            $moderatorRole->givePermissionTo(['view contact messages']);
        }
    }
}