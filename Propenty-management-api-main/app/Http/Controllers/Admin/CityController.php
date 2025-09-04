<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use App\Models\Neighborhood;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CityController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('view cities');

        $query = City::with('governorate')->withCount(['neighborhoods', 'properties']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('governorate_id')) {
            $query->where('governorate_id', $request->governorate_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $cities = $query->orderBy('created_at', 'desc')->paginate(15);
        $governorates = Governorate::active()->orderBy('name_ar')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.cities.partials.table', compact('cities'))->render(),
                'pagination' => $cities->links()->render()
            ]);
        }

        return view('admin.cities.index', compact('cities', 'governorates'));
    }

    public function create(Request $request)
    {
        Gate::authorize('manage cities');
        
        $governorates = Governorate::active()->orderBy('name_ar')->get();
        $selectedGovernorate = $request->get('governorate_id');
        
        return view('admin.cities.create', compact('governorates', 'selectedGovernorate'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage cities');

        $request->validate([
            'name_en' => 'required|string|max:255|unique:cities',
            'name_ar' => 'required|string|max:255|unique:cities',
            'name_ku' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:cities',
            'governorate_id' => 'nullable|exists:governorates,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        City::create($request->all());

        return redirect()->route('admin.cities.index')
                        ->with('success', 'City created successfully.');
    }

    public function show(City $city)
    {
        Gate::authorize('view cities');
        
        $city->load(['governorate', 'neighborhoods' => function($query) {
            $query->withCount('properties');
        }]);
        
        return view('admin.cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        Gate::authorize('manage cities');
        
        $governorates = Governorate::active()->orderBy('name_ar')->get();
        
        return view('admin.cities.edit', compact('city', 'governorates'));
    }

    public function update(Request $request, City $city)
    {
        Gate::authorize('manage cities');

        $request->validate([
            'name_en' => 'required|string|max:255|unique:cities,name_en,' . $city->id,
            'name_ar' => 'required|string|max:255|unique:cities,name_ar,' . $city->id,
            'name_ku' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:cities,slug,' . $city->id,
            'governorate_id' => 'nullable|exists:governorates,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean'
        ]);

        $city->update($request->all());

        return redirect()->route('admin.cities.index')
                        ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city)
    {
        Gate::authorize('manage cities');

        if ($city->properties()->count() > 0) {
            return redirect()->route('admin.cities.index')
                            ->with('error', 'Cannot delete city with associated properties.');
        }

        $city->delete();

        return redirect()->route('admin.cities.index')
                        ->with('success', 'City deleted successfully.');
    }

    public function neighborhoods(City $city)
    {
        Gate::authorize('manage neighborhoods');
        
        $neighborhoods = $city->neighborhoods()->withCount('properties')->paginate(15);
        
        return view('admin.cities.neighborhoods', compact('city', 'neighborhoods'));
    }

    public function storeNeighborhood(Request $request, City $city)
    {
        Gate::authorize('manage neighborhoods');

        $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'name_ku' => 'nullable|string|max:255',
            'slug' => 'required|string|max:255|unique:neighborhoods',
            'is_active' => 'boolean'
        ]);

        $city->neighborhoods()->create($request->all());

        return redirect()->route('admin.cities.neighborhoods', $city)
                        ->with('success', 'Neighborhood created successfully.');
    }

    public function destroyNeighborhood(Neighborhood $neighborhood)
    {
        Gate::authorize('manage neighborhoods');

        if ($neighborhood->properties()->count() > 0) {
            return back()->with('error', 'Cannot delete neighborhood with associated properties.');
        }

        $neighborhood->delete();

        return back()->with('success', 'Neighborhood deleted successfully.');
    }

    /**
     * Get cities by governorate for AJAX requests
     */
    public function getCitiesByGovernorate(Request $request)
    {
        $governorateId = $request->get('governorate_id');
        
        if (!$governorateId) {
            return response()->json([]);
        }

        $cities = City::where('governorate_id', $governorateId)
                     ->where('is_active', true)
                     ->orderBy('name_ar')
                     ->get(['id', 'name_en', 'name_ar']);

        return response()->json($cities);
    }

    /**
     * Get cities by state for AJAX requests (legacy method)
     */
    public function getCitiesByState(Request $request)
    {
        $state = $request->get('state');
        
        if (!$state) {
            return response()->json([]);
        }

        // Legacy method - return all active cities
        $cities = City::where('is_active', true)
                     ->orderBy('name_ar')
                     ->get(['id', 'name_en', 'name_ar']);

        return response()->json($cities);
    }

    /**
     * Toggle city status via AJAX
     */
    public function toggleStatus(City $city)
    {
        Gate::authorize('manage cities');
        
        $city->update(['is_active' => !$city->is_active]);
        
        return response()->json([
            'success' => true,
            'message' => $city->is_active ? 'تم تفعيل المدينة بنجاح' : 'تم إلغاء تفعيل المدينة بنجاح',
            'is_active' => $city->is_active
        ]);
    }

    /**
     * Get neighborhoods by city for AJAX requests
     */
    public function getNeighborhoodsByCity(Request $request)
    {
        $cityName = $request->get('city');
        
        if (!$cityName) {
            return response()->json([]);
        }

        // Find the city by name (either English or Arabic)
        $city = City::where('name_en', $cityName)
                   ->orWhere('name_ar', $cityName)
                   ->first();

        if (!$city) {
            return response()->json([]);
        }

        $neighborhoods = $city->neighborhoods()
                             ->where('is_active', true)
                             ->orderBy('name_en')
                             ->get(['id', 'name_en', 'name_ar']);

        return response()->json($neighborhoods);
    }
}