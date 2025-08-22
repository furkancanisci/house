<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view leads');

        $query = Lead::with(['property', 'assignedTo']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Source filter
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Assigned to filter
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Quality score filter
        if ($request->filled('quality_score')) {
            $query->where('quality_score', '>=', $request->quality_score);
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get agents for assignment dropdown
        $agents = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Agent', 'Admin', 'SuperAdmin']);
        })->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.leads.partials.table', compact('leads'))->render(),
                'pagination' => $leads->links()->render()
            ]);
        }

        return view('admin.leads.index', compact('leads', 'agents'));
    }

    public function show(Lead $lead)
    {
        Gate::authorize('view leads');
        
        $lead->load(['property', 'assignedTo']);
        
        // Get agents for assignment
        $agents = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Agent', 'Admin', 'SuperAdmin']);
        })->get();
        
        return view('admin.leads.show', compact('lead', 'agents'));
    }

    public function create()
    {
        Gate::authorize('create leads');
        
        $properties = Property::where('status', 'published')->get();
        $agents = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Agent', 'Admin', 'SuperAdmin']);
        })->get();
        
        return view('admin.leads.create', compact('properties', 'agents'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create leads');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'required|in:website,contact_form,listing_inquiry,phone,walk_in',
            'status' => 'required|in:new,in_progress,qualified,unqualified,closed',
            'property_id' => 'nullable|exists:properties,id',
            'message' => 'nullable|string',
            'property_type' => 'nullable|string|max:255',
            'listing_type' => 'nullable|in:rent,sale',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'move_in_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'quality_score' => 'nullable|integer|min:1|max:10',
            'internal_notes' => 'nullable|string'
        ]);

        $leadData = $request->all();
        
        if ($request->filled('assigned_to')) {
            $leadData['assigned_at'] = now();
        }

        $lead = Lead::create($leadData);

        return redirect()->route('admin.leads.index')
                        ->with('success', 'Lead created successfully.');
    }

    public function edit(Lead $lead)
    {
        Gate::authorize('edit leads');
        
        $properties = Property::where('status', 'published')->get();
        $agents = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Agent', 'Admin', 'SuperAdmin']);
        })->get();
        
        return view('admin.leads.edit', compact('lead', 'properties', 'agents'));
    }

    public function update(Request $request, Lead $lead)
    {
        Gate::authorize('edit leads');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'required|in:website,contact_form,listing_inquiry,phone,walk_in',
            'status' => 'required|in:new,in_progress,qualified,unqualified,closed',
            'property_id' => 'nullable|exists:properties,id',
            'message' => 'nullable|string',
            'property_type' => 'nullable|string|max:255',
            'listing_type' => 'nullable|in:rent,sale',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'move_in_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'quality_score' => 'nullable|integer|min:1|max:10',
            'internal_notes' => 'nullable|string'
        ]);

        $leadData = $request->all();
        
        // Track assignment changes
        if ($request->filled('assigned_to') && $lead->assigned_to != $request->assigned_to) {
            $leadData['assigned_at'] = now();
        }
        
        // Track conversion
        if ($request->status == 'closed' && $lead->status != 'closed') {
            $leadData['converted_at'] = now();
        }

        $lead->update($leadData);

        return redirect()->route('admin.leads.index')
                        ->with('success', 'Lead updated successfully.');
    }

    public function destroy(Lead $lead)
    {
        Gate::authorize('delete leads');

        $lead->delete();

        return redirect()->route('admin.leads.index')
                        ->with('success', 'Lead deleted successfully.');
    }

    public function assign(Request $request, Lead $lead)
    {
        Gate::authorize('assign leads');

        $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        $lead->update([
            'assigned_to' => $request->agent_id,
            'assigned_at' => now()
        ]);

        return response()->json(['success' => 'Lead assigned successfully.']);
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        Gate::authorize('edit leads');

        $request->validate([
            'status' => 'required|in:new,in_progress,qualified,unqualified,closed'
        ]);

        $updateData = ['status' => $request->status];
        
        if ($request->status == 'closed') {
            $updateData['converted_at'] = now();
        }

        $lead->update($updateData);

        return response()->json(['success' => 'Lead status updated successfully.']);
    }

    public function addNote(Request $request, Lead $lead)
    {
        Gate::authorize('edit leads');

        $request->validate([
            'note' => 'required|string'
        ]);

        $existingNotes = $lead->internal_notes ?? '';
        $newNote = sprintf(
            "[%s - %s]: %s\n",
            now()->format('Y-m-d H:i'),
            auth()->user()->full_name,
            $request->note
        );

        $lead->update([
            'internal_notes' => $existingNotes . $newNote,
            'last_contacted_at' => now(),
            'contact_attempts' => $lead->contact_attempts + 1
        ]);

        return response()->json(['success' => 'Note added successfully.']);
    }

    public function exportCsv(Request $request)
    {
        Gate::authorize('export leads');

        $query = Lead::with(['property', 'assignedTo']);

        // Apply filters (same as index method)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $leads = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Source', 'Status', 
                'Property', 'Budget Min', 'Budget Max', 'Bedrooms', 'Bathrooms',
                'Assigned To', 'Quality Score', 'Created At', 'Last Contacted'
            ]);
            
            // Data rows
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->source,
                    $lead->status,
                    $lead->property?->title,
                    $lead->budget_min,
                    $lead->budget_max,
                    $lead->bedrooms,
                    $lead->bathrooms,
                    $lead->assignedTo?->full_name,
                    $lead->quality_score,
                    $lead->created_at->format('Y-m-d H:i'),
                    $lead->last_contacted_at?->format('Y-m-d H:i')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}