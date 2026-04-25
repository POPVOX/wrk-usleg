<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiInsight extends Model
{
    protected $fillable = [
        'insight_type',
        'title',
        'description',
        'reasoning',
        'priority',
        'category',
        'related_items',
        'dismissed',
        'dismissed_at',
        'dismissed_by',
    ];

    protected $casts = [
        'related_items' => 'array',
        'dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
    ];

    public const INSIGHT_TYPES = [
        'suggestion' => 'Suggestion',
        'pattern' => 'Pattern Detected',
        'alert' => 'Alert',
        'opportunity' => 'Opportunity',
    ];

    public const PRIORITIES = [
        'urgent' => 'Urgent',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
    ];

    public const CATEGORIES = [
        'communication' => 'Communication',
        'legislation' => 'Legislation',
        'district' => 'District',
        'media' => 'Media',
        'constituent' => 'Constituent',
    ];

    public function dismissedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dismissed_by');
    }

    public function dismiss(int $userId): void
    {
        $this->update([
            'dismissed' => true,
            'dismissed_at' => now(),
            'dismissed_by' => $userId,
        ]);
    }

    public static function getActive(int $limit = 5)
    {
        return static::where('dismissed', false)
            ->orderByRaw("CASE 
                WHEN priority = 'urgent' THEN 1 
                WHEN priority = 'high' THEN 2 
                WHEN priority = 'medium' THEN 3 
                ELSE 4 END")
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public static function createSuggestion(string $title, string $description, string $category, string $priority = 'medium', ?string $reasoning = null): self
    {
        return static::create([
            'insight_type' => 'suggestion',
            'title' => $title,
            'description' => $description,
            'reasoning' => $reasoning,
            'priority' => $priority,
            'category' => $category,
        ]);
    }
}



