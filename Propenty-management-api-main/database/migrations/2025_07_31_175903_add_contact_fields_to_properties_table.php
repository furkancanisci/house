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
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                if (!Schema::hasColumn('properties', 'contact_name')) {
                    $table->string('contact_name')->nullable();
                }
                if (!Schema::hasColumn('properties', 'contact_phone')) {
                    $table->string('contact_phone', 20)->nullable();
                }
                if (!Schema::hasColumn('properties', 'contact_email')) {
                    $table->string('contact_email')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('properties')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn(['contact_name', 'contact_phone', 'contact_email']);
            });
        }
    }
};
