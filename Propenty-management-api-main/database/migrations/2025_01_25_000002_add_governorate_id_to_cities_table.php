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
        Schema::table('cities', function (Blueprint $table) {
            // إضافة عمود المحافظة
            $table->unsignedBigInteger('governorate_id')->nullable()->after('slug');
            
            // إضافة المفتاح الخارجي
            $table->foreign('governorate_id')
                  ->references('id')
                  ->on('governorates')
                  ->onDelete('set null');
            
            // إنشاء فهرس للمحافظة
            $table->index('governorate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            // حذف المفتاح الخارجي والفهرس
            $table->dropForeign(['governorate_id']);
            $table->dropIndex(['governorate_id']);
            $table->dropColumn('governorate_id');
        });
    }
};