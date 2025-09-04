<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GovernorateController extends Controller
{
    /**
     * Display a listing of the governorates.
     */
    public function index(Request $request)
    {
        $query = Governorate::query();

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort_by', 'name_ar');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $governorates = $query->withCount('cities')->paginate(15);

        if ($request->ajax()) {
            return view('admin.governorates.partials.table', compact('governorates'))->render();
        }

        return view('admin.governorates.index', compact('governorates'));
    }

    /**
     * Show the form for creating a new governorate.
     */
    public function create()
    {
        return view('admin.governorates.create');
    }

    /**
     * Store a newly created governorate in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_ar' => 'required|string|max:255|unique:governorates,name_ar',
            'name_en' => 'required|string|max:255|unique:governorates,name_en',
            'name_ku' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ], [
            'name_ar.required' => 'الاسم باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم موجود مسبقاً',
            'name_en.required' => 'الاسم باللغة الإنجليزية مطلوب',
            'name_en.unique' => 'هذا الاسم موجود مسبقاً',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180',
        ]);

        // إنشاء slug تلقائياً
        $validated['slug'] = Str::slug($validated['name_en']);
        $validated['is_active'] = $request->has('is_active');

        $governorate = Governorate::create($validated);

        return redirect()->route('admin.governorates.index')
                        ->with('success', 'تم إنشاء المحافظة بنجاح');
    }

    /**
     * Display the specified governorate.
     */
    public function show(Governorate $governorate)
    {
        $governorate->load(['cities' => function ($query) {
            $query->withCount('neighborhoods');
        }]);

        return view('admin.governorates.show', compact('governorate'));
    }

    /**
     * Show the form for editing the specified governorate.
     */
    public function edit(Governorate $governorate)
    {
        return view('admin.governorates.edit', compact('governorate'));
    }

    /**
     * Update the specified governorate in storage.
     */
    public function update(Request $request, Governorate $governorate)
    {
        $validated = $request->validate([
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('governorates', 'name_ar')->ignore($governorate->id)
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('governorates', 'name_en')->ignore($governorate->id)
            ],
            'name_ku' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ], [
            'name_ar.required' => 'الاسم باللغة العربية مطلوب',
            'name_ar.unique' => 'هذا الاسم موجود مسبقاً',
            'name_en.required' => 'الاسم باللغة الإنجليزية مطلوب',
            'name_en.unique' => 'هذا الاسم موجود مسبقاً',
            'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90',
            'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180',
        ]);

        // تحديث slug إذا تغير الاسم الإنجليزي
        if ($validated['name_en'] !== $governorate->name_en) {
            $validated['slug'] = Str::slug($validated['name_en']);
        }

        $validated['is_active'] = $request->has('is_active');

        $governorate->update($validated);

        return redirect()->route('admin.governorates.index')
                        ->with('success', 'تم تحديث المحافظة بنجاح');
    }

    /**
     * Remove the specified governorate from storage.
     */
    public function destroy(Governorate $governorate)
    {
        // التحقق من وجود مدن مرتبطة
        if ($governorate->cities()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف المحافظة لأنها تحتوي على مدن مرتبطة بها'
            ], 400);
        }

        $governorate->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف المحافظة بنجاح'
        ]);
    }

    /**
     * Get cities by governorate (AJAX)
     */
    public function getCities(Request $request, $governorateId)
    {
        $cities = City::where('governorate_id', $governorateId)
                     ->active()
                     ->select('id', 'name_ar', 'name_en')
                     ->orderBy('name_ar')
                     ->get();

        return response()->json($cities);
    }

    /**
     * Toggle governorate status (AJAX)
     */
    public function toggleStatus(Governorate $governorate)
    {
        $governorate->update([
            'is_active' => !$governorate->is_active
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $governorate->is_active,
            'message' => $governorate->is_active ? 'تم تفعيل المحافظة' : 'تم إلغاء تفعيل المحافظة'
        ]);
    }

    /**
     * Get all active governorates for dropdown (AJAX)
     */
    public function getActiveGovernorates()
    {
        $governorates = Governorate::active()
                                 ->select('id', 'name_ar', 'name_en')
                                 ->orderBy('name_ar')
                                 ->get();

        return response()->json($governorates)