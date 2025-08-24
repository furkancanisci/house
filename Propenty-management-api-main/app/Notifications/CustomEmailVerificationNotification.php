<?php

namespace App\Notifications;

use App\Models\EmailVerificationLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class CustomEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected EmailVerificationLog $verificationLog;
    public $locale;

    /**
     * Create a new notification instance.
     */
    public function __construct(EmailVerificationLog $verificationLog, string $locale = 'en')
    {
        $this->verificationLog = $verificationLog;
        $this->locale = $locale;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $user = $notifiable;
        
        // Set locale for email content
        app()->setLocale($this->locale);
        
        $mailMessage = (new MailMessage)
            ->subject($this->getSubject($user))
            ->view('emails.verification', [
                'user' => $user,
                'verificationUrl' => $verificationUrl,
                'verificationLog' => $this->verificationLog,
                'locale' => $this->locale,
                'expiresAt' => $this->verificationLog->expires_at,
                'companyName' => config('app.name', 'Best Trend'),
            ]);

        return $mailMessage;
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        $baseUrl = config('app.frontend_url', 'https://house-6g6m.onrender.com');
        
        // Create encrypted payload with all verification data
        $payload = [
            'user_id' => $notifiable->getKey(),
            'email' => $notifiable->getEmailForVerification(),
            'token' => $this->verificationLog->verification_token,
            'expires_at' => $this->verificationLog->expires_at->timestamp,
            'created_at' => now()->timestamp,
            'nonce' => Str::random(32), // Add randomness to prevent replay attacks
        ];
        
        // Encrypt the entire payload
        $encryptedPayload = Crypt::encryptString(json_encode($payload));
        
        // Create additional signature for integrity check
        $signature = hash_hmac('sha256', 
            $encryptedPayload . $this->verificationLog->verification_token,
            config('app.key')
        );
        
        return $baseUrl . '/verify-email?' . http_build_query([
            'data' => base64_encode($encryptedPayload),
            'signature' => $signature,
        ]);
    }

    /**
     * Get the email subject based on user type and locale.
     */
    protected function getSubject(User $user): string
    {
        $subjects = [
            'en' => [
                'property_owner' => 'Welcome to Best Trend - Verify Your Property Owner Account',
                'general_user' => 'Welcome to Best Trend - Verify Your Account',
                'default' => 'Welcome to Best Trend - Verify Your Email Address',
            ],
            'ar' => [
                'property_owner' => 'مرحباً بك في بيست ترند - تأكيد حساب مالك العقار',
                'general_user' => 'مرحباً بك في بيست ترند - تأكيد حسابك',
                'default' => 'مرحباً بك في بيست ترند - تأكيد عنوان البريد الإلكتروني',
            ],
        ];

        $localeSubjects = $subjects[$this->locale] ?? $subjects['en'];
        return $localeSubjects[$user->user_type] ?? $localeSubjects['default'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'verification_log_id' => $this->verificationLog->id,
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
            'user_type' => $notifiable->user_type,
            'locale' => $this->locale,
            'expires_at' => $this->verificationLog->expires_at,
        ];
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(object $notifiable): bool
    {
        // Don't send if user is already verified
        if ($notifiable->hasVerifiedEmail()) {
            return false;
        }

        // Don't send if verification log is expired
        if ($this->verificationLog->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $this->verificationLog->markAsFailed($exception->getMessage());
    }
}