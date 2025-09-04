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
        Schema::table('properties', function (Blueprint $table) {
            // Add foreign key columns only if they don't exist
            if (!Schema::hasColumn('properties', 'governorate_id')) {
                $table->unsignedBigInteger('governorate_id')->nullable()->after('state');
            }
            if (!Schema::hasColumn('properties', 'neighborhood_id')) {
                $table->unsignedBigInteger('neighborhood_id')->nullable()->after('city_id');
            }
            
            // Add foreign key constraints
            $table->foreign('governorate_id')->references('id')->on('governorates')->onDelete('set null');
            if (!Schema::hasColumn('properties', 'city_id')) {
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            }
            $table->foreign('neighborhood_id')->references('id')->on('neighborhoods')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop foreign key constraints first
            if (Schema::hasColumn('properties', 'governorate_id')) {
                $table->dropForeign(['governorate_id']);
                $table->dropColumn('governorate_id');
            }
            if (Schema::hasColumn('properties', 'neighborhood_id')) {
                $table->dropForeign(['neighborhood_id']);
                $table->dropColumn('neighborhood_id');
            }
        });
    }
};
