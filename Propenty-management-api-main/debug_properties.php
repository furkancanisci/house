<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;

echo "=== Property Status Debug ===\n";
echo "Total properties: " . Property::count() . "\n";
echo "Active properties (status=active AND is_available=true): " . Property::active()->count() . "\n";
echo "\n=== Status Distribution ===\n";

$statusCounts = Property::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get();

foreach ($statusCounts as $status) {
    echo $status->status . ": " . $status->count . "\n";
}

echo "\n=== Availability Distribution ===\n";
$availabilityCounts = Property::selectRaw('is_available, COUNT(*) as count')
    ->groupBy('is_available')
    ->get();

foreach ($availabilityCounts as $availability) {
    $label = $availability->is_available ? 'Available (true)' : 'Not Available (false)';
    echo $label . ": " . $availability->count . "\n";
}

echo "\n=== Sample Properties ===\n";
$sampleProperties = Property::select('id', 'title', 'status', 'is_available')->limit(5)->get();

foreach ($sampleProperties as $property) {
    echo "ID: {$property->id} | Title: {$property->title} | Status: {$property->status} | Available: " . ($property->is_available ? 'true' : 'false') . "\n";
}

echo "\n=== Testing Active Scope ===\n";
$activeProperties = Property::active()->select('id', 'title', 'status', 'is_available')->get();
echo "Properties returned by active() scope: " . $activeProperties->count() . "\n";

if ($activeProperties->count() > 0) {
    echo "First active property: ID {$activeProperties->first()->id}, Status: {$activeProperties->first()->status}, Available: " . ($activeProperties->first()->is_available ? 'true' : 'false') . "\n";
}