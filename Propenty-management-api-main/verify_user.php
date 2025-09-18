<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Boot the application
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Get all users
    $users = App\Models\User::all();
    
    if ($users->count() > 0) {
        echo "Found " . $users->count() . " users:\n";
        echo "================================\n";
        
        foreach ($users as $user) {
            echo "User ID: " . $user->id . "\n";
            echo "Email: " . $user->email . "\n";
            echo "Name: " . $user->name . "\n";
            echo "Email verified at: " . ($user->email_verified_at ?? 'NULL') . "\n";
            echo "Has verified email: " . ($user->hasVerifiedEmail() ? 'true' : 'false') . "\n";
            echo "Is active: " . ($user->is_active ? 'true' : 'false') . "\n";
            
            // If user is not verified, verify them
            if (!$user->hasVerifiedEmail()) {
                echo "Verifying user email...\n";
                $user->email_verified_at = now();
                $user->save();
                echo "User email verified successfully!\n";
            }
            
            echo "--------------------------------\n";
        }
    } else {
        echo "No users found in the database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}