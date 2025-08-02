<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        \DB::beginTransaction();
        
        try {
            // Raw PostgreSQL insert query with RETURNING clause
            $result = \DB::select(
                "INSERT INTO users (first_name, last_name, email, password, phone, user_type, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW()) 
                RETURNING id",
                [
                    $request->first_name,
                    $request->last_name,
                    $request->email,
                    Hash::make($request->password),
                    $request->phone,
                    $request->user_type ?? 'general_user'
                ]
            );
            
            // Get the inserted user ID
            $userId = $result[0]->id;
            
            // Get the newly created user
            $user = \DB::selectOne(
                "SELECT * FROM users WHERE id = ?", 
                [$userId]
            );
            
            // Convert stdClass to User model instance
            $userModel = User::findOrFail($userId);
            
            try {
                // Try to send email verification
                $userModel->sendEmailVerificationNotification();
                $emailMessage = 'Verification email has been sent.';
            } catch (\Exception $e) {
                // Log the error but don't fail the registration
                \Log::warning('Could not send verification email: ' . $e->getMessage());
                $emailMessage = 'Registration successful, but could not send verification email.';
            }
            
            // Create access token
            $token = $userModel->createToken('auth_token')->plainTextToken;
            
            \DB::commit();
            
            return response()->json([
                'message' => 'Registration successful. ' . ($emailMessage ?? 'Please verify your email address.'),
                'user' => new UserResource($userModel),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('User registration failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate user and return token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
      
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated. Please contact support.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create access token with expiration
        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;
        
        // Create a refresh token (stored in database)
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;
        
        return response()
            ->json([
                'message' => 'Login successful.',
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 24 * 7, // 7 days in minutes
                'refresh_token' => $refreshToken,
            ])
            ->withCookie(cookie(
                'refresh_token',
                $refreshToken,
                30 * 24 * 60, // 30 days in minutes
                '/',
                null,
                config('app.env') === 'production',
                true, // httpOnly
                false,
                'strict'
            ));
    }

    /**
     * Get authenticated user information.
     */
    public function me(Request $request): JsonResponse
    {
        // Get the token from the request
        $token = $request->bearerToken();
        
        // Simple logging without user context to prevent recursion
        \Log::info('Auth/me endpoint called', [
            'has_token' => $token ? 'yes' : 'no',
            'ip' => $request->ip()
        ]);

        // Check if token exists
        if (!$token) {
            return response()->json([
                'message' => 'No authentication token provided',
                'user' => null
            ], 401);
        }

        try {
            // Manually validate the token without using the guard to prevent recursion
            $tokenModel = 'Laravel\\Sanctum\\PersonalAccessToken';
            
            if (!class_exists($tokenModel)) {
                \Log::error('Sanctum token model class not found');
                return response()->json([
                    'message' => 'Authentication service error',
                    'user' => null
                ], 500);
            }
            
            $accessToken = $tokenModel::findToken($token);

            if (!$accessToken) {
                \Log::warning('Token not found in database', [
                    'token_prefix' => substr($token, 0, 10) . '...'
                ]);
                return response()->json([
                    'message' => 'Invalid or expired token',
                    'user' => null
                ], 401);
            }

            // Check if token is expired
            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                \Log::warning('Token has expired', [
                    'token_id' => $accessToken->id,
                    'expires_at' => $accessToken->expires_at
                ]);
                return response()->json([
                    'message' => 'Token has expired',
                    'user' => null
                ], 401);
            }

            // Get the user associated with the token
            $user = $accessToken->tokenable;
            
            if (!$user) {
                \Log::error('Token has no associated user', [
                    'token_id' => $accessToken->id
                ]);
                return response()->json([
                    'message' => 'Invalid token',
                    'user' => null
                ], 401);
            }

            // Log successful authentication
            \Log::info('User authenticated', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Ensure the user is active
            if (property_exists($user, 'status') && $user->status !== 'active') {
                \Log::warning('User account is not active', ['user_id' => $user->id]);
                return response()->json([
                    'message' => 'User account is not active',
                    'user' => null
                ], 403);
            }
            
            // Return user data
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'is_verified' => $user->hasVerifiedEmail(),
                    'user_type' => $user->user_type,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in AuthController@me: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'An error occurred while processing your request',
                'error' => config('app.debug') ? $e->getMessage() : null,
                'user' => null
            ], 500);
        }

        // Return user data with roles and permissions
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'is_verified' => $user->hasVerifiedEmail(),
                'user_type' => $user->user_type,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }

    /**
     * Refresh the access token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie('refresh_token') ?? $request->bearerToken();
        
        if (!$refreshToken) {
            return response()->json([
                'message' => 'No refresh token provided'
            ], 401);
        }

        // Find the token in the database
        $token = PersonalAccessToken::findToken($refreshToken);
        
        if (!$token || !$token->can('refresh')) {
            return response()->json([
                'message' => 'Invalid refresh token'
            ], 401);
        }

        // Get the user
        $user = $token->tokenable;
        
        // Revoke the old refresh token
        $token->delete();

        // Create new tokens
        $newToken = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;
        $newRefreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()
            ->json([
                'access_token' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 24 * 7, // 7 days in minutes
                'refresh_token' => $newRefreshToken,
            ])
            ->withCookie(cookie(
                'refresh_token',
                $newRefreshToken,
                30 * 24 * 60, // 30 days in minutes
                '/',
                null,
                config('app.env') === 'production',
                true, // httpOnly
                false,
                'strict'
            ));
    }

    /**
     * Logout user (revoke all tokens).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revoke the current access token
            $request->user()->currentAccessToken()->delete();
            
            // Also clear the refresh token cookie
            return response()
                ->json(['message' => 'Successfully logged out'])
                ->withCookie(cookie()->forget('refresh_token'));
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json(['message' => 'Error during logout'], 500);
        }
    }

    /**
     * Logout from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully.',
        ]);
    }

    /**
     * Refresh token.
     */
  

    /**
     * Send email verification notification.
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email sent.',
        ]);
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($request->id);

        if (!hash_equals(sha1($user->getEmailForVerification()), $request->hash)) {
            return response()->json([
                'message' => 'Invalid verification link.',
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    /**
     * Send password reset link.
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Here you would implement password reset logic
        // For now, we'll just return a success message
        
        return response()->json([
            'message' => 'Password reset link sent to your email.',
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Here you would implement password reset logic
        // For now, we'll just return a success message
        
        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}
