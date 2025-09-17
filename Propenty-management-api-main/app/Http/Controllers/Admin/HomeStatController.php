<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use Illuminate\Http\Request;
use App\Http\Middleware\InputSanitizationMiddleware;

class HomeStatController extends Controller
{
    public function __construct()
    {
        // InputSanitizationMiddleware is skipped for home-stats paths in the middleware itself
        $this->middleware(InputSanitizationMiddleware::class);
    }

    /**
     * Display a listing of home statistics.
     */
    public function index()
    {
        $stats = HomeStat::orderBy('order')->get();
        return view('admin.home-stats.index', compact('stats'));
    }

    /**
     * Show the form for creating a new home statistic.
     */
    public function create()
    {
        return view('admin.home-stats.create');
    }

    /**
     * Store a newly created home statistic in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:home_stats,key|regex:/^[a-zA-Z0-9_]+$/',
            'icon' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'number' => 'required|string|max:50',
            'label_ar' => 'required|string|max:500',
            'label_en' => 'required|string|max:500',
            'label_ku' => 'required|string|max:500',
            'color' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-_]+$/',
            'order' => 'nullable|integer|min:0|max:999',
        ]);

        try {
            HomeStat::create([
                'key' => $request->key,
                'icon' => $request->icon,
                'number' => $request->number,
                'label_ar' => $request->label_ar,
                'label_en' => $request->label_en,
                'label_ku' => $request->label_ku,
                'color' => $request->color ?? 'text-primary-600',
                'order' => $request->order ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.home-stats.index')
                ->with('success', 'تم إضافة الإحصائية بنجاح!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء إضافة الإحصائية: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified home statistic.
     */
    public function edit(HomeStat $homeStat)
    {
        return view('admin.home-stats.edit', compact('homeStat'));
    }

    /**
     * Update the specified home statistic in storage.
     */
    public function update(Request $request, HomeStat $homeStat)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:home_stats,key,' . $homeStat->id . '|regex:/^[a-zA-Z0-9_]+$/',
            'icon' => 'required|string|max:255|regex:/^[a-zA-Z0-9_]+$/',
            'number' => 'required|string|max:50',
            'label_ar' => 'required|string|max:500',
            'label_en' => 'required|string|max:500',
            'label_ku' => 'required|string|max:500',
            'color' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\-_]+$/',
            'order' => 'nullable|integer|min:0|max:999',
        ]);

        try {
            $homeStat->update([
                'key' => $request->key,
                'icon' => $request->icon,
                'number' => $request->number,
                'label_ar' => $request->label_ar,
                'label_en' => $request->label_en,
                'label_ku' => $request->label_ku,
                'color' => $request->color ?? 'text-primary-600',
                'order' => $request->order ?? 0,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.home-stats.index')
                ->with('success', 'تم تحديث الإحصائية بنجاح!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء تحديث الإحصائية: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified home statistic from storage.
     */
    public function destroy(HomeStat $homeStat)
    {
        try {
            $homeStat->delete();
            return redirect()->route('admin.home-stats.index')
                ->with('success', 'تم حذف الإحصائية بنجاح!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف الإحصائية: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle the active status of the home statistic.
     */
    public function toggleStatus(HomeStat $homeStat)
    {
        try {
            $homeStat->update(['is_active' => !$homeStat->is_active]);

            $status = $homeStat->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
            return redirect()->route('admin.home-stats.index')
                ->with('success', $status . ' الإحصائية بنجاح!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'حدث خطأ أثناء تغيير حالة الإحصائية: ' . $e->getMessage()]);
        }
    }
}