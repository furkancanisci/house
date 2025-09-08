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
        Schema::table('users', function (Blueprint $table) {
            // Check and add columns only if they don't exist
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('state');
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('postal_code');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('UTC')->after('last_login_at');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 10)->default('en')->after('timezone');
            }
            if (!Schema::hasColumn('users', 'date_format')) {
                $table->string('date_format', 20)->default('Y-m-d')->after('language');
            }
        });
        
        // Add indexes separately
        Schema::table('users', function (Blueprint $table) {
            if (!collect(Schema::getIndexes('users'))->pluck('name')->contains('users_city_index')) {
                $table->index('city');
            }
            if (!collect(Schema::getIndexes('users'))->pluck('name')->contains('users_state_index')) {
                $table->index('state');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['city']);
            $table->dropIndex(['state']);
            
            // Drop columns
            $table->dropColumn([
                'avatar',
                'bio',
                'address',
                'city',
                'state',
                'postal_code',
                'last_login_at',
                'timezone',
                'language',
                'date_format'
            ]);
        });
    }
};
