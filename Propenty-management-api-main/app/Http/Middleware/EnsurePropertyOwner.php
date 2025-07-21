<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePropertyOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Authentication required.',
                'error' => 'UNAUTHENTICATED',
            ], 401);
        }

        $user = auth()->user();

        // Check if user is a property owner
        if (!$user->isPropertyOwner()) {
            return response()->json([
                'message' => 'This action is only available to property owners.',
                'error' => 'INSUFFICIENT_PERMISSIONS',
                'required_user_type' => 'property_owner',
                'current_user_type' => $user->user_type,
            ], 403);
        }

        // Check if user is verified
        if (!$user->is_verified) {
            return response()->json([
                'message' => 'Please verify your email address to access this feature.',
                'error' => 'EMAIL_NOT_VERIFIED',
                'action_required' => 'email_verification',
            ], 403);
        }

        // Check if user account is active
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact support.',
                'error' => 'ACCOUNT_INACTIVE',
                'support_email' => 'support@example.com',
            ], 403);
        }

        return $next($request);
    }
}
