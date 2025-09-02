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
        Schema::create('utilities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar'); // Arabic name
            $table->string('name_en'); // English name
            $table->string('name_ku'); // Kurdish name
            $table->text('description_ar')->nullable(); // Arabic description
            $table->text('description_en')->nullable(); // English description
            $table->text('description_ku')->nullable(); // Kurdish description
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // Font Awesome icon class or SVG
            $table->string('category')->nullable(); // electricity, water, gas, internet, etc.
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['is_active', 'sort_order']);
            $table->index('category');
            $table->fullText(['name_ar', 'name_en', 'name_ku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilities');
    }
};