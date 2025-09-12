<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'unread':
                    $query->where('is_read', false);
                    break;
                case 'read':
                    $query->where('is_read', true);
                    break;
                case 'spam':
                    $query->where('is_spam', true);
                    break;
                case 'not_spam':
                    $query->where('is_spam', false);
                    break;
            }
        }

        $messages = $query->orderBy('created_at', 'desc')
                         ->paginate(20)
                         ->withQueryString();

        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::where('is_read', false)->count(),
            'spam' => ContactMessage::where('is_spam', true)->count(),
            'today' => ContactMessage::whereDate('created_at', today())->count(),
        ];

        return view('admin.contact.index', compact('messages', 'stats'));
    }

    public function show(ContactMessage $contactMessage)
    {
        // Mark as read when viewed
        if (!$contactMessage->is_read) {
            $contactMessage->markAsRead();
        }

        return view('admin.contact.show', compact('contactMessage'));
    }

    public function markAsSpam(ContactMessage $contactMessage)
    {
        $contactMessage->markAsSpam();

        return redirect()->back()->with('success', __('admin.message_marked_spam'));
    }

    public function markAsRead(ContactMessage $contactMessage)
    {
        $contactMessage->markAsRead();

        return redirect()->back()->with('success', __('admin.message_marked_read'));
    }

    public function destroy(ContactMessage $contactMessage)
    {
        $contactMessage->delete();

        return redirect()->route('admin.contact.index')
                        ->with('success', __('admin.message_deleted'));
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_spam,delete',
            'messages' => 'required|array',
            'messages.*' => 'exists:contact_messages,id'
        ]);

        $messages = ContactMessage::whereIn('id', $request->messages);

        switch ($request->action) {
            case 'mark_read':
                $messages->update(['is_read' => true, 'read_at' => now()]);
                $message = __('admin.messages_bulk_action');
                break;
            case 'mark_spam':
                $messages->update(['is_spam' => true]);
                $message = __('admin.messages_bulk_action');
                break;
            case 'delete':
                $messages->delete();
                $message = __('admin.messages_bulk_action');
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    public function settings()
    {
        $settings = ContactSetting::ordered()->get();
        return view('admin.contact.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = ContactSetting::all();
        
        foreach ($settings as $setting) {
            $value = $request->input('settings.' . $setting->key);
            $isDisplayed = $request->has('display.' . $setting->key);
            
            // Validation based on type and requirements
            if ($setting->is_required && $isDisplayed && empty($value)) {
                return redirect()->back()
                    ->withErrors(['settings.' . $setting->key => "The {$setting->label} field is required when displayed."])
                    ->withInput();
            }
            
            // Type-specific validation (only if displayed and not empty)
            if ($isDisplayed && !empty($value)) {
                switch ($setting->type) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return redirect()->back()
                                ->withErrors(['settings.' . $setting->key => "The {$setting->label} must be a valid email address."])
                                ->withInput();
                        }
                        break;
                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            return redirect()->back()
                                ->withErrors(['settings.' . $setting->key => "The {$setting->label} must be a valid URL."])
                                ->withInput();
                        }
                        break;
                    case 'phone':
                        // Basic phone validation - you can make this more specific
                        if (!preg_match('/^[\+]?[1-9][\d]{0,15}$/', preg_replace('/[\s\-\(\)]+/', '', $value))) {
                            return redirect()->back()
                                ->withErrors(['settings.' . $setting->key => "The {$setting->label} must be a valid phone number."])
                                ->withInput();
                        }
                        break;
                }
            }
            
            $setting->update([
                'value' => $value,
                'is_displayed' => $isDisplayed
            ]);
        }
        
        return redirect()->back()->with('success', __('admin.updated_successfully'));
    }
}
