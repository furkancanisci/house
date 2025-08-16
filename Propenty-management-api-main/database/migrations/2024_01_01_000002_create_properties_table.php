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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('property_type', [
                'apartment',
                'house', 
                'condo',
                'townhouse',
                'studio',
                'loft',
                'villa',
                'commercial',
                'land'
            ]);
            $table->enum('listing_type', ['rent', 'sale']);
            $table->decimal('price', 12, 2);
            $table->enum('price_type', ['monthly', 'yearly', 'total'])->default('monthly');
            
            // Location fields
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            // Country field removed - Syria-only application
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('neighborhood')->nullable();
            
            // Property details
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->integer('square_feet')->nullable();
            $table->integer('lot_size')->nullable();
            $table->integer('year_built')->nullable();
            $table->enum('parking_type', ['none', 'street', 'garage', 'driveway', 'carport'])->default('none');
            $table->integer('parking_spaces')->default(0);
            
            // Property status
            $table->enum('status', ['draft', 'active', 'pending', 'sold', 'rented', 'inactive'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->date('available_from')->nullable();
            
            // SEO and metadata
            $table->string('slug')->unique();
            $table->json('amenities')->nullable(); // Store as JSON array
            $table->json('nearby_places')->nullable(); // Store nearby amenities
            $table->integer('views_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            
            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['property_type', 'listing_type']);
            $table->index(['city', 'state']);
            $table->index(['price', 'listing_type']);
            $table->index(['is_featured', 'is_available']);
            $table->index(['latitude', 'longitude']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
