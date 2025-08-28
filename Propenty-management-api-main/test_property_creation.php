<?php
// Test script to verify property creation works correctly

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test data
$testData = [
    'title' => 'Test Property ' . Str::random(5),
    'description' => 'This is a test property for verification',
    'property_type' => 'apartment',
    'listing_type' => 'rent',
    'price' => 1500.00,
    'street_address' => '123 Test Street',
    'city' => 'Test City',
    'state' => 'Test State',
    'postal_code' => '12345',
    'bedrooms' => 2,
    'bathrooms' => 1,
    'contact_name' => 'Test User',
    'contact_phone' => '555-1234',
    'contact_email' => 'test@example.com',
    'user_id' => 1,
    'status' => 'active',
    'is_available' => true,
    'published_at' => now(),
    'views_count' => 0,
];

try {
    // Generate slug
    $baseSlug = Str::slug($testData['title']);
    $slug = $baseSlug;
    $counter = 1;
    
    while (DB::table('properties')->where('slug', $slug)->exists()) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
    }
    $testData['slug'] = $slug;
    
    // Add timestamps
    $testData['created_at'] = now();
    $testData['updated_at'] = now();
    
    echo "Attempting to insert property with data:\n";
    print_r(array_keys($testData));
    
    // Test insertGetId
    $propertyId = DB::table('properties')->insertGetId($testData);
    
    if ($propertyId) {
        echo "SUCCESS: Property created with ID: $propertyId\n";
        
        // Retrieve the property
        $property = DB::table('properties')->find($propertyId);
        if ($property) {
            echo "SUCCESS: Property retrieved successfully\n";
            echo "Title: " . $property->title . "\n";
            echo "Slug: " . $property->slug . "\n";
        } else {
            echo "ERROR: Could not retrieve property\n";
        }
        
        // Clean up - delete the test property
        DB::table('properties')->where('id', $propertyId)->delete();
        echo "Cleaned up test property\n";
    } else {
        echo "ERROR: Failed to create property\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo "TRACE: " . $e->getTraceAsString() . "\n";
}