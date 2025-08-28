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
            $table->unsignedBigInteger('user_id'); // Will add foreign key later
            $table->string('title');
            $table->text('description');
            $table->string('property_type'); // apartment, house, condo, townhouse, studio, loft, villa, commercial, land
            $table->string('listing_type'); // rent, sale
            $table->decimal('price', 12, 2);
            $table->string('price_type')->default('monthly'); // monthly, yearly, total
            
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
            $table->string('parking_type')->default('none'); // none, street, garage, driveway, carport
            $table->integer('parking_spaces')->default(0);
            
            // Property status
            $table->string('status')->default('draft'); // draft, active, pending, sold, rented, inactive
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_available')->default(true);
            $table->date('available_from')->nullable();
            
            // SEO and metadata
            $table->string('slug'); // Remove unique constraint temporarily
            $table->json('amenities')->nullable(); // Store as JSON array
            $table->json('nearby_places')->nullable(); // Store nearby amenities
            $table->integer('views_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            
            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
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
