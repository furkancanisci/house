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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar'); // الاسم بالعربية
            $table->string('name_en'); // الاسم بالإنجليزية
            $table->string('name_ku')->nullable(); // الاسم بالكردية
            $table->string('slug')->unique(); // URL slug
            $table->string('country_ar')->default('سوريا'); // البلد بالعربية
            $table->string('country_en')->default('Syria'); // البلد بالإنجليزية
            $table->string('state_ar')->nullable(); // المحافظة بالعربية
            $table->string('state_en')->nullable(); // المحافظة بالإنجليزية
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};