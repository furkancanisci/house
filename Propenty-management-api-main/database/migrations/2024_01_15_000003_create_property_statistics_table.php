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
        Schema::create('property_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id');
            $table->integer('views_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            $table->integer('favorites_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            
            // Unique constraint to ensure one statistics record per property
            $table->unique('property_id');
            
            // Indexes for performance
            $table->index('views_count');
            $table->index('inquiries_count');
            $table->index('favorites_count');
            $table->index('last_viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_statistics');
    }
};