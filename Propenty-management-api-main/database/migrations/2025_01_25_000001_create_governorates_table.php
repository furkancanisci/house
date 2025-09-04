<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('governorates', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar'); // الاسم بالعربية
            $table->string('name_en'); // الاسم بالإنجليزية
            $table->string('name_ku')->nullable(); // الاسم بالكردية
            $table->string('slug')->unique(); // URL slug
            $table->decimal('latitude', 10, 7)->nullable(); // خط العرض
            $table->decimal('longitude', 10, 7)->nullable(); // خط الطول
            $table->boolean('is_active')->default(true); // حالة التفعيل
            $table->timestamps();

            // إنشاء الفهارس
            $table->index('name_ar');
            $table->index('name_en');
            $table->index('name_ku');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('governorates');
    }
};