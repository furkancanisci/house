<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed Building Types
        DB::table('building_types')->insert([
            [
                'name_en' => 'New Construction',
                'name_ar' => 'بناء جديد',
                'value' => 'new',
                'description_en' => 'Newly constructed building',
                'description_ar' => 'مبنى جديد البناء',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Resale',
                'name_ar' => 'إعادة بيع',
                'value' => 'resale',
                'description_en' => 'Previously owned property',
                'description_ar' => 'عقار مملوك سابقاً',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Under Construction',
                'name_ar' => 'تحت الإنشاء',
                'value' => 'under_construction',
                'description_en' => 'Building currently under construction',
                'description_ar' => 'مبنى قيد الإنشاء حالياً',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Project',
                'name_ar' => 'مشروع',
                'value' => 'project',
                'description_en' => 'Development project',
                'description_ar' => 'مشروع تطوير',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Seed Window Types
        DB::table('window_types')->insert([
            [
                'name_en' => 'Aluminum',
                'name_ar' => 'ألمنيوم',
                'value' => 'aluminum',
                'description_en' => 'Aluminum window frames',
                'description_ar' => 'إطارات نوافذ من الألمنيوم',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'PVC',
                'name_ar' => 'بي في سي',
                'value' => 'pvc',
                'description_en' => 'PVC window frames',
                'description_ar' => 'إطارات نوافذ من البي في سي',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Wood',
                'name_ar' => 'خشب',
                'value' => 'wood',
                'description_en' => 'Wooden window frames',
                'description_ar' => 'إطارات نوافذ خشبية',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Steel',
                'name_ar' => 'حديد',
                'value' => 'steel',
                'description_en' => 'Steel window frames',
                'description_ar' => 'إطارات نوافذ من الحديد',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Seed Floor Types
        DB::table('floor_types')->insert([
            [
                'name_en' => 'Laminate',
                'name_ar' => 'لامينيت',
                'value' => 'laminate',
                'description_en' => 'Laminate flooring',
                'description_ar' => 'أرضية لامينيت',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Hardwood',
                'name_ar' => 'خشب صلب',
                'value' => 'hardwood',
                'description_en' => 'Hardwood flooring',
                'description_ar' => 'أرضية خشب صلب',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Tile',
                'name_ar' => 'بلاط',
                'value' => 'tile',
                'description_en' => 'Ceramic or porcelain tiles',
                'description_ar' => 'بلاط سيراميك أو بورسلين',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Marble',
                'name_ar' => 'رخام',
                'value' => 'marble',
                'description_en' => 'Marble flooring',
                'description_ar' => 'أرضية رخام',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Carpet',
                'name_ar' => 'سجاد',
                'value' => 'carpet',
                'description_en' => 'Carpeted flooring',
                'description_ar' => 'أرضية مفروشة بالسجاد',
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Vinyl',
                'name_ar' => 'فينيل',
                'value' => 'vinyl',
                'description_en' => 'Vinyl flooring',
                'description_ar' => 'أرضية فينيل',
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Concrete',
                'name_ar' => 'خرسانة',
                'value' => 'concrete',
                'description_en' => 'Concrete flooring',
                'description_ar' => 'أرضية خرسانية',
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name_en' => 'Parquet',
                'name_ar' => 'باركيه',
                'value' => 'parquet',
                'description_en' => 'Parquet flooring',
                'description_ar' => 'أرضية باركيه',
                'is_active' => true,
                'sort_order' => 8,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('building_types')->truncate();
        DB::table('window_types')->truncate();
        DB::table('floor_types')->truncate();
    }
};