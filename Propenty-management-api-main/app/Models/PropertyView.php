<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyView extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'property_views';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'property_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'device_info',
        'viewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'device_info' => 'array',
        'viewed_at' => 'datetime',
    ];

    /**
     * Disable timestamps for this model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The property that was viewed.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * The user who viewed the property (nullable).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new property view record.
     */
    public static function recordView(Property $property, $request): self
    {
        return self::create([
            'property_id' => $property->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'device_info' => [
                'browser' => self::getBrowser($request->userAgent()),
                'platform' => self::getPlatform($request->userAgent()),
                'is_mobile' => $request->header('sec-ch-ua-mobile') === '?1',
            ],
            'viewed_at' => now(),
        ]);
    }

    /**
     * Extract browser information from user agent.
     */
    private static function getBrowser($userAgent): string
    {
        if (str_contains($userAgent, 'Chrome')) return 'Chrome';
        if (str_contains($userAgent, 'Firefox')) return 'Firefox';
        if (str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'Edge')) return 'Edge';
        if (str_contains($userAgent, 'Opera')) return 'Opera';
        
        return 'Unknown';
    }

    /**
     * Extract platform information from user agent.
     */
    private static function getPlatform($userAgent): string
    {
        if (str_contains($userAgent, 'Windows')) return 'Windows';
        if (str_contains($userAgent, 'Mac')) return 'macOS';
        if (str_contains($userAgent, 'Linux')) return 'Linux';
        if (str_contains($userAgent, 'Android')) return 'Android';
        if (str_contains($userAgent, 'iOS')) return 'iOS';
        
        return 'Unknown';
    }

    /**
     * Scope for filtering views by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering unique views (by IP and user).
     */
    public function scopeUnique($query)
    {
        return $query->distinct(['ip_address', 'user_id']);
    }
}
