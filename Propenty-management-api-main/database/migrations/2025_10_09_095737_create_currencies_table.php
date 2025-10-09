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
            $table->string('code', 3)->unique()->comment('Currency code (USD, EUR, etc.)');
            $table->string('name_ar')->comment('Currency name in Arabic');
            $table->string('name_en')->comment('Currency name in English');
            $table->string('name_ku')->nullable()->comment('Currency name in Kurdish');
            $table->string('symbol', 10)->comment('Currency symbol');
            $table->boolean('is_active')->default(true)->comment('Is currency active');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Insert default currencies
        DB::table('currencies')->insert([
            [
                'code' => 'USD',
                'name_ar' => 'دولار أمريكي',
                'name_en' => 'US Dollar',
                'name_ku' => 'دۆلاری ئەمریکی',
                'symbol' => '$',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'TRY',
                'name_ar' => 'ليرة تركية',
                'name_en' => 'Turkish Lira',
                'name_ku' => 'لیرەی تورکی',
                'symbol' => '₺',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'EUR',
                'name_ar' => 'يورو',
                'name_en' => 'Euro',
                'name_ku' => 'یۆرۆ',
                'symbol' => '€',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'SYP',
                'name_ar' => 'ليرة سورية',
                'name_en' => 'Syrian Pound',
                'name_ku' => 'لیرەی سووری',
                'symbol' => 'ل.س',
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
