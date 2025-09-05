<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index()
    {
        Gate::authorize('manage permissions');

        $permissions = Permission::with('roles')->paginate(20);

        // Group permissions by category
        $groupedPermissions = Permission::all()->groupBy(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        });

        return view('admin.permissions.index', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        Gate::authorize('manage permissions');

        // Get existing categories
        $existingCategories = Permission::all()->map(function($permission) {
            return explode(' ', $permission->name)[1] ?? 'general';
        })->unique()->values();

        return view('admin.permissions.create', compact('existingCategories'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('manage permissions');

        $request->validate([
            'name' => 'required|unique:permissions,name|max:255',
            'guard_name' => 'nullable|string|max:255',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        if ($request->has('roles')) {
            $permission->syncRoles($request->roles);
        }

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        Gate::authorize('manage permissions');

        $permission->load(['roles']);
        
        // Get current category
        $currentCategory = explode(' ', $permission->name)[1] ?? 'general';
        
        // Get related permissions in same category
        $relatedPermissions = Permission::where('id', '!=', $permission->id)
            ->get()
            ->filter(function($p) use ($currentCategory) {
                $category = explode(' ', $p->name)[1] ?? 'general';
                return $category === $currentCategory;
            });
            
        $totalRoles = Role::count();
        $totalUsers = \App\Models\User::count();

        return view('admin.permissions.show', compact('permission', 'currentCategory', 'relatedPermissions', 'totalRoles', 'totalUsers'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        Gate::authorize('manage permissions');

        $permission->load('roles');
        
        // Get current category
        $currentCategory = explode(' ', $permission->name)[1] ?? 'general';
        
        // Get existing categories
        $existingCategories = Permission::all()->map(function($p) {
            return explode(' ', $p->name)[1] ?? 'general';
        })->unique()->values();

        return view('admin.permissions.edit', compact('permission', 'currentCategory', 'existingCategories'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        Gate::authorize('manage permissions');

        $request->validate([
            'name' => 'required|unique:permissions,name,' . $permission->id . '|max:255',
            'guard_name' => 'nullable|string|max:255',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);

        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
        ]);

        if ($request->has('roles')) {
            $permission->syncRoles($request->roles);
        } else {
            $permission->syncRoles([]);
        }

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        Gate::authorize('manage permissions');

        // Check if permission is used by any roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                           ->with('error', 'Cannot delete permission that is assigned to roles.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission deleted successfully.');
    }
}