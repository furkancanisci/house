<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyView;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertyViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active properties
        $activeProperties = Property::where('status', 'active')->get();
        
        // Get all users
        $users = User::all();

        if ($activeProperties->isEmpty()) {
            return;
        }

        // Sample IP addresses for anonymous views
        $sampleIPs = [
            '192.168.1.100',
            '10.0.0.50',
            '172.16.0.25',
            '203.0.113.10',
            '198.51.100.5',
            '192.0.2.15',
            '172.20.0.30',
            '10.1.1.75',
        ];

        // Sample user agents
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/88.0',
        ];

        foreach ($activeProperties as $property) {
            // Each property gets 5-20 views (reduced for simplicity)
            $viewsCount = rand(5, 20);

            for ($i = 0; $i < $viewsCount; $i++) {
                // 60% chance it's an authenticated user view, 40% anonymous
                $user = (rand(1, 10) <= 6 && !$users->isEmpty()) ? $users->random() : null;
                
                // Don't let property owners view their own properties in stats
                if ($user && $user->id === $property->user_id) {
                    $user = null;
                }

                PropertyView::create([
                    'property_id' => $property->id,
                    'user_id' => $user?->id,
                    'ip_address' => $user ? $this->generateUserIP($user->id) : $sampleIPs[array_rand($sampleIPs)],
                    'user_agent' => $userAgents[array_rand($userAgents)],
                ]);
            }
        }
    }

    /**
     * Generate a consistent IP for a user (simulating same user, same IP)
     */
    private function generateUserIP($userId): string
    {
        $baseIPs = [
            '192.168.1.',
            '10.0.0.',
            '172.16.0.',
            '203.0.113.',
        ];
        
        $baseIP = $baseIPs[$userId % count($baseIPs)];
        $lastOctet = ($userId % 200) + 50; // Generate 50-249
        
        return $baseIP . $lastOctet;
    }

    /**
     * Extract browser from user agent
     */
    private function getBrowserFromUserAgent($userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        
        return 'Unknown';
    }

    /**
     * Extract platform from user agent
     */
    private function getPlatformFromUserAgent($userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Macintosh')) return 'macOS';
        if (str_contains($userAgent, 'iPhone')) return 'iOS';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        
        return 'Unknown';
    }
}
