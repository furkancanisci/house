<?php

// Script to fix media records that reference the old bunny disk
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking Media Records ===\n";

try {
    // Check if media table exists
    if (!\Illuminate\Support\Facades\Schema::hasTable('media')) {
        echo "No media table found. Nothing to fix.\n";
        exit(0);
    }

    // Find media records with bunny disk
    $bunnyMediaRecords = \Illuminate\Support\Facades\DB::table('media')
        ->where('disk', 'bunny')
        ->get();

    echo "Found " . $bunnyMediaRecords->count() . " media records using 'bunny' disk\n";

    if ($bunnyMediaRecords->count() > 0) {
        echo "\nMedia records with bunny disk:\n";
        foreach ($bunnyMediaRecords as $record) {
            echo "- ID: {$record->id}, File: {$record->file_name}, Collection: {$record->collection_name}\n";
        }

        echo "\nDo you want to update these records to use 'public' disk? (y/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);

        if (trim($line) === 'y' || trim($line) === 'Y') {
            $updated = \Illuminate\Support\Facades\DB::table('media')
                ->where('disk', 'bunny')
                ->update(['disk' => 'public']);

            echo "✓ Updated {$updated} media records to use 'public' disk\n";
        } else {
            echo "No changes made.\n";
        }
    } else {
        echo "✓ No media records found using 'bunny' disk\n";
    }

    // Also check for any other non-standard disk references
    $allDisks = \Illuminate\Support\Facades\DB::table('media')
        ->select('disk')
        ->distinct()
        ->pluck('disk');

    echo "\nAll disk types in media table:\n";
    foreach ($allDisks as $disk) {
        $count = \Illuminate\Support\Facades\DB::table('media')->where('disk', $disk)->count();
        echo "- {$disk}: {$count} records\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Done ===\n";