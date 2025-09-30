<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // TRY, USD, EUR, SYP
            $table->string('name_en', 100);
            $table->string('name_ar', 100);
            $table->string('name_ku', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Insert default currencies
        DB::table('currencies')->insert([
            [
                'code' => 'TRY',
                'name_en' => 'Turkish Lira',
                'name_ar' => 'الليرة التركية',
                'name_ku' => 'Lîra Tirkî',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'USD',
                'name_en' => 'US Dollar',
                'name_ar' => 'الدولار الأمريكي',
                'name_ku' => 'Dolarê Amerîkî',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EUR',
                'name_en' => 'Euro',
                'name_ar' => 'اليورو',
                'name_ku' => 'Ewro',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SYP',
                'name_en' => 'Syrian Pound',
                'name_ar' => 'الليرة السورية',
                'name_ku' => 'Lîra Sûrî',
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
