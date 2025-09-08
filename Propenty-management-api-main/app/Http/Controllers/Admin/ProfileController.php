<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the user's profile form.
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::exists('public/' . $user->avatar)) {
                Storage::delete('public/' . $user->avatar);
            }

            // Store new avatar
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

        // Update user information
        $user->update($validated);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Show the change password form.
     */
    public function showChangePassword()
    {
        return view('admin.profile.change-password');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Show the account settings page.
     */
    public function settings()
    {
        $user = Auth::user();
        return view('admin.profile.settings', compact('user'));
    }

    /**
     * Update account settings.
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'notification_email' => 'boolean',
            'notification_sms' => 'boolean',
            'newsletter' => 'boolean',
            'marketing_emails' => 'boolean',
            'timezone' => 'nullable|string|max:50',
            'language' => 'nullable|string|max:10',
            'date_format' => 'nullable|string|max:20',
        ]);

        // Store settings in user meta or settings table
        // For now, we'll store in session or user attributes if they exist
        foreach ($validated as $key => $value) {
            // You might want to store these in a user_settings table or user meta
            $request->session()->put('user_settings.' . $key, $value);
        }

        return redirect()->route('admin.profile.settings')
            ->with('success', 'Settings updated successfully!');
    }

    /**
     * Delete user account (soft delete).
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        // Log out the user
        Auth::logout();

        // Soft delete the user account
        $user->delete();

        return redirect()->route('admin.login')
            ->with('success', 'Your account has been deleted.');
    }
}
