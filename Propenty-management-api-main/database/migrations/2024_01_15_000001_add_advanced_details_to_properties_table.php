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
            // Add foreign key columns for advanced property details
            $table->unsignedBigInteger('building_type_id')->nullable()->after('building_type');
            $table->unsignedBigInteger('window_type_id')->nullable()->after('window_type');
            $table->unsignedBigInteger('floor_type_id')->nullable()->after('floor_type');
            
            // Add foreign key constraints
            $table->foreign('building_type_id')
                  ->references('id')
                  ->on('building_types')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('window_type_id')
                  ->references('id')
                  ->on('window_types')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('floor_type_id')
                  ->references('id')
                  ->on('floor_types')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            // Add indexes for better performance
            $table->index('building_type_id');
            $table->index('window_type_id');
            $table->index('floor_type_id');
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