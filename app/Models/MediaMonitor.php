<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaMonitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'query',
        'monitor_type',
        'source_type',
        'cadence',
        'is_active',
        'auto_approve',
        'topic_id',
        'issue_id',
        'created_by',
        'last_checked_at',
        'last_clip_at',
        'clips_found',
        'last_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_approve' => 'boolean',
        'last_checked_at' => 'datetime',
        'last_clip_at' => 'datetime',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getCadenceLabelAttribute(): string
    {
        return match ($this->cadence) {
            'hourly' => 'Hourly',
            'three_times_daily' => '3x Daily',
            'daily' => 'Daily',
            default => ucfirst(str_replace('_', ' ', $this->cadence)),
        };
    }

    public function getMonitorTypeLabelAttribute(): string
    {
        return match ($this->monitor_type) {
            'member' => 'Member',
            'topic' => 'Topic',
            'issue' => 'Issue',
            'custom' => 'Custom',
            default => ucfirst($this->monitor_type),
        };
    }

    public function minimumIntervalInMinutes(): int
    {
        return match ($this->cadence) {
            'hourly' => 60,
            'three_times_daily' => 360,
            'daily' => 1440,
            default => 60,
        };
    }

    public function isDue(?\Carbon\CarbonInterface $now = null): bool
    {
        $now ??= now();

        if (!$this->is_active) {
            return false;
        }

        if ($this->last_checked_at === null) {
            return true;
        }

        return $this->last_checked_at->diffInMinutes($now) >= $this->minimumIntervalInMinutes();
    }
}
