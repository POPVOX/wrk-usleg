<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionEvolution extends Model
{
    protected $fillable = [
        'issue',
        'event_type',
        'event_date',
        'description',
        'reasoning',
        'influence_weight',
        'related_bill_id',
        'related_meeting_id',
        'previous_event_id',
        'metadata',
    ];

    protected $casts = [
        'event_date' => 'date',
        'metadata' => 'array',
    ];

    public const EVENT_TYPES = [
        'initial_position' => 'Initial Position',
        'constituent_input' => 'Constituent Input',
        'committee_learning' => 'Committee Learning',
        'bill_sponsorship' => 'Bill Sponsorship',
        'vote' => 'Vote',
        'statement' => 'Public Statement',
        'position_shift' => 'Position Shift',
        'town_hall_moment' => 'Town Hall Moment',
        'committee_hearing' => 'Committee Hearing',
        'bipartisan_collaboration' => 'Bipartisan Collaboration',
    ];

    public const COMMON_ISSUES = [
        'Veterans Affairs',
        'Healthcare',
        'Immigration',
        'National Defense',
        'Education',
        'Environment',
        'Economy',
        'Infrastructure',
        'Civil Rights',
        'Foreign Policy',
    ];

    public function previousEvent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'previous_event_id');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'related_meeting_id');
    }

    public function getEventTypeLabelAttribute(): string
    {
        return self::EVENT_TYPES[$this->event_type] ?? ucfirst(str_replace('_', ' ', $this->event_type));
    }

    public static function getIssueTimeline(string $issue): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('issue', $issue)
            ->orderBy('event_date', 'asc')
            ->get();
    }

    public static function getTopIssues(int $limit = 5): \Illuminate\Support\Collection
    {
        return static::select('issue')
            ->selectRaw('COUNT(*) as event_count')
            ->selectRaw('MAX(event_date) as latest_event')
            ->groupBy('issue')
            ->orderByDesc('event_count')
            ->limit($limit)
            ->get();
    }
}



