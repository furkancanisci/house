<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EmailVerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'verification_token',
        'status',
        'sent_at',
        'verified_at',
        'expires_at',
        'ip_address',
        'user_agent',
        'metadata',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_VERIFIED = 'verified';
    const STATUS_EXPIRED = 'expired';
    const STATUS_FAILED = 'failed';

    /**
     * Get the user that owns the verification log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a unique verification token.
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new verification log entry.
     */
    public static function createForUser(User $user, array $metadata = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'verification_token' => self::generateToken(),
            'status' => self::STATUS_PENDING,
            'expires_at' => Carbon::now()->addHours(24), // 24 hours expiry
            'ip_address' => $metadata['ip_address'] ?? request()->ip(),
            'user_agent' => $metadata['user_agent'] ?? request()->userAgent(),
            'metadata' => array_merge([
                'user_type' => $user->user_type,
                'attempt_count' => 1,
            ], Arr::except($metadata, ['ip_address', 'user_agent'])),
        ]);
    }

    /**
     * Mark the verification as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the verification as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark the verification as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Check if the verification token is expired.
     */
    public function isExpired(): bool
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if the verification is still valid.
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_SENT && !$this->isExpired();
    }

    /**
     * Scope to get active (non-expired) verifications.
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_SENT]);
    }

    /**
     * Scope to get expired verifications.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now())
                    ->where('status', '!=', self::STATUS_VERIFIED);
    }

    /**
     * Get the latest verification log for a user.
     */
    public static function getLatestForUser(User $user): ?self
    {
        return self::where('user_id', $user->id)
                  ->orderBy('created_at', 'desc')
                  ->first();
    }

    /**
     * Clean up expired verification logs.
     */
    public static function cleanupExpired(): int
    {
        return self::expired()->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Increment attempt count in metadata.
     */
    public function incrementAttemptCount(): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['attempt_count'] = ($metadata['attempt_count'] ?? 0) + 1;
        $metadata['last_attempt_at'] = Carbon::now()->toISOString();
        
        $this->update(['metadata' => $metadata]);
    }
}