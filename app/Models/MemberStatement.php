<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberStatement extends Model
{
    protected $fillable = [
        'statement_type',
        'title',
        'content',
        'summary',
        'published_date',
        'outlet',
        'url',
        'topics',
        'related_bills',
        'media_pickups',
        'created_by',
    ];

    protected $casts = [
        'published_date' => 'date',
        'topics' => 'array',
        'related_bills' => 'array',
    ];

    public const STATEMENT_TYPES = [
        'press_release' => 'Press Release',
        'floor_speech' => 'Floor Speech',
        'op_ed' => 'Op-Ed',
        'social_media' => 'Social Media',
        'interview' => 'Interview',
        'letter' => 'Letter',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatementTypeLabelAttribute(): string
    {
        return self::STATEMENT_TYPES[$this->statement_type] ?? ucfirst(str_replace('_', ' ', $this->statement_type));
    }

    public static function getRecentByType(string $type, int $limit = 5)
    {
        return static::where('statement_type', $type)
            ->orderByDesc('published_date')
            ->limit($limit)
            ->get();
    }

    public static function getRecent(int $days = 7, int $limit = 10)
    {
        return static::where('published_date', '>=', now()->subDays($days))
            ->orderByDesc('published_date')
            ->limit($limit)
            ->get();
    }
}
