<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source',
        'status',
        'name',
        'email',
        'phone',
        'message',
        'property_type',
        'listing_type',
        'budget_min',
        'budget_max',
        'preferred_location',
        'bedrooms',
        'bathrooms',
        'move_in_date',
        'assigned_to',
        'assigned_at',
        'property_id',
        'internal_notes',
        'quality_score',
        'last_contacted_at',
        'contact_attempts',
        'converted_at',
        'ip_address',
        'user_agent',
        'referrer',
        'utm_parameters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'move_in_date' => 'date',
        'assigned_at' => 'datetime',
        'last_contacted_at' => 'datetime',
        'converted_at' => 'datetime',
        'utm_parameters' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    /**
     * Available lead sources.
     */
    const SOURCES = [
        'website' => 'Website',
        'contact_form' => 'Contact Form',
        'listing_inquiry' => 'Listing Inquiry',
        'phone' => 'Phone',
        'walk_in' => 'Walk-in',
        'referral' => 'Referral',
        'social_media' => 'Social Media',
    ];

    /**
     * Available lead statuses.
     */
    const STATUSES = [
        'new' => 'New',
        'in_progress' => 'In Progress',
        'qualified' => 'Qualified',
        'unqualified' => 'Unqualified',
        'closed' => 'Closed',
        'converted' => 'Converted',
    ];

    /**
     * Get the property associated with the lead.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user assigned to the lead.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by source.
     */
    public function scopeSource(Builder $query, $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope for new leads.
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for assigned leads.
     */
    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereNotNull('assigned_to');
    }

    /**
     * Scope for unassigned leads.
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for converted leads.
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->whereNotNull('converted_at');
    }

    /**
     * Scope for leads by date range.
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Mark lead as contacted.
     */
    public function markAsContacted(): void
    {
        $this->update([
            'last_contacted_at' => now(),
            'contact_attempts' => $this->contact_attempts + 1,
        ]);
    }

    /**
     * Mark lead as converted.
     */
    public function markAsConverted(): void
    {
        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);
    }

    /**
     * Assign lead to user.
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
        ]);
    }

    /**
     * Get formatted budget range.
     */
    public function getFormattedBudgetAttribute(): string
    {
        if ($this->budget_min && $this->budget_max) {
            return '$' . number_format($this->budget_min) . ' - $' . number_format($this->budget_max);
        } elseif ($this->budget_min) {
            return '$' . number_format($this->budget_min) . '+';
        } elseif ($this->budget_max) {
            return 'Up to $' . number_format($this->budget_max);
        }
        
        return 'Not specified';
    }

    /**
     * Get quality score color.
     */
    public function getQualityScoreColorAttribute(): string
    {
        if (!$this->quality_score) {
            return 'secondary';
        }

        if ($this->quality_score >= 8) {
            return 'success';
        } elseif ($this->quality_score >= 5) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'in_progress' => 'info',
            'qualified' => 'success',
            'unqualified' => 'warning',
            'closed' => 'secondary',
            'converted' => 'success',
            default => 'secondary',
        };
    }
}