<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Amenity;
use App\Models\Property;

echo "Checking for unused amenities...\n\n";

// Get all amenities that are not associated with any properties
$unusedAmenities = Amenity::doesntHave('properties')->get();

echo "Total amenities: " . Amenity::count() . "\n";
echo "Unused amenities: " . $unusedAmenities->count() . "\n\n";

if ($unusedAmenities->count() > 0) {
    echo "List of unused amenities:\n";
    echo "========================\n";
    foreach ($unusedAmenities as $amenity) {
        echo "ID: {$amenity->id}\n";
        echo "Name: {$amenity->name}\n";
        echo "Slug: {$amenity->slug}\n";
        echo "Category: {$amenity->category}\n";
        echo "Active: " . ($amenity->is_active ? 'Yes' : 'No') . "\n";
        echo "Created: {$amenity->created_at}\n";
        echo "---\n";
    }
    
    echo "\nThese amenities can be safely deleted as they are not used by any properties.\n";
} else {
    echo "All amenities are currently being used by properties.\n";
}

// Also check for amenities that exist in property JSON but not in amenities table
echo "\n" . str_repeat('=', 50) . "\n";
echo "Checking for orphaned amenity references in properties...\n\n";

$properties = Property::whereNotNull('amenities')->get();
$allAmenityNames = Amenity::pluck('name')->toArray();
$orphanedAmenities = [];

foreach ($properties as $property) {
    $propertyAmenities = is_string($property->amenities) 
        ? json_decode($property->amenities, true) 
        : $property->amenities;
    
    if (is_array($propertyAmenities)) {
        foreach ($propertyAmenities as $amenityName) {
            if (!in_array($amenityName, $allAmenityNames) && !in_array($amenityName, $orphanedAmenities)) {
                $orphanedAmenities[] = $amenityName;
            }
        }
    }
}

if (!empty($orphanedAmenities)) {
    echo "Found orphaned amenity references in properties:\n";
    foreach ($orphanedAmenities as $orphaned) {
        echo "- {$orphaned}\n";
    }
    echo "\nThese amenity names exist in property data but not in the amenities table.\n";
} else {
    echo "No orphaned amenity references found.\n";
}

echo "\nAnalysis complete.\n";