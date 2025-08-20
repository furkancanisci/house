<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationLog;
use App\Notifications\CustomEmailVerificationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmailVerificationService
{
    /**
     * Send email verification with rate limiting and logging.
     */
    public function sendVerificationEmail(User $user, string $ipAddress = null, string $userAgent = null): array
    {
        // Check if user is already verified
        if ($user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => 'Email already verified.',
                'code' => 'ALREADY_VERIFIED'
            ];
        }

        // Rate limiting: 3 attempts per hour per user
        $rateLimitKey = 'email-verification:' . $user->id;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return [
                'success' => false,
                'message' => 'Too many verification emails sent. Please try again in ' . ceil($seconds / 60) . ' minutes.',
                'code' => 'RATE_LIMITED',
                'retry_after' => $seconds
            ];
        }

        try {
            // Check for existing active verification
            $existingLog = $this->getActiveVerificationLog($user);
            
            if ($existingLog) {
                // Attempt to resend with retry mechanism
                $result = $this->sendEmailWithRetry($user, $existingLog, $ipAddress);
                if ($result['success']) {
                    Log::info('Email verification resent', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'token_id' => $existingLog->id,
                        'ip_address' => $ipAddress,
                        'attempts' => $result['attempts']
                    ]);
                }
                return $result;
            } else {
                // Create new verification log
                $verificationLog = EmailVerificationLog::createForUser($user, [
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent
                ]);
                
                // Attempt to send with retry mechanism
                $result = $this->sendEmailWithRetry($user, $verificationLog, $ipAddress);
                if ($result['success']) {
                    Log::info('New email verification sent', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'token_id' => $verificationLog->id,
                        'ip_address' => $ipAddress,
                        'attempts' => $result['attempts']
                    ]);
                }
                return $result;
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to send verification email. Please try again later.',
                'code' => 'SEND_FAILED',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify email with token validation.
     */
    public function verifyEmail(User $user, string $hash, string $token, string $ipAddress = null, string $userAgent = null): array
    {
        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => 'Email already verified.',
                'code' => 'ALREADY_VERIFIED'
            ];
        }

        // Verify the hash (Laravel's default verification)
        if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            Log::warning('Invalid verification hash', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $ipAddress
            ]);
            
            return [
                'success' => false,
                'message' => 'Invalid verification link.',
                'code' => 'INVALID_HASH'
            ];
        }

        // Find and validate the verification log with token
        $verificationLog = EmailVerificationLog::where('user_id', $user->id)
            ->where('verification_token', $token)
            ->where('status', 'sent')
            ->first();

        if (!$verificationLog) {
            Log::warning('Invalid verification token', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => $token,
                'ip_address' => $ipAddress
            ]);
            
            return [
                'success' => false,
                'message' => 'Invalid or expired verification token.',
                'code' => 'INVALID_TOKEN'
            ];
        }

        // Check if token is expired (24 hours)
        if ($verificationLog->isExpired()) {
            $verificationLog->update(['status' => 'expired']);
            
            Log::warning('Verification token expired', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_id' => $verificationLog->id,
                'expired_at' => $verificationLog->expires_at,
                'ip_address' => $ipAddress
            ]);
            
            return [
                'success' => false,
                'message' => 'Verification link has expired. Please request a new one.',
                'code' => 'TOKEN_EXPIRED'
            ];
        }

        try {
            // Mark email as verified (Laravel's default method)
            $user->markEmailAsVerified();
            
            // Also update the custom is_verified field
            $user->update(['is_verified' => true]);
            
            // Update verification log
            $verificationLog->markAsVerified();
            
            Log::info('Email verified successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_id' => $verificationLog->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);

            return [
                'success' => true,
                'message' => 'Email verified successfully.',
                'code' => 'EMAIL_VERIFIED',
                'user' => $user->fresh()
            ];
            
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_id' => $verificationLog->id,
                'error' => $e->getMessage(),
                'ip_address' => $ipAddress
            ]);
            
            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.',
                'code' => 'VERIFICATION_FAILED',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get active verification log for user.
     */
    public function getActiveVerificationLog(User $user): ?EmailVerificationLog
    {
        return EmailVerificationLog::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'sent'])
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get verification statistics for user.
     */
    public function getVerificationStats(User $user): array
    {
        $logs = EmailVerificationLog::where('user_id', $user->id)->get();
        
        return [
            'total_attempts' => $logs->count(),
            'sent_count' => $logs->where('status', 'sent')->count(),
            'failed_count' => $logs->where('status', 'failed')->count(),
            'expired_count' => $logs->where('status', 'expired')->count(),
            'verified_count' => $logs->where('status', 'verified')->count(),
            'last_attempt' => $logs->sortByDesc('created_at')->first()?->created_at,
            'is_verified' => $user->hasVerifiedEmail(),
            'active_verification' => $this->getActiveVerificationLog($user)
        ];
    }

    /**
     * Clean up expired verification logs.
     */
    public function cleanupExpiredLogs(): int
    {
        $expiredCount = EmailVerificationLog::where('expires_at', '<', now())
            ->whereIn('status', ['pending', 'sent'])
            ->update(['status' => 'expired']);
            
        Log::info('Cleaned up expired verification logs', [
            'expired_count' => $expiredCount
        ]);
        
        return $expiredCount;
    }

    /**
     * Check if user can request new verification email.
     */
    public function canRequestVerification(User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'can_request' => false,
                'reason' => 'Email already verified'
            ];
        }

        $rateLimitKey = 'email-verification:' . $user->id;
        
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return [
                'can_request' => false,
                'reason' => 'Rate limited',
                'retry_after' => $seconds,
                'retry_after_minutes' => ceil($seconds / 60)
            ];
        }

        return [
            'can_request' => true,
            'remaining_attempts' => 3 - RateLimiter::attempts($rateLimitKey)
        ];
    }

    /**
     * Send email with retry mechanism and exponential backoff.
     */
    private function sendEmailWithRetry(User $user, EmailVerificationLog $verificationLog, string $ipAddress = null): array
    {
        $maxAttempts = 3;
        $baseDelay = 1; // seconds
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // Attempt to send the email
                $user->notify(new CustomEmailVerificationNotification($verificationLog));
                $verificationLog->markAsSent();
                
                // Hit rate limiter only on successful send
                $rateLimitKey = 'email-verification:' . $user->id;
                RateLimiter::hit($rateLimitKey, 3600); // 1 hour
                
                return [
                    'success' => true,
                    'message' => 'Verification email sent successfully.',
                    'code' => 'EMAIL_SENT',
                    'attempts' => $attempt
                ];
                
            } catch (\Exception $e) {
                $lastException = $e;
                
                Log::warning('Email send attempt failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'attempt' => $attempt,
                    'max_attempts' => $maxAttempts,
                    'error' => $e->getMessage(),
                    'ip_address' => $ipAddress
                ]);
                
                // If this is not the last attempt, wait before retrying
                if ($attempt < $maxAttempts) {
                    $delay = $baseDelay * pow(2, $attempt - 1); // Exponential backoff: 1s, 2s, 4s
                    sleep($delay);
                }
            }
        }
        
        // All attempts failed, mark as failed and return error
        $verificationLog->markAsFailed($lastException->getMessage());
        
        Log::error('All email send attempts failed', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempts' => $maxAttempts,
            'final_error' => $lastException->getMessage(),
            'ip_address' => $ipAddress
        ]);
        
        return [
            'success' => false,
            'message' => 'Failed to send verification email after multiple attempts. Please try again later.',
            'code' => 'SEND_FAILED_RETRY_EXHAUSTED',
            'attempts' => $maxAttempts,
            'error' => $lastException->getMessage()
        ];
    }

    /**
     * Provide alternative verification method when email fails.
     */
    public function getAlternativeVerificationMethods(User $user): array
    {
        $methods = [];
        
        // Manual verification by admin (for critical cases)
        $methods[] = [
            'type' => 'admin_verification',
            'title' => 'Contact Support',
            'description' => 'Contact our support team for manual email verification.',
            'action' => 'mailto:support@example.com?subject=Email Verification Request&body=User ID: ' . $user->id
        ];
        
        // Temporary access with limited functionality
        $methods[] = [
            'type' => 'limited_access',
            'title' => 'Continue with Limited Access',
            'description' => 'Access the platform with limited functionality until email is verified.',
            'action' => 'continue_limited'
        ];
        
        return $methods;
    }

    /**
     * Enable limited access for unverified users.
     */
    public function enableLimitedAccess(User $user): array
    {
        // Update user status to allow limited access
        $user->update([
            'limited_access_enabled' => true,
            'limited_access_granted_at' => Carbon::now()
        ]);
        
        Log::info('Limited access enabled for unverified user', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);
        
        return [
            'success' => true,
            'message' => 'Limited access enabled. You can use basic features while we work on email verification.',
            'code' => 'LIMITED_ACCESS_ENABLED',
            'restrictions' => [
                'Cannot change email address',
                'Cannot access premium features',
                'Limited to basic functionality'
            ]
        ];
    }
}