<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Try to find a user
    $user = User::first();
    
    if (!$user) {
        echo "No user found in database\n";
        exit(1);
    }
    
    echo "Found user: " . $user->id . "\n";
    echo "Current first_name: " . $user->first_name . "\n";
    echo "Current last_name: " . $user->last_name . "\n";
    echo "Current email: " . $user->email . "\n";
    
    // Try to update the user
    echo "Attempting to update user...\n";
    $result = $user->update([
        'first_name' => 'TestFirstName',
        'last_name' => 'TestLastName',
        'updated_at' => now()
    ]);
    
    echo "Update result: " . ($result ? 'success' : 'failed') . "\n";
    
    // Refresh the user data
    $user->refresh();
    echo "Updated first_name: " . $user->first_name . "\n";
    echo "Updated last_name: " . $user->last_name . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}