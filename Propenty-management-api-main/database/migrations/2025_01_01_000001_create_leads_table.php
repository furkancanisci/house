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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('website'); // website, contact_form, listing_inquiry, phone, walk-in
            $table->string('status')->default('new'); // new, in_progress, qualified, unqualified, closed
            
            // Contact Information
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            
            // Lead Details
            $table->string('property_type')->nullable();
            $table->string('listing_type')->nullable(); // rent, sale
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->date('move_in_date')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            
            // Related Property (if inquiry is about specific property)
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            
            // Internal Notes
            $table->text('internal_notes')->nullable();
            $table->integer('quality_score')->nullable(); // 1-10 rating
            
            // Contact History
            $table->timestamp('last_contacted_at')->nullable();
            $table->integer('contact_attempts')->default(0);
            $table->timestamp('converted_at')->nullable();
            
            // Metadata
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->json('utm_parameters')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('source');
            $table->index('assigned_to');
            $table->index('property_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};