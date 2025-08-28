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
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'limited_access_enabled')) {
                    $table->boolean('limited_access_enabled')->default(false)->after('last_login_at');
                }
                if (!Schema::hasColumn('users', 'limited_access_granted_at')) {
                    $table->timestamp('limited_access_granted_at')->nullable()->after('limited_access_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['limited_access_enabled', 'limited_access_granted_at']);
            });
        }
    }
};
