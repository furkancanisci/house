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
            // Building Information
            $table->integer('building_age')->nullable()->comment('Age of the building in years');
            $table->enum('building_type', ['new', 'resale', 'under_construction', 'project'])->nullable()->comment('Building construction status');
            
            // Interior Details
            $table->enum('floor_type', ['laminate', 'hardwood', 'tile', 'marble', 'carpet', 'vinyl', 'concrete', 'parquet'])->nullable()->comment('Type of flooring');
            $table->enum('window_type', ['aluminum', 'pvc', 'wood', 'steel'])->nullable()->comment('Type of windows');
            
            // Financial Information
            $table->decimal('maintenance_fee', 10, 2)->nullable()->comment('Monthly maintenance fee');
            $table->decimal('deposit_amount', 12, 2)->nullable()->comment('Security deposit amount');
            $table->decimal('annual_tax', 10, 2)->nullable()->comment('Annual property tax');
            
            // Add indexes for better query performance
            $table->index('building_age');
            $table->index('building_type');
            $table->index('floor_type');
            $table->index('window_type');
            $table->index('maintenance_fee');
            $table->index('deposit_amount');
            $table->index('annual_tax');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['building_age']);
            $table->dropIndex(['building_type']);
            $table->dropIndex(['floor_type']);
            $table->dropIndex(['window_type']);
            $table->dropIndex(['maintenance_fee']);
            $table->dropIndex(['deposit_amount']);
            $table->dropIndex(['annual_tax']);
            
            // Drop columns
            $table->dropColumn([
                'building_age',
                'building_type',
                'floor_type',
                'window_type',
                'maintenance_fee',
                'deposit_amount',
                'annual_tax'
            ]);
        });
    }
};
