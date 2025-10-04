<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Property 11 currency check:\n";

$property = App\Models\Property::find(11);

if ($property) {
    echo "ID: " . $property->id . "\n";
    echo "Title: " . $property->title . "\n";
    echo "Currency: " . $property->currency . "\n";
    echo "Price: " . $property->price . "\n";
    echo "Listing Type: " . $property->listing_type . "\n";
} else {
    echo "Property 11 not found\n";
}