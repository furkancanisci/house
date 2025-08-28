<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        Gate::authorize('view reports');

        // Get basic statistics
        $stats = [
            'total_properties' => Property::count(),
            'active_properties' => Property::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_leads' => Lead::count(),
        ];

        // Get monthly property statistics
        $monthlyProperties = Property::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', Carbon::now()->subMonths(12))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Get leads by status
        $leadsByStatus = Lead::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Get properties by type
        $propertiesByType = Property::select('property_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('property_type')
            ->groupBy('property_type')
            ->get();

        return view('admin.reports.index', compact(
            'stats',
            'monthlyProperties',
            'leadsByStatus',
            'propertiesByType'
        ));
    }

    /**
     * Display property reports.
     */
    public function properties(Request $request)
    {
        Gate::authorize('view reports');

        $query = Property::with(['user']);

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply type filter
        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        $properties = $query->latest()->paginate(20);

        // Get filter options
        $statuses = Property::distinct()->pluck('status')->filter();
        $propertyTypes = Property::distinct()->pluck('property_type')->filter();

        return view('admin.reports.properties', compact(
            'properties',
            'statuses',
            'propertyTypes'
        ));
    }

    /**
     * Display user reports.
     */
    public function users(Request $request)
    {
        Gate::authorize('view reports');

        $query = User::withCount(['properties', 'leads']);

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply role filter
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $users = $query->latest()->paginate(20);

        return view('admin.reports.users', compact('users'));
    }

    /**
     * Display revenue reports.
     */
    public function revenue(Request $request)
    {
        Gate::authorize('view reports');

        // This is a placeholder for revenue reporting
        // In a real application, you would have payment/transaction models
        
        $revenueData = [
            'total_revenue' => 0,
            'monthly_revenue' => [],
            'revenue_by_type' => [],
        ];

        return view('admin.reports.revenue', compact('revenueData'));
    }

    /**
     * Export reports data.
     */
    public function export(Request $request, $type)
    {
        Gate::authorize('export reports');

        switch ($type) {
            case 'properties':
                return $this->exportProperties($request);
            case 'users':
                return $this->exportUsers($request);
            case 'leads':
                return $this->exportLeads($request);
            default:
                abort(404);
        }
    }

    /**
     * Export properties data to CSV.
     */
    private function exportProperties(Request $request)
    {
        $properties = Property::with(['user'])->get();

        $filename = 'properties_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($properties) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID',
                'Title',
                'Type',
                'Status',
                'Price',
                'Owner',
                'Created At'
            ]);

            // CSV data
            foreach ($properties as $property) {
                fputcsv($file, [
                    $property->id,
                    $property->title,
                    $property->property_type,
                    $property->status,
                    $property->price,
                    $property->user ? $property->user->full_name : 'N/A',
                    $property->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export users data to CSV.
     */
    private function exportUsers(Request $request)
    {
        $users = User::withCount(['properties', 'leads'])->get();

        $filename = 'users_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Role',
                'Status',
                'Properties Count',
                'Leads Count',
                'Created At'
            ]);

            // CSV data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->full_name,
                    $user->email,
                    $user->roles->first()?->name ?? 'N/A',
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->properties_count,
                    $user->leads_count,
                    $user->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export leads data to CSV.
     */
    private function exportLeads(Request $request)
    {
        $leads = Lead::with(['user', 'property'])->get();

        $filename = 'leads_report_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($leads) {
            $file = fopen('php://output', 'w');
            
            // CSV header
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Phone',
                'Status',
                'Property',
                'User',
                'Created At'
            ]);

            // CSV data
            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->status,
                    $lead->property ? $lead->property->title : 'N/A',
                    $lead->user ? $lead->user->full_name : 'N/A',
                    $lead->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}