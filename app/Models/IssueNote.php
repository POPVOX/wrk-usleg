<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueNote extends Model
{
    protected $table = 'issue_notes';

    protected $fillable = [
        'issue_id',
        'user_id',
        'content',
        'note_type',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    public const NOTE_TYPES = [
        'update' => 'Update',
        'decision' => 'Decision',
        'blocker' => 'Blocker',
        'general' => 'General',
    ];

    public const NOTE_COLORS = [
        'update' => 'blue',
        'decision' => 'green',
        'blocker' => 'red',
        'general' => 'gray',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getColorAttribute(): string
    {
        return self::NOTE_COLORS[$this->note_type] ?? 'gray';
    }
}



