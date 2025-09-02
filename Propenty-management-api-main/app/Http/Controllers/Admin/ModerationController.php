<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ModerationController extends Controller
{
    /**
     * Display the moderation queue.
     */
    public function index(Request $request)
    {
        Gate::authorize('moderate properties');

        $query = Property::with(['user', 'media'])
                        ->where('status', 'pending')
                        ->latest();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('street_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $properties = $query->paginate(20);

        // Get filter options
        $propertyTypes = Property::select('property_type')
                               ->distinct()
                               ->whereNotNull('property_type')
                               ->pluck('property_type')
                               ->sort();

        return view('admin.moderation.index', compact('properties', 'propertyTypes'));
    }

    /**
     * Show a specific property for moderation.
     */
    public function show(Property $property)
    {
        Gate::authorize('moderate properties');

        $property->load(['user', 'media']);

        return view('admin.moderation.show', compact('property'));
    }

    /**
     * Approve a property.
     */
    public function approve(Request $request, Property $property)
    {
        Gate::authorize('moderate properties');

        $property->update([
            'status' => 'active',
            'published_at' => now(),
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);

        // Log the moderation action (requires Spatie Activity Log package)
        // Activity::create([
        //     'subject_type' => get_class($property),
        //     'subject_id' => $property->id,
        //     'causer_type' => get_class(Auth::user()),
        //     'causer_id' => Auth::id(),
        //     'description' => 'Property approved for publication',
        //     'properties' => [
        //         'action' => 'approved',
        //         'notes' => $request->notes,
        //         'previous_status' => 'pending'
        //     ]
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Property approved successfully.'
        ]);
    }

    /**
     * Reject a property.
     */
    public function reject(Request $request, Property $property)
    {
        Gate::authorize('moderate properties');

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        $property->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'moderated_by' => Auth::id(),
            'moderated_at' => now(),
        ]);

        // Log the moderation action (requires Spatie Activity Log package)
        // Activity::create([
        //     'subject_type' => get_class($property),
        //     'subject_id' => $property->id,
        //     'causer_type' => get_class(Auth::user()),
        //     'causer_id' => Auth::id(),
        //     'description' => 'Property rejected during moderation',
        //     'properties' => [
        //         'action' => 'rejected',
        //         'reason' => $request->reason,
        //         'notes' => $request->notes,
        //         'previous_status' => 'pending'
        //     ]
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Property rejected successfully.'
        ]);
    }

    /**
     * Handle bulk actions on properties.
     */
    public function bulkAction(Request $request)
    {
        Gate::authorize('moderate properties');

        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'property_ids' => 'required|array',
            'property_ids.*' => 'exists:properties,id',
            'reason' => 'required_if:action,reject|string|max:1000'
        ]);

        $properties = Property::whereIn('id', $request->property_ids);
        $count = $properties->count();

        switch ($request->action) {
            case 'approve':
                $properties->update([
                    'status' => 'active',
                    'published_at' => now(),
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                ]);
                $message = "Successfully approved {$count} properties.";
                break;

            case 'reject':
                $properties->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->reason,
                    'moderated_by' => Auth::id(),
                    'moderated_at' => now(),
                ]);
                $message = "Successfully rejected {$count} properties.";
                break;

            case 'delete':
                $properties->delete();
                $message = "Successfully deleted {$count} properties.";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}