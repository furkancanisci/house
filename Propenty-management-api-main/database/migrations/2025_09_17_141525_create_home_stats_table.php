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
        Schema::create('home_stats', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // properties_listed, happy_customers, etc.
            $table->string('icon')->default('HomeIcon'); // Lucide icon name
            $table->string('number'); // The displayed number/value
            $table->string('label_ar'); // Arabic label
            $table->string('label_en'); // English label
            $table->string('label_ku'); // Kurdish label
            $table->string('color')->default('text-primary-600'); // Tailwind color class
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Display order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_stats');
    }
};
