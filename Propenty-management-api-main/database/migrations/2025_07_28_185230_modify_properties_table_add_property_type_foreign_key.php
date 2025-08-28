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
        // Check if properties table exists before trying to modify it
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                // Add property_type_id foreign key
                if (!Schema::hasColumn('properties', 'property_type_id')) {
                    $table->unsignedBigInteger('property_type_id')->nullable()->after('property_type');
                }
                
                // Add city_id foreign key to replace string city field
                if (!Schema::hasColumn('properties', 'city_id')) {
                    $table->unsignedBigInteger('city_id')->nullable()->after('city');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (Schema::hasColumn('properties', 'property_type_id')) {
                    $table->dropColumn('property_type_id');
                }
                if (Schema::hasColumn('properties', 'city_id')) {
                    $table->dropColumn('city_id');
                }
            });
        }
    }
};
