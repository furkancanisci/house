# خطة التنفيذ التفصيلية لتحسين نظام إدارة العقارات

## 1. نظرة عامة على خطة التنفيذ

خطة التنفيذ مقسمة إلى 4 مراحل رئيسية على مدى 8 أسابيع، مع التركيز على إضافة الحقول المفقودة وتحسين تجربة المستخدم لتنافس أفضل المواقع العقارية.

## 2. المرحلة الأولى (الأسبوع 1-2): الحقول الأساسية

### 2.1 تحديثات قاعدة البيانات

#### ملف Migration الجديد
```php
<?php
// database/migrations/2025_01_30_000001_add_basic_property_fields.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // معلومات المبنى الأساسية
            $table->integer('floor_number')->nullable()->after('year_built');
            $table->integer('total_floors')->nullable()->after('floor_number');
            $table->integer('balcony_count')->default(0)->after('total_floors');
            
            // الاتجاه والإطلالة
            $table->enum('orientation', [
                'north', 'south', 'east', 'west', 
                'northeast', 'northwest', 'southeast', 'southwest'
            ])->nullable()->after('balcony_count');
            
            $table->enum('view_type', [
                'sea', 'mountain', 'street', 'garden', 'city', 'courtyard'
            ])->nullable()->after('orientation');
            
            // فهارس للأداء
            $table->index('floor_number');
            $table->index('orientation');
            $table->index('view_type');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['floor_number']);
            $table->dropIndex(['orientation']);
            $table->dropIndex(['view_type']);
            
            $table->dropColumn([
                'floor_number', 'total_floors', 'balcony_count',
                'orientation', 'view_type'
            ]);
        });
    }
};
```

#### إضافة الميزات الأساسية
```php
<?php
// database/seeders/BasicFeaturesSeeder.php

use Illuminate\Database\Seeder;
use App\Models\Feature;

class BasicFeaturesSeeder extends Seeder
{
    public function run()
    {
        $basicFeatures = [
            [
                'name_ar' => 'مصعد',
                'name_en' => 'Elevator',
                'name_ku' => 'Elevator',
                'description_ar' => 'يحتوي المبنى على مصعد',
                'description_en' => 'Building has elevator',
                'description_ku' => 'Building has elevator',
                'category' => 'building',
                'icon' => 'fas fa-elevator',
                'sort_order' => 1,
                'is_active' => true
            ],
            [
                'name_ar' => 'شرفة',
                'name_en' => 'Balcony',
                'name_ku' => 'Balcony',
                'description_ar' => 'العقار يحتوي على شرفة',
                'description_en' => 'Property has balcony',
                'description_ku' => 'Property has balcony',
                'category' => 'interior',
                'icon' => 'fas fa-home',
                'sort_order' => 2,
                'is_active' => true
            ],
            [
                'name_ar' => 'حارس أمن',
                'name_en' => 'Security Guard',
                'name_ku' => 'Security Guard',
                'description_ar' => 'يوجد حارس أمن في المبنى',
                'description_en' => 'Building has security guard',
                'description_ku' => 'Building has security guard',
                'category' => 'security',
                'icon' => 'fas fa-shield-alt',
                'sort_order' => 3,
                'is_active' => true
            ]
        ];

        foreach ($basicFeatures as $feature) {
            Feature::updateOrCreate(
                ['name_en' => $feature['name_en']],
                $feature
            );
        }
    }
}
```

### 2.2 تحديث نموذج Property

```php
<?php
// app/Models/Property.php - إضافات جديدة

class Property extends Model
{
    protected $fillable = [
        // ... الحقول الموجودة
        'floor_number',
        'total_floors', 
        'balcony_count',
        'orientation',
        'view_type',
    ];

    protected $casts = [
        // ... الموجود
        'floor_number' => 'integer',
        'total_floors' => 'integer',
        'balcony_count' => 'integer',
    ];

    // Constants للقيم الثابتة
    const ORIENTATIONS = [
        'north' => 'شمال',
        'south' => 'جنوب', 
        'east' => 'شرق',
        'west' => 'غرب',
        'northeast' => 'شمال شرق',
        'northwest' => 'شمال غرب',
        'southeast' => 'جنوب شرق',
        'southwest' => 'جنوب غرب',
    ];

    const VIEW_TYPES = [
        'sea' => 'بحر',
        'mountain' => 'جبل',
        'street' => 'شارع', 
        'garden' => 'حديقة',
        'city' => 'مدينة',
        'courtyard' => 'فناء',
    ];

    // Accessor للحصول على تسمية الاتجاه
    public function getOrientationLabelAttribute()
    {
        return $this->orientation ? self::ORIENTATIONS[$this->orientation] ?? $this->orientation : null;
    }

    // Accessor للحصول على تسمية نوع الإطلالة
    public function getViewTypeLabelAttribute()
    {
        return $this->view_type ? self::VIEW_TYPES[$this->view_type] ?? $this->view_type : null;
    }

    // Scope للبحث حسب الطابق
    public function scopeByFloor($query, $floorNumber)
    {
        return $query->where('floor_number', $floorNumber);
    }

    // Scope للبحث حسب الاتجاه
    public function scopeByOrientation($query, $orientation)
    {
        return $query->where('orientation', $orientation);
    }

    // Scope للبحث حسب نوع الإطلالة
    public function scopeByViewType($query, $viewType)
    {
        return $query->where('view_type', $viewType);
    }
}
```

### 2.3 تحديث API Controller

```php
<?php
// app/Http/Controllers/Api/PropertyController.php - تحديثات

use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;

class PropertyController extends Controller
{
    public function store(StorePropertyRequest $request)
    {
        $validated = $request->validated();
        
        $property = Property::create($validated);
        
        // ربط الميزات
        if ($request->has('features')) {
            $property->features()->sync($request->features);
        }
        
        // ربط المرافق
        if ($request->has('utilities')) {
            $property->utilities()->sync($request->utilities);
        }
        
        return response()->json([
            'success' => true,
            'data' => $property->load(['features', 'utilities', 'images']),
            'message' => 'تم إنشاء العقار بنجاح'
        ], 201);
    }

    public function search(Request $request)
    {
        $query = Property::with(['propertyType', 'features', 'utilities', 'images'])
            ->where('is_active', true);

        // فلترة حسب الحقول الجديدة
        if ($request->filled('floor_number')) {
            $query->byFloor($request->floor_number);
        }

        if ($request->filled('orientation')) {
            $query->byOrientation($request->orientation);
        }

        if ($request->filled('view_type')) {
            $query->byViewType($request->view_type);
        }

        if ($request->filled('has_elevator')) {
            $elevatorFeature = Feature::where('name_en', 'Elevator')->first();
            if ($elevatorFeature) {
                $query->whereHas('features', function($q) use ($elevatorFeature) {
                    $q->where('feature_id', $elevatorFeature->id);
                });
            }
        }

        if ($request->filled('balcony_count')) {
            $query->where('balcony_count', '>=', $request->balcony_count);
        }

        $properties = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }
}
```

### 2.4 تحديث Form Requests

```php
<?php
// app/Http/Requests/StorePropertyRequest.php

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Property;

class StorePropertyRequest extends FormRequest
{
    public function rules()
    {
        return [
            // ... القواعد الموجودة
            'floor_number' => 'nullable|integer|min:0|max:100',
            'total_floors' => 'nullable|integer|min:1|max:100',
            'balcony_count' => 'nullable|integer|min:0|max:10',
            'orientation' => 'nullable|in:' . implode(',', array_keys(Property::ORIENTATIONS)),
            'view_type' => 'nullable|in:' . implode(',', array_keys(Property::VIEW_TYPES)),
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
            'utilities' => 'nullable|array', 
            'utilities.*' => 'exists:utilities,id',
        ];
    }

    public function messages()
    {
        return [
            'floor_number.integer' => 'رقم الطابق يجب أن يكون رقماً صحيحاً',
            'floor_number.min' => 'رقم الطابق لا يمكن أن يكون أقل من 0',
            'floor_number.max' => 'رقم الطابق لا يمكن أن يكون أكثر من 100',
            'total_floors.min' => 'إجمالي الطوابق يجب أن يكون على الأقل 1',
            'orientation.in' => 'الاتجاه المحدد غير صحيح',
            'view_type.in' => 'نوع الإطلالة المحدد غير صحيح',
        ];
    }
}
```

### 2.5 تحديثات الفرونت اند - React

#### تحديث أنواع البيانات
```typescript
// src/types/property.ts

export interface PropertyFormData {
  // ... الحقول الموجودة
  floorNumber?: number;
  totalFloors?: number;
  balconyCount?: number;
  orientation?: OrientationType;
  viewType?: ViewType;
  features?: number[];
  utilities?: number[];
}

export type OrientationType = 
  | 'north' | 'south' | 'east' | 'west'
  | 'northeast' | 'northwest' | 'southeast' | 'southwest';

export type ViewType = 
  | 'sea' | 'mountain' | 'street' | 'garden' | 'city' | 'courtyard';

export const ORIENTATIONS: Record<OrientationType, string> = {
  north: 'شمال',
  south: 'جنوب',
  east: 'شرق', 
  west: 'غرب',
  northeast: 'شمال شرق',
  northwest: 'شمال غرب',
  southeast: 'جنوب شرق',
  southwest: 'جنوب غرب',
};

export const VIEW_TYPES: Record<ViewType, string> = {
  sea: 'بحر',
  mountain: 'جبل',
  street: 'شارع',
  garden: 'حديقة', 
  city: 'مدينة',
  courtyard: 'فناء',
};
```

#### تحديث Zod Schema
```typescript
// src/schemas/propertySchema.ts

import { z } from 'zod';

export const propertySchema = z.object({
  // ... الحقول الموجودة
  floorNumber: z.number().min(0).max(100).optional(),
  totalFloors: z.number().min(1).max(100).optional(),
  balconyCount: z.number().min(0).max(10).optional(),
  orientation: z.enum([
    'north', 'south', 'east', 'west',
    'northeast', 'northwest', 'southeast', 'southwest'
  ]).optional(),
  viewType: z.enum([
    'sea', 'mountain', 'street', 'garden', 'city', 'courtyard'
  ]).optional(),
  features: z.array(z.number()).optional(),
  utilities: z.array(z.number()).optional(),
});
```

#### تحديث مكون AddProperty
```tsx
// src/components/AddProperty.tsx - إضافات جديدة

import { ORIENTATIONS, VIEW_TYPES } from '../types/property';

const AddProperty: React.FC = () => {
  // ... الكود الموجود

  // إضافة خطوة جديدة للتفاصيل الأساسية
  const renderStep2Enhanced = () => (
    <div className="space-y-6">
      <h3 className="text-xl font-bold text-gray-800 mb-4">
        تفاصيل المبنى
      </h3>
      
      {/* معلومات الطابق */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label htmlFor="floorNumber">رقم الطابق</Label>
          <Input
            id="floorNumber"
            type="number"
            min="0"
            max="100"
            placeholder="مثال: 3"
            {...register('floorNumber', { valueAsNumber: true })}
          />
          {errors.floorNumber && (
            <p className="text-red-500 text-sm mt-1">
              {errors.floorNumber.message}
            </p>
          )}
        </div>
        
        <div>
          <Label htmlFor="totalFloors">إجمالي الطوابق</Label>
          <Input
            id="totalFloors"
            type="number"
            min="1"
            max="100"
            placeholder="مثال: 10"
            {...register('totalFloors', { valueAsNumber: true })}
          />
          {errors.totalFloors && (
            <p className="text-red-500 text-sm mt-1">
              {errors.totalFloors.message}
            </p>
          )}
        </div>
        
        <div>
          <Label htmlFor="balconyCount">عدد الشرفات</Label>
          <Input
            id="balconyCount"
            type="number"
            min="0"
            max="10"
            placeholder="مثال: 2"
            {...register('balconyCount', { valueAsNumber: true })}
          />
          {errors.balconyCount && (
            <p className="text-red-500 text-sm mt-1">
              {errors.balconyCount.message}
            </p>
          )}
        </div>
      </div>
      
      {/* الاتجاه والإطلالة */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label htmlFor="orientation">اتجاه العقار</Label>
          <Controller
            name="orientation"
            control={control}
            render={({ field }) => (
              <Select onValueChange={field.onChange} value={field.value}>
                <SelectTrigger>
                  <SelectValue placeholder="اختر الاتجاه" />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(ORIENTATIONS).map(([key, label]) => (
                    <SelectItem key={key} value={key}>
                      {label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            )}
          />
          {errors.orientation && (
            <p className="text-red-500 text-sm mt-1">
              {errors.orientation.message}
            </p>
          )}
        </div>
        
        <div>
          <Label htmlFor="viewType">نوع الإطلالة</Label>
          <Controller
            name="viewType"
            control={control}
            render={({ field }) => (
              <Select onValueChange={field.onChange} value={field.value}>
                <SelectTrigger>
                  <SelectValue placeholder="اختر نوع الإطلالة" />
                </SelectTrigger>
                <SelectContent>
                  {Object.entries(VIEW_TYPES).map(([key, label]) => (
                    <SelectItem key={key} value={key}>
                      {label}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            )}
          />
          {errors.viewType && (
            <p className="text-red-500 text-sm mt-1">
              {errors.viewType.message}
            </p>
          )}
        </div>
      </div>
    </div>
  );

  // ... باقي الكود
};
```

## 3. المرحلة الثانية (الأسبوع 3-4): التفاصيل المتقدمة

### 3.1 إضافة الحقول المتقدمة

```php
<?php
// database/migrations/2025_02_06_000001_add_advanced_property_fields.php

return new class extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            // تفاصيل المبنى المتقدمة
            $table->integer('building_age')->nullable()->after('view_type');
            $table->enum('building_type', ['residential', 'commercial', 'mixed'])
                  ->nullable()->after('building_age');
            
            // تفاصيل داخلية
            $table->enum('floor_type', ['ceramic', 'marble', 'wood', 'carpet', 'granite'])
                  ->nullable()->after('building_type');
            $table->enum('window_type', ['aluminum', 'wood', 'pvc'])
                  ->nullable()->after('floor_type');
            
            // معلومات مالية
            $table->decimal('maintenance_fee', 10, 2)->nullable()->after('window_type');
            $table->decimal('deposit_amount', 10, 2)->nullable()->after('maintenance_fee');
            $table->decimal('annual_tax', 10, 2)->nullable()->after('deposit_amount');
            
            // فهارس
            $table->index('building_age');
            $table->index('building_type');
            $table->index('floor_type');
        });
    }

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['building_age']);
            $table->dropIndex(['building_type']);
            $table->dropIndex(['floor_type']);
            
            $table->dropColumn([
                'building_age', 'building_type', 'floor_type', 
                'window_type', 'maintenance_fee', 'deposit_amount', 'annual_tax'
            ]);
        });
    }
};
```

### 3.2 تحسين البحث المتقدم

```php
<?php
// app/Services/PropertySearchService.php

class PropertySearchService
{
    public function search(array $filters)
    {
        $query = Property::with(['propertyType', 'features', 'utilities', 'images'])
            ->where('is_active', true);

        // البحث النصي
        if (!empty($filters['q'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'ILIKE', '%' . $filters['q'] . '%')
                  ->orWhere('description', 'ILIKE', '%' . $filters['q'] . '%');
            });
        }

        // فلاتر الأسعار
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // فلاتر المبنى
        if (!empty($filters['floor_number'])) {
            $query->where('floor_number', $filters['floor_number']);
        }
        if (!empty($filters['building_age'])) {
            $query->where('building_age', '<=', $filters['building_age']);
        }
        if (!empty($filters['orientation'])) {
            $query->where('orientation', $filters['orientation']);
        }
        if (!empty($filters['view_type'])) {
            $query->where('view_type', $filters['view_type']);
        }

        // فلاتر الميزات
        if (!empty($filters['features'])) {
            $query->whereHas('features', function($q) use ($filters) {
                $q->whereIn('feature_id', $filters['features']);
            });
        }

        // فلاتر المرافق
        if (!empty($filters['utilities'])) {
            $query->whereHas('utilities', function($q) use ($filters) {
                $q->whereIn('utility_id', $filters['utilities']);
            });
        }

        // ترتيب النتائج
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 12);
    }
}
```

## 4. المرحلة الثالثة (الأسبوع 5-6): الميزات المتقدمة

### 4.1 نظام الإحصائيات

```php
<?php
// app/Models/PropertyStatistic.php

class PropertyStatistic extends Model
{
    protected $fillable = [
        'property_id', 'views_count', 'inquiries_count', 
        'favorites_count', 'last_viewed_at'
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public static function incrementViews($propertyId)
    {
        return static::updateOrCreate(
            ['property_id' => $propertyId],
            ['last_viewed_at' => now()]
        )->increment('views_count');
    }

    public static function incrementInquiries($propertyId)
    {
        return static::updateOrCreate(
            ['property_id' => $propertyId],
            []
        )->increment('inquiries_count');
    }
}
```

### 4.2 نظام البحثات المحفوظة

```php
<?php
// app/Models/SavedSearch.php

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id', 'name', 'search_criteria', 'notification_enabled'
    ];

    protected $casts = [
        'search_criteria' => 'array',
        'notification_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMatchingPropertiesCount()
    {
        $searchService = new PropertySearchService();
        return $searchService->search($this->search_criteria)->total();
    }
}
```

## 5. المرحلة الرابعة (الأسبوع 7-8): التحسينات النهائية

### 5.1 تحسين الأداء

```php
<?php
// app/Http/Controllers/Api/PropertyController.php - تحسينات الأداء

use Illuminate\Support\Facades\Cache;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = 'properties_' . md5(serialize($request->all()));
        
        $properties = Cache::remember($cacheKey, 300, function() use ($request) {
            return (new PropertySearchService())->search($request->all());
        });

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    public function show($id)
    {
        $property = Cache::remember("property_{$id}", 600, function() use ($id) {
            return Property::with([
                'propertyType', 'features', 'utilities', 'images', 
                'city', 'governorate', 'neighborhood', 'user'
            ])->findOrFail($id);
        });

        // تسجيل المشاهدة
        PropertyStatistic::incrementViews($id);

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }
}
```

### 5.2 تحسين واجهة البحث المتقدم

```tsx
// src/components/AdvancedSearch.tsx

import React, { useState, useEffect } from 'react';
import { useForm, Controller } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

const searchSchema = z.object({
  q: z.string().optional(),
  propertyType: z.number().optional(),
  minPrice: z.number().min(0).optional(),
  maxPrice: z.number().min(0).optional(),
  floorNumber: z.number().min(0).max(100).optional(),
  orientation: z.enum(['north', 'south', 'east', 'west', 'northeast', 'northwest', 'southeast', 'southwest']).optional(),
  viewType: z.enum(['sea', 'mountain', 'street', 'garden', 'city', 'courtyard']).optional(),
  features: z.array(z.number()).optional(),
  utilities: z.array(z.number()).optional(),
});

type SearchFormData = z.infer<typeof searchSchema>;

const AdvancedSearch: React.FC = () => {
  const [features, setFeatures] = useState([]);
  const [utilities, setUtilities] = useState([]);
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  const { register, control, handleSubmit, watch, formState: { errors } } = useForm<SearchFormData>({
    resolver: zodResolver(searchSchema)
  });

  const onSubmit = async (data: SearchFormData) => {
    setLoading(true);
    try {
      const response = await fetch('/api/properties/search', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
      });
      const result = await response.json();
      setResults(result.data);
    } catch (error) {
      console.error('خطأ في البحث:', error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-lg p-6">
      <h2 className="text-2xl font-bold text-gray-800 mb-6">البحث المتقدم</h2>
      
      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        {/* البحث النصي */}
        <div>
          <Label htmlFor="q">البحث</Label>
          <Input
            id="q"
            placeholder="ابحث عن العقارات..."
            {...register('q')}
          />
        </div>

        {/* فلاتر الأسعار */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label htmlFor="minPrice">الحد الأدنى للسعر</Label>
            <Input
              id="minPrice"
              type="number"
              min="0"
              placeholder="0"
              {...register('minPrice', { valueAsNumber: true })}
            />
          </div>
          <div>
            <Label htmlFor="maxPrice">الحد الأقصى للسعر</Label>
            <Input
              id="maxPrice"
              type="number"
              min="0"
              placeholder="1000000"
              {...register('maxPrice', { valueAsNumber: true })}
            />
          </div>
        </div>

        {/* فلاتر المبنى */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <Label htmlFor="floorNumber">رقم الطابق</Label>
            <Input
              id="floorNumber"
              type="number"
              min="0"
              max="100"
              {...register('floorNumber', { valueAsNumber: true })}
            />
          </div>
          
          <div>
            <Label htmlFor="orientation">الاتجاه</Label>
            <Controller
              name="orientation"
              control={control}
              render={({ field }) => (
                <Select onValueChange={field.onChange} value={field.value}>
                  <SelectTrigger>
                    <SelectValue placeholder="اختر الاتجاه" />
                  </SelectTrigger>
                  <SelectContent>
                    {Object.entries(ORIENTATIONS).map(([key, label]) => (
                      <SelectItem key={key} value={key}>{label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              )}
            />
          </div>
          
          <div>
            <Label htmlFor="viewType">نوع الإطلالة</Label>
            <Controller
              name="viewType"
              control={control}
              render={({ field }) => (
                <Select onValueChange={field.onChange} value={field.value}>
                  <SelectTrigger>
                    <SelectValue placeholder="اختر نوع الإطلالة" />
                  </SelectTrigger>
                  <SelectContent>
                    {Object.entries(VIEW_TYPES).map(([key, label]) => (
                      <SelectItem key={key} value={key}>{label}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              )}
            />
          </div>
        </div>

        {/* أزرار الإجراءات */}
        <div className="flex gap-4">
          <Button type="submit" disabled={loading} className="flex-1">
            {loading ? 'جاري البحث...' : 'بحث'}
          </Button>
          <Button type="button" variant="outline" onClick={() => window.location.reload()}>
            إعادة تعيين
          </Button>
        </div>
      </form>

      {/* عرض النتائج */}
      {results.length > 0 && (
        <div className="mt-8">
          <h3 className="text-xl font-bold mb-4">نتائج البحث ({results.length})</h3>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {results.map((property) => (
              <PropertyCard key={property.id} property={property} />
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default AdvancedSearch;
```

## 6. قائمة المهام للتنفيذ

### المرحلة الأولى ✅
- [ ] إنشاء migration للحقول الأساسية
- [ ] تحديث نموذج Property
- [ ] إضافة الميزات الأساسية (مصعد، شرفة، أمان)
- [ ] تحديث API endpoints
- [ ] تحديث واجهة AddProperty
- [ ] اختبار الوظائف الأساسية

### المرحلة الثانية ⏳
- [ ] إضافة الحقول المتقدمة
- [ ] تطوير خدمة البحث المتقدم
- [ ] تحسين واجهة البحث
- [ ] إضافة المزيد من الميزات والمرافق
- [ ] تحسين عرض تفاصيل العقار

### المرحلة الثالثة ⏳
- [ ] تطوير نظام الإحصائيات
- [ ] إضافة البحثات المحفوظة
- [ ] تطوير لوحة تحكم محسنة
- [ ] إضافة نظام التنبيهات
- [ ] تحسين الأمان والتحقق

### المرحلة الرابعة ⏳
- [ ] تحسين الأداء والتخزين المؤقت
- [ ] اختبار شامل للنظام
- [ ] تحسين SEO والبحث
- [ ] التوثيق النهائي
- [ ] النشر والمتابعة

## 7. ملاحظات مهمة للتنفيذ

1. **النسخ الاحتياطية**: تأكد من عمل نسخة احتياطية من قاعدة البيانات قبل تشغيل أي migration
2. **الاختبار**: اختبر كل مرحلة على بيئة التطوير قبل النشر
3. **الأداء**: راقب أداء الاستعلامات بعد إضافة الفهارس الجديدة
4. **التوافق**: تأكد من توافق التحديثات مع الكود الموجود
5. **المستخدمين**: أعلم المستخدمين بالميزات الجديدة عبر النظام

هذه الخطة توفر دليلاً شاملاً لتحسين نظام إدارة العقارات ليصبح منافساً قوياً في السوق العقاري.