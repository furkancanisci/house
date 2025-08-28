<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view users');

        $query = User::with('roles')
                    ->withCount(['properties', 'leads'])
                    ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        if ($request->filled('email_verified')) {
            if ($request->email_verified == '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->paginate(15);
        $roles = Role::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.users.partials.table', compact('users'))->render(),
                'pagination' => $users->links()->render()
            ]);
        }

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        Gate::authorize('create users');
        
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create users');

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'email_verified' => 'boolean'
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')
                        ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        Gate::authorize('view users');
        
        $user->load(['roles', 'properties' => function($query) {
            $query->with('city')->latest()->take(10);
        }, 'leads' => function($query) {
            $query->with('property')->latest()->take(10);
        }]);
        
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        Gate::authorize('edit users');
        
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        Gate::authorize('edit users');

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
            'email_verified' => 'boolean'
        ]);

        $userData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => $request->boolean('email_verified') ? now() : null,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        
        if (auth()->user()->can('assign roles')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('admin.users.index')
                        ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete users');

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'You cannot delete your own account.');
        }

        if ($user->properties()->count() > 0) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Cannot delete user with associated properties. Transfer properties first.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(Request $request, User $user)
    {
        Gate::authorize('edit users');

        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'You cannot change your own status.'], 400);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return response()->json(['success' => "User {$status} successfully."]);
    }

    public function assignRole(Request $request, User $user)
    {
        Gate::authorize('assign roles');

        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $user->syncRoles([$request->role]);

        return response()->json(['success' => 'Role assigned successfully.']);
    }

    public function properties(User $user)
    {
        Gate::authorize('view users');
        
        $properties = $user->properties()
                          ->with(['city', 'media'])
                          ->latest()
                          ->paginate(15);
        
        return view('admin.users.properties', compact('user', 'properties'));
    }

    public function leads(User $user)
    {
        Gate::authorize('view users');
        
        $leads = $user->leads()
                     ->with(['property', 'property.city'])
                     ->latest()
                     ->paginate(15);
        
        return view('admin.users.leads', compact('user', 'leads'));
    }

    public function impersonate(Request $request, User $user)
    {
        Gate::authorize('impersonate users');

        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot impersonate yourself.');
        }

        session(['impersonating' => auth()->id()]);
        auth()->login($user);

        return redirect('/')->with('info', "You are now impersonating {$user->full_name}. Click 'Stop Impersonating' to return to your account.");
    }

    public function stopImpersonation()
    {
        if (!session('impersonating')) {
            return redirect('/admin')->with('error', 'You are not impersonating anyone.');
        }

        $originalUserId = session('impersonating');
        session()->forget('impersonating');
        
        auth()->loginUsingId($originalUserId);

        return redirect()->route('admin.users.index')->with('success', 'Stopped impersonating user.');
    }
}