<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MemberLocation extends Model
{
    protected $fillable = [
        'location_name',
        'timezone',
        'latitude',
        'longitude',
        'current_activity',
        'activity_until',
        'is_current',
        'updated_by',
    ];

    protected $casts = [
        'activity_until' => 'datetime',
        'is_current' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public const LOCATIONS = [
        'Washington, DC' => 'America/New_York',
        'District Office' => 'America/New_York', // Default, can be overridden
        'Traveling' => 'America/New_York',
        'In District' => 'America/New_York',
    ];

    public const TIMEZONE_OPTIONS = [
        'America/New_York' => 'Eastern (ET)',
        'America/Chicago' => 'Central (CT)',
        'America/Denver' => 'Mountain (MT)',
        'America/Los_Angeles' => 'Pacific (PT)',
        'America/Anchorage' => 'Alaska (AKT)',
        'Pacific/Honolulu' => 'Hawaii (HT)',
    ];

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getCurrent(): ?self
    {
        return static::where('is_current', true)->latest()->first();
    }

    /**
     * Alias for getCurrent() for compatibility
     */
    public static function getCurrentLocation(): ?self
    {
        return static::getCurrent();
    }

    public static function updateLocation(array $data, int $userId): self
    {
        // Set all previous to not current
        static::where('is_current', true)->update(['is_current' => false]);

        return static::create([
            'location_name' => $data['location_name'],
            'timezone' => $data['timezone'] ?? 'America/New_York',
            'current_activity' => $data['current_activity'] ?? null,
            'activity_until' => $data['activity_until'] ?? null,
            'is_current' => true,
            'updated_by' => $userId,
        ]);
    }

    public function getLocalTimeAttribute(): string
    {
        return Carbon::now($this->timezone)->format('g:i A T');
    }

    public function getTimezoneLabelAttribute(): string
    {
        $labels = [
            'America/New_York' => 'Eastern',
            'America/Chicago' => 'Central',
            'America/Denver' => 'Mountain',
            'America/Los_Angeles' => 'Pacific',
        ];

        return $labels[$this->timezone] ?? $this->timezone;
    }
}
