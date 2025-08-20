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
        Schema::create('email_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('verification_token')->unique();
            $table->string('status')->default('pending'); // pending, sent, verified, expired, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // Additional data like user_type, attempt_count, etc.
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['verification_token']);
            $table->index(['expires_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verification_logs');
    }
};