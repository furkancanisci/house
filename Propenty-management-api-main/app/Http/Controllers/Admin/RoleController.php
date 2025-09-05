<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        Gate::authorize('manage roles');

        $roles = Role::with('permissions')->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        Gate::authorize('manage roles');

        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });

        return view('admin.roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage roles');

        $request->validate([
            'name' => 'required|unique:roles,name|max:255',
            'guard_name' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        Gate::authorize('manage roles');

        $role->load(['permissions', 'users']);
        
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });
        
        $totalRoles = Role::count();
        $totalUsers = \App\Models\User::count();

        return view('admin.roles.show', compact('role', 'permissions', 'groupedPermissions', 'totalRoles', 'totalUsers'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        Gate::authorize('manage roles');

        $role->load('permissions');
        
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });

        return view('admin.roles.edit', compact('role', 'permissions', 'groupedPermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        Gate::authorize('manage roles');

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id . '|max:255',
            'guard_name' => 'nullable|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        Gate::authorize('manage roles');

        // Prevent deletion of super admin role
        if ($role->name === 'SuperAdmin') {
            return redirect()->route('admin.roles.index')
                           ->with('error', 'Cannot delete SuperAdmin role.');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                           ->with('error', 'Cannot delete role that has users assigned to it.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
                        ->with('success', 'Role deleted successfully.');
    }

    /**
     * Update permissions for a role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        Gate::authorize('manage roles');

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully.'
        ]);
    }
}