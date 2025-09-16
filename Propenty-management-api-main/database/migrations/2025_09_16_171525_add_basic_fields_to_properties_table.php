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
            $table->integer('floor_number')->nullable()->comment('Floor number of the property');
            $table->integer('total_floors')->nullable()->comment('Total floors in the building');
            $table->integer('balcony_count')->default(0)->comment('Number of balconies');
            $table->enum('orientation', ['north', 'south', 'east', 'west', 'northeast', 'northwest', 'southeast', 'southwest'])->nullable()->comment('Property orientation');
            $table->enum('view_type', ['sea', 'city', 'mountain', 'garden', 'street', 'courtyard', 'forest', 'lake'])->nullable()->comment('Type of view from property');
            
            // Add indexes for better query performance
            $table->index('floor_number');
            $table->index('total_floors');
            $table->index('balcony_count');
            $table->index('orientation');
            $table->index('view_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['floor_number']);
            $table->dropIndex(['total_floors']);
            $table->dropIndex(['balcony_count']);
            $table->dropIndex(['orientation']);
            $table->dropIndex(['view_type']);
            
            $table->dropColumn([
                'floor_number',
                'total_floors',
                'balcony_count',
                'orientation',
                'view_type'
            ]);
        });
    }
};
