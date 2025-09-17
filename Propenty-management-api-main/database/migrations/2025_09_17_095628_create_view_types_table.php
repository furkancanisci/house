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
        Schema::create('view_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_en')->comment('View type name in English');
            $table->string('name_ar')->comment('View type name in Arabic');
            $table->string('value')->unique()->comment('Unique value for this view type');
            $table->text('description_en')->nullable()->comment('Description in English');
            $table->text('description_ar')->nullable()->comment('Description in Arabic');
            $table->boolean('is_active')->default(true)->comment('Whether this view type is active');
            $table->integer('sort_order')->default(0)->comment('Sort order for display');
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_types');
    }
};
