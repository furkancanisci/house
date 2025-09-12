<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Rate limiting for spam protection
        $key = 'contact-form:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => "Too many contact attempts. Please try again in {$seconds} seconds.",
                'errors' => ['rate_limit' => ["Too many attempts. Please wait {$seconds} seconds."]]
            ], 429);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Basic spam detection
        $isSpam = $this->detectSpam($request);

        try {
            $contactMessage = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'is_spam' => $isSpam,
            ]);

            // Increment rate limiter
            RateLimiter::hit($key, 300); // 5 minutes

            return response()->json([
                'success' => true,
                'message' => __('admin.message_sent_success'),
                'data' => [
                    'id' => $contactMessage->id,
                    'created_at' => $contactMessage->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending your message. Please try again.',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Filter by spam status
        if ($request->has('exclude_spam') && $request->exclude_spam) {
            $query->notSpam();
        }

        // Filter by read status
        if ($request->has('unread_only') && $request->unread_only) {
            $query->unread();
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function show(ContactMessage $contactMessage)
    {
        // Mark as read when viewed
        if (!$contactMessage->is_read) {
            $contactMessage->markAsRead();
        }

        return response()->json([
            'success' => true,
            'data' => $contactMessage
        ]);
    }

    public function markAsSpam(ContactMessage $contactMessage)
    {
        $contactMessage->markAsSpam();

        return response()->json([
            'success' => true,
            'message' => __('admin.message_marked_spam')
        ]);
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->markAsRead();

        return response()->json([
            'success' => true,
            'message' => __('admin.message_marked_read')
        ]);
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return response()->json([
            'success' => true,
            'message' => __('admin.message_deleted')
        ]);
    }

    private function detectSpam(Request $request): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'poker', 'loan', 'credit', 'debt', 'mortgage',
            'insurance', 'pharmacy', 'crypto', 'bitcoin', 'investment',
            'make money', 'work from home', 'get rich', 'click here',
            'free money', 'guaranteed', 'limited time', 'act now'
        ];

        $content = strtolower($request->name . ' ' . $request->email . ' ' . $request->message);

        foreach ($spamKeywords as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        // Check for excessive links
        $linkCount = preg_match_all('/https?:\/\//', $request->message);
        if ($linkCount > 2) {
            return true;
        }

        // Check for excessive repetition
        $words = str_word_count($request->message, 1);
        if (count($words) > 0) {
            $wordCounts = array_count_values(array_map('strtolower', $words));
            $maxCount = max($wordCounts);
            if ($maxCount > 5 && count($words) > 10) {
                return true;
            }
        }

        return false;
    }

    public function getSettings()
    {
        try {
            $settings = ContactSetting::getPublicSettings();
            
            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact settings',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }
}
