<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Check permissions
        $this->authorize('view dashboard');

        // Get KPI stats
        $stats = $this->getKpiStats();
        
        // Get recent activities
        $recentProperties = $this->getRecentProperties();
        $recentLeads = $this->getRecentLeads();
        
        // Get chart data
        $chartData = $this->getChartData();

        return view('admin.dashboard.index', compact(
            'stats',
            'recentProperties',
            'recentLeads',
            'chartData'
        ));
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request)
    {
        $this->authorize('view dashboard');
        
        $stats = $this->getKpiStats();
        
        if ($request->ajax()) {
            return response()->json($stats);
        }
        
        return $stats;
    }

    /**
     * Get chart data for dashboard.
     */
    public function charts(Request $request)
    {
        $this->authorize('view dashboard');
        
        $chartData = $this->getChartData();
        
        if ($request->ajax()) {
            return response()->json($chartData);
        }
        
        return $chartData;
    }

    /**
     * Get KPI statistics.
     */
    private function getKpiStats()
    {
        $totalProperties = Property::count();
        $activeProperties = Property::where('status', 'active')->count();
        $pendingApprovals = Property::where('status', 'pending')->count();
        $featuredProperties = Property::where('is_featured', true)->count();
        
        $totalUsers = User::count();
        $totalAgents = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Agent', 'Admin', 'SuperAdmin']);
        })->count();
        
        // Check if leads table exists
        try {
            $totalLeads = Lead::count();
            $newLeads = Lead::where('status', 'new')->count();
            $newLeadsThisWeek = Lead::where('created_at', '>=', Carbon::now()->subWeek())->count();
            $convertedLeads = Lead::whereNotNull('converted_at')->count();
            $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
        } catch (\Exception $e) {
            // Leads table doesn't exist yet
            $totalLeads = 0;
            $newLeads = 0;
            $newLeadsThisWeek = 0;
            $convertedLeads = 0;
            $conversionRate = 0;
        }
        
        // Property stats by listing type using property_type relationship
        $forSaleType = \App\Models\PropertyType::where('slug', 'for-sale')->first();
        $forRentType = \App\Models\PropertyType::where('slug', 'for-rent')->first();
        
        $forSale = $forSaleType ? Property::where('property_type_id', $forSaleType->id)->where('status', 'active')->count() : 0;
        $forRent = $forRentType ? Property::where('property_type_id', $forRentType->id)->where('status', 'active')->count() : 0;
        
        return [
            'properties' => [
                'total' => $totalProperties,
                'active' => $activeProperties,
                'pending' => $pendingApprovals,
                'featured' => $featuredProperties,
                'for_sale' => $forSale,
                'for_rent' => $forRent,
            ],
            'users' => [
                'total' => $totalUsers,
                'agents' => $totalAgents,
            ],
            'leads' => [
                'total' => $totalLeads,
                'new' => $newLeads,
                'this_week' => $newLeadsThisWeek,
                'converted' => $convertedLeads,
                'conversion_rate' => $conversionRate,
            ],
        ];
    }

    /**
     * Get recent properties for dashboard.
     */
    private function getRecentProperties()
    {
        return Property::with(['user', 'media', 'city'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'status' => $property->status,
                    'price' => $property->formatted_price,
                    'location' => ($property->city ?? 'Unknown') . ', ' . ($property->state ?? 'Syria'),
                    'owner' => $property->user->full_name ?? 'Unknown',
                    'created_at' => $property->created_at->diffForHumans(),
                    'image' => $property->getFirstMediaUrl('images', 'small'),
                ];
            });
    }

    /**
     * Get recent leads for dashboard.
     */
    private function getRecentLeads()
    {
        try {
            return Lead::with(['property', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($lead) {
                    return [
                        'id' => $lead->id,
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'phone' => $lead->phone,
                        'status' => $lead->status,
                        'source' => $lead->source,
                        'property' => $lead->property?->title,
                        'assigned_to' => $lead->assignedTo?->full_name,
                        'created_at' => $lead->created_at->diffForHumans(),
                    ];
                });
        } catch (\Exception $e) {
            // Leads table doesn't exist yet, return empty collection
            return collect([]);
        }
    }

    /**
     * Get chart data for dashboard.
     */
    private function getChartData()
    {
        // Properties by day (last 30 days)
        $propertiesByDay = Property::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill in missing dates with 0 count
        $propertyChartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = $propertiesByDay->where('date', $date)->first()?->count ?? 0;
            $propertyChartData[] = [
                'date' => Carbon::parse($date)->format('M j'),
                'count' => $count,
            ];
        }

        // Properties by city (top 10) - using direct city field since city_id may not be populated
        $propertiesByCity = Property::select(
                'city as city_name', 
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'active')
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'city' => $item->city_name,
                    'count' => $item->count,
                ];
            });

        // Leads conversion funnel
        $leadsByStatus = Lead::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'count' => $item->count,
                ];
            });

        // Properties by type
        $propertiesByType = Property::select('property_types.name as type_name', DB::raw('COUNT(properties.id) as count'))
            ->join('property_types', 'properties.property_type_id', '=', 'property_types.id')
            ->where('properties.status', 'active')
            ->groupBy('property_types.id', 'property_types.name')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => ucfirst($item->type_name),
                    'count' => $item->count,
                ];
            });

        return [
            'properties_by_day' => $propertyChartData,
            'properties_by_city' => $propertiesByCity,
            'leads_by_status' => $leadsByStatus,
            'properties_by_type' => $propertiesByType,
        ];
    }
}