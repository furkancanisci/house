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
        Schema::create('floor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->comment('English name');
            $table->string('name_ar')->comment('Arabic name');
            $table->string('value')->unique()->comment('Enum value for properties table');
            $table->text('description_en')->nullable()->comment('English description');
            $table->text('description_ar')->nullable()->comment('Arabic description');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->integer('sort_order')->default(0)->comment('Display order');
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floor_types');
    }
};