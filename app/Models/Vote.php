<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'vote_number',
        'bill_number',
        'question',
        'vote_cast',
        'vote_date',
        'result',
        'vote_count_yea',
        'vote_count_nay',
        'congress',
        'chamber',
        'congress_api_url',
    ];

    protected $casts = [
        'vote_date' => 'date',
        'vote_count_yea' => 'integer',
        'vote_count_nay' => 'integer',
    ];

    /**
     * Vote cast options.
     */
    public const VOTE_OPTIONS = [
        'YEA' => 'Yea',
        'NAY' => 'Nay',
        'PRESENT' => 'Present',
        'NOT VOTING' => 'Not Voting',
    ];

    /**
     * Get the related bill if exists.
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_number', 'bill_number');
    }

    /**
     * Scope for Yea votes.
     */
    public function scopeYea($query)
    {
        return $query->where('vote_cast', 'YEA');
    }

    /**
     * Scope for Nay votes.
     */
    public function scopeNay($query)
    {
        return $query->where('vote_cast', 'NAY');
    }

    /**
     * Scope for recent votes.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('vote_date', '>=', now()->subDays($days))
            ->orderBy('vote_date', 'desc');
    }

    /**
     * Scope for current congress.
     */
    public function scopeCurrentCongress($query)
    {
        return $query->where('congress', config('office.current_congress', '119'));
    }

    /**
     * Get vote label with styling class.
     */
    public function getVoteLabelAttribute(): string
    {
        return self::VOTE_OPTIONS[$this->vote_cast] ?? $this->vote_cast;
    }

    /**
     * Get CSS class for vote display.
     */
    public function getVoteColorClassAttribute(): string
    {
        return match ($this->vote_cast) {
            'YEA' => 'text-green-600 bg-green-100',
            'NAY' => 'text-red-600 bg-red-100',
            'PRESENT' => 'text-amber-600 bg-amber-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }
}
