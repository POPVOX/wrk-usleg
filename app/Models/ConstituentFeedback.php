<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConstituentFeedback extends Model
{
    protected $table = 'constituent_feedback';

    protected $fillable = [
        'source',
        'issue',
        'summary',
        'sentiment',
        'received_date',
        'count',
        'related_meeting_id',
        'metadata',
    ];

    protected $casts = [
        'received_date' => 'date',
        'metadata' => 'array',
    ];

    public const SOURCES = [
        'phone' => 'Phone Call',
        'email' => 'Email',
        'town_hall' => 'Town Hall',
        'office_hours' => 'Office Hours',
        'letter' => 'Letter',
        'social_media' => 'Social Media',
    ];

    public const SENTIMENTS = [
        'positive' => 'Positive',
        'neutral' => 'Neutral',
        'negative' => 'Negative',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'related_meeting_id');
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst($this->source);
    }

    public static function getTopIssues(int $days = 30, int $limit = 5)
    {
        return static::where('received_date', '>=', now()->subDays($days))
            ->select('issue')
            ->selectRaw('SUM(count) as total_count')
            ->selectRaw('COUNT(*) as feedback_entries')
            ->groupBy('issue')
            ->orderByDesc('total_count')
            ->limit($limit)
            ->get();
    }

    public static function getStats(int $days = 30): array
    {
        $since = now()->subDays($days);

        return [
            'total_count' => static::where('received_date', '>=', $since)->sum('count'),
            'by_source' => static::where('received_date', '>=', $since)
                ->select('source')
                ->selectRaw('SUM(count) as count')
                ->groupBy('source')
                ->pluck('count', 'source')
                ->toArray(),
            'by_sentiment' => static::where('received_date', '>=', $since)
                ->select('sentiment')
                ->selectRaw('SUM(count) as count')
                ->groupBy('sentiment')
                ->pluck('count', 'sentiment')
                ->toArray(),
        ];
    }
}



