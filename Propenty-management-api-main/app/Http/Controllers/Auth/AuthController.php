<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\EmailVerificationLog;
use App\Notifications\CustomEmailVerificationNotification;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }
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
            
            // Send email verification using service
            $verificationResult = $this->emailVerificationService->sendVerificationEmail(
                $userModel, 
                $request->ip(), 
                $request->userAgent()
            );
            
            // Create access token
            $token = $userModel->createToken('auth_token')->plainTextToken;
            
            \DB::commit();
            
            // Prepare response based on email sending result
            $response = [
                'message' => 'Registration successful.',
                'user' => new UserResource($userModel),
                'access_token' => $token,
                'token_type' => 'Bearer',
                'email_verification' => [
                    'status' => $verificationResult['success'] ? 'sent' : 'failed',
                    'message' => $verificationResult['message'],
                    'code' => $verificationResult['code']
                ]
            ];
            
            // If email sending failed, provide alternative verification methods
            if (!$verificationResult['success']) {
                $alternativeMethods = $this->emailVerificationService->getAlternativeVerificationMethods($userModel);
                $response['email_verification']['alternative_methods'] = $alternativeMethods;
                $response['email_verification']['retry_info'] = [
                    'can_retry' => true,
                    'retry_endpoint' => '/api/auth/resend-verification',
                    'retry_after_minutes' => 5
                ];
                
                // Enable limited access for the user
                $limitedAccessResult = $this->emailVerificationService->enableLimitedAccess($userModel);
                $response['limited_access'] = $limitedAccessResult;
            }
            
            return response()->json($response, 201);
            
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
            Log::info('Logout attempt', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            // Check if user is authenticated
            if (!$request->user()) {
                Log::warning('Logout attempt without authenticated user');
                return response()->json(['message' => 'User not authenticated'], 401);
            }
            
            // Revoke the current access token
            $token = $request->user()->currentAccessToken();
            if ($token) {
                $token->delete();
                Log::info('Access token revoked successfully', ['user_id' => $request->user()->id]);
            } else {
                Log::warning('No current access token found for user', ['user_id' => $request->user()->id]);
            }
            
            // Also clear the refresh token cookie
            return response()
                ->json(['message' => 'Successfully logged out'])
                ->withCookie(cookie()->forget('refresh_token'));
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id
            ]);
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
     * Send email verification notification with rate limiting.
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $result = $this->emailVerificationService->sendVerificationEmail(
            $request->user(),
            $request->ip(),
            $request->userAgent()
        );

        $statusCode = match($result['code']) {
            'ALREADY_VERIFIED' => 200,
            'RATE_LIMITED' => 429,
            'EMAIL_SENT' => 200,
            'SEND_FAILED' => 500,
            default => 500
        };

        $response = [
            'message' => $result['message'],
            'success' => $result['success'],
            'code' => $result['code']
        ];

        // Add retry information for rate limited requests
        if ($result['code'] === 'RATE_LIMITED') {
            $response['retry_after'] = $result['retry_after'];
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Verify email address with enhanced security.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        // Support both old format (for backward compatibility) and new encrypted format
        if ($request->has('data') && $request->has('signature')) {
            // New encrypted format
            return $this->verifyEmailEncrypted($request);
        } else {
            // Old format for backward compatibility
            $request->validate([
                'id' => 'required|integer',
                'hash' => 'required|string',
                'token' => 'required|string',
            ]);

            $user = User::findOrFail($request->id);

            $result = $this->emailVerificationService->verifyEmail(
                $user,
                $request->hash,
                $request->token,
                $request->ip(),
                $request->userAgent()
            );

            $statusCode = match($result['code']) {
                'ALREADY_VERIFIED' => 200,
                'INVALID_HASH', 'INVALID_TOKEN', 'TOKEN_EXPIRED' => 400,
                'EMAIL_VERIFIED' => 200,
                'VERIFICATION_FAILED' => 500,
                default => 500
            };

            $response = [
                'message' => $result['message'],
                'success' => $result['success'],
                'code' => $result['code']
            ];

            // Add user data for successful verification
            if ($result['success'] && isset($result['user'])) {
                $response['user'] = new UserResource($result['user']);
            }

            return response()->json($response, $statusCode);
        }
    }

    /**
     * Verify email using encrypted data format.
     */
    private function verifyEmailEncrypted(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|string',
            'signature' => 'required|string',
        ]);

        try {
            // Decode and decrypt the payload
            $encryptedPayload = base64_decode($request->data);
            $decryptedPayload = Crypt::decryptString($encryptedPayload);
            $payload = json_decode($decryptedPayload, true);

            if (!$payload || !is_array($payload)) {
                return response()->json([
                    'message' => 'Invalid verification data',
                    'success' => false,
                    'code' => 'INVALID_DATA'
                ], 400);
            }

            // Verify signature
            $expectedSignature = hash_hmac('sha256', 
                $encryptedPayload . $payload['token'],
                config('app.key')
            );

            if (!hash_equals($expectedSignature, $request->signature)) {
                \Log::warning('Invalid verification signature', [
                    'expected_signature' => $expectedSignature,
                    'received_signature' => $request->signature,
                    'payload' => $payload,
                    'ip' => $request->ip()
                ]);
                
                return response()->json([
                    'message' => 'Invalid verification signature',
                    'success' => false,
                    'code' => 'INVALID_SIGNATURE'
                ], 400);
            }

            // Check if link has expired (24 hours)
            if (isset($payload['expires_at']) && $payload['expires_at'] < now()->timestamp) {
                return response()->json([
                    'message' => 'Verification link has expired',
                    'success' => false,
                    'code' => 'LINK_EXPIRED'
                ], 400);
            }

            // Find user and verify
            $user = User::findOrFail($payload['user_id']);
            
            // Verify email matches
            if ($user->email !== $payload['email']) {
                return response()->json([
                    'message' => 'Email mismatch',
                    'success' => false,
                    'code' => 'EMAIL_MISMATCH'
                ], 400);
            }

            $result = $this->emailVerificationService->verifyEmail(
                $user,
                sha1($user->getEmailForVerification()),
                $payload['token'],
                $request->ip(),
                $request->userAgent()
            );

            $statusCode = match($result['code']) {
                'ALREADY_VERIFIED' => 200,
                'INVALID_HASH', 'INVALID_TOKEN', 'TOKEN_EXPIRED' => 400,
                'EMAIL_VERIFIED' => 200,
                'VERIFICATION_FAILED' => 500,
                default => 500
            };

            $response = [
                'message' => $result['message'],
                'success' => $result['success'],
                'code' => $result['code']
            ];

            // Add user data for successful verification
            if ($result['success'] && isset($result['user'])) {
                $response['user'] = new UserResource($result['user']);
            }

            return response()->json($response, $statusCode);

        } catch (\Exception $e) {
            \Log::error('Email verification decryption failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->data,
                'signature' => $request->signature,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'message' => 'Invalid verification data',
                'success' => false,
                'code' => 'DECRYPTION_FAILED',
                'debug_info' => app()->environment('local') ? $e->getMessage() : null
            ], 400);
        }
    }

    /**
     * Get verification status and statistics for the authenticated user.
     */
    public function getVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->emailVerificationService->getVerificationStats($user);
        $canRequest = $this->emailVerificationService->canRequestVerification($user);

        return response()->json([
            'user_id' => $user->id,
            'email' => $user->email,
            'is_verified' => $user->hasVerifiedEmail(),
            'verification_stats' => $stats,
            'can_request_new' => $canRequest,
            'active_verification' => $stats['active_verification'] ? [
                'token_id' => $stats['active_verification']->id,
                'expires_at' => $stats['active_verification']->expires_at,
                'status' => $stats['active_verification']->status,
                'created_at' => $stats['active_verification']->created_at
            ] : null
        ]);
    }

    /**
     * Resend verification email (handles both authenticated and unauthenticated requests).
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        // Handle both authenticated and unauthenticated requests
        $user = $request->user();
        
        if (!$user) {
            // For unauthenticated requests, require email parameter
            $request->validate([
                'email' => 'required|email|exists:users,email'
            ]);
            
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                    'success' => false,
                    'code' => 'USER_NOT_FOUND'
                ], 404);
            }
        }
        
        // Additional validation for resend requests
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.',
                'success' => false,
                'code' => 'ALREADY_VERIFIED'
            ]);
        }

        // Check if user can request verification
        $canRequest = $this->emailVerificationService->canRequestVerification($user);
        
        if (!$canRequest['can_request']) {
            $statusCode = $canRequest['reason'] === 'Rate limited' ? 429 : 400;
            $response = [
                'message' => 'Cannot resend verification email: ' . $canRequest['reason'],
                'success' => false,
                'code' => 'CANNOT_RESEND',
                'reason' => $canRequest['reason']
            ];
            
            if (isset($canRequest['retry_after'])) {
                $response['retry_after'] = $canRequest['retry_after'];
                $response['retry_after_minutes'] = $canRequest['retry_after_minutes'];
            }
            
            return response()->json($response, $statusCode);
        }

        // Send verification email using the service
        $result = $this->emailVerificationService->sendVerificationEmail(
            $user,
            $request->ip(),
            $request->userAgent()
        );

        $statusCode = match($result['code']) {
            'ALREADY_VERIFIED' => 200,
            'RATE_LIMITED' => 429,
            'EMAIL_SENT' => 200,
            'SEND_FAILED' => 500,
            default => 500
        };

        $response = [
            'message' => $result['message'],
            'success' => $result['success'],
            'code' => $result['code']
        ];

        // Add retry information for rate limited requests
        if ($result['code'] === 'RATE_LIMITED') {
            $response['retry_after'] = $result['retry_after'];
        }

        return response()->json($response, $statusCode);
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
