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

        // Redirect to properties admin page with pending filter
        return redirect()->route('admin.properties.index', ['filter[status]' => 'pending'])
                        ->with('info', 'Property moderation is now handled in the Properties section. Showing pending properties for approval.');
    }

    /**
     * Show a specific property for moderation.
     */
    public function show(Property $property)
    {
        Gate::authorize('moderate properties');

        $property->load(['user', 'media']);

        // Redirect to properties admin page to view the property
        return redirect()->route('admin.properties.show', $property)
                        ->with('info', 'Property moderation is now handled in the Properties section.');
    }

    /**
     * Approve a property.
     */
    public function approve(Request $request, Property $property)
    {
        // Redirect to PropertyController approve method which includes email notifications
        return app(\App\Http\Controllers\Admin\PropertyController::class)->approve($property);

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
        // Redirect to PropertyController reject method
        return app(\App\Http\Controllers\Admin\PropertyController::class)->reject($request, $property);
    }

    /**
     * Handle bulk actions on properties.
     */
    public function bulkAction(Request $request)
    {
        // Redirect to PropertyController bulk action method
        return app(\App\Http\Controllers\Admin\PropertyController::class)->bulkAction($request);
    }
}
