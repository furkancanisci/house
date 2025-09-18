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
            // Add foreign key columns for advanced property details only if they don't exist
            if (!Schema::hasColumn('properties', 'building_type_id')) {
                $table->unsignedBigInteger('building_type_id')->nullable()->after('building_type');
            }
            if (!Schema::hasColumn('properties', 'window_type_id')) {
                $table->unsignedBigInteger('window_type_id')->nullable()->after('window_type');
            }
            if (!Schema::hasColumn('properties', 'floor_type_id')) {
                $table->unsignedBigInteger('floor_type_id')->nullable()->after('floor_type');
            }
        });

        // Add foreign key constraints in a separate schema modification
        Schema::table('properties', function (Blueprint $table) {
            // Check if foreign key constraints don't already exist before adding them
            $existingConstraints = collect(\DB::select("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = 'properties' 
                AND constraint_type = 'FOREIGN KEY' 
                AND (constraint_name LIKE '%building_type_id%' 
                     OR constraint_name LIKE '%window_type_id%' 
                     OR constraint_name LIKE '%floor_type_id%')
            "))->pluck('constraint_name')->toArray();
            
            if (!in_array('properties_building_type_id_foreign', $existingConstraints)) {
                $table->foreign('building_type_id')
                      ->references('id')
                      ->on('building_types')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
                      
            if (!in_array('properties_window_type_id_foreign', $existingConstraints)) {
                $table->foreign('window_type_id')
                      ->references('id')
                      ->on('window_types')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
                      
            if (!in_array('properties_floor_type_id_foreign', $existingConstraints)) {
                $table->foreign('floor_type_id')
                      ->references('id')
                      ->on('floor_types')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
            
            // Check for existing indexes
            $existingIndexes = collect(\DB::select("
                SELECT indexname 
                FROM pg_indexes 
                WHERE tablename = 'properties' 
                AND (indexname LIKE '%building_type_id%' 
                     OR indexname LIKE '%window_type_id%' 
                     OR indexname LIKE '%floor_type_id%')
            "))->pluck('indexname')->toArray();
                
            if (!in_array('properties_building_type_id_index', $existingIndexes)) {
                $table->index('building_type_id');
            }
            if (!in_array('properties_window_type_id_index', $existingIndexes)) {
                $table->index('window_type_id');
            }
            if (!in_array('properties_floor_type_id_index', $existingIndexes)) {
                $table->index('floor_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['building_type_id']);
            $table->dropForeign(['window_type_id']);
            $table->dropForeign(['floor_type_id']);
            
            // Drop indexes
            $table->dropIndex(['building_type_id']);
            $table->dropIndex(['window_type_id']);
            $table->dropIndex(['floor_type_id']);
            
            // Drop columns
            $table->dropColumn([
                'building_type_id',
                'window_type_id',
                'floor_type_id'
            ]);
        });
    }
};