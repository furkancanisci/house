<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BuildingType;
use App\Models\WindowType;
use App\Models\FloorType;
use App\Models\ViewType;
use App\Models\Direction;

echo "=== Checking Database Data ===\n\n";

// Check Building Types
echo "Building Types Count: " . BuildingType::count() . "\n";
echo "Active Building Types: " . BuildingType::active()->count() . "\n";
if (BuildingType::active()->count() > 0) {
    echo "First 3 Building Types:\n";
    BuildingType::active()->take(3)->get()->each(function($type) {
        echo "- {$type->name_en} ({$type->value})\n";
    });
}
echo "\n";

// Check Window Types
echo "Window Types Count: " . WindowType::count() . "\n";
echo "Active Window Types: " . WindowType::active()->count() . "\n";
if (WindowType::active()->count() > 0) {
    echo "First 3 Window Types:\n";
    WindowType::active()->take(3)->get()->each(function($type) {
        echo "- {$type->name_en} ({$type->value})\n";
    });
}
echo "\n";

// Check Floor Types
echo "Floor Types Count: " . FloorType::count() . "\n";
echo "Active Floor Types: " . FloorType::active()->count() . "\n";
if (FloorType::active()->count() > 0) {
    echo "First 3 Floor Types:\n";
    FloorType::active()->take(3)->get()->each(function($type) {
        echo "- {$type->name_en} ({$type->value})\n";
    });
}
echo "\n";

// Check View Types
echo "View Types Count: " . ViewType::count() . "\n";
echo "Active View Types: " . ViewType::active()->count() . "\n";
if (ViewType::active()->count() > 0) {
    echo "First 3 View Types:\n";
    ViewType::active()->take(3)->get()->each(function($type) {
        echo "- {$type->name_en} ({$type->value})\n";
    });
}
echo "\n";

// Check Directions
echo "Directions Count: " . Direction::count() . "\n";
echo "Active Directions: " . Direction::active()->count() . "\n";
if (Direction::active()->count() > 0) {
    echo "First 3 Directions:\n";
    Direction::active()->take(3)->get()->each(function($type) {
        echo "- {$type->name_en} ({$type->value})\n";
    });
}
echo "\n";

echo "=== End Check ===\n";