<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AiUsage extends Model
{
    protected $table = 'ai_usage';

    protected $fillable = [
        'user_id',
        'feature',
        'model',
        'input_tokens',
        'output_tokens',
        'prompt_preview',
    ];

    public const FEATURES = [
        'meeting_summary' => 'Meeting Summaries',
        'knowledge_qa' => 'Knowledge Hub Q&A',
        'briefing' => 'Briefing Generation',
        'research' => 'Issue Research',
        'member_qa' => 'Member Hub Q&A',
        'team_resources' => 'Team Resources Q&A',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFeatureLabelAttribute(): string
    {
        return self::FEATURES[$this->feature] ?? ucfirst(str_replace('_', ' ', $this->feature));
    }

    /**
     * Get usage count for current billing period (this month)
     */
    public static function getMonthlyUsageCount(?int $userId = null): int
    {
        $query = static::where('created_at', '>=', Carbon::now()->startOfMonth());
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->count();
    }

    /**
     * Get usage breakdown by feature for current month
     */
    public static function getMonthlyUsageByFeature(?int $userId = null): array
    {
        $query = static::where('created_at', '>=', Carbon::now()->startOfMonth())
            ->selectRaw('feature, COUNT(*) as count')
            ->groupBy('feature');
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->pluck('count', 'feature')->toArray();
    }

    /**
     * Log an AI usage event
     */
    public static function log(
        int $userId,
        string $feature,
        ?string $model = null,
        ?int $inputTokens = null,
        ?int $outputTokens = null,
        ?string $promptPreview = null
    ): self {
        return static::create([
            'user_id' => $userId,
            'feature' => $feature,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'prompt_preview' => $promptPreview ? substr($promptPreview, 0, 200) : null,
        ]);
    }
}


