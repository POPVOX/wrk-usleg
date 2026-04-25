<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'bill_type',
        'title',
        'sponsor_role',
        'introduced_date',
        'status',
        'summary',
        'committee',
        'cosponsors',
        'congress',
        'congress_api_url',
    ];

    protected $casts = [
        'introduced_date' => 'date',
        'cosponsors' => 'array',
    ];

    /**
     * Bill type constants.
     */
    public const TYPES = [
        'HR' => 'House Bill',
        'S' => 'Senate Bill',
        'HRES' => 'House Resolution',
        'SRES' => 'Senate Resolution',
        'HJRES' => 'House Joint Resolution',
        'SJRES' => 'Senate Joint Resolution',
        'HCONRES' => 'House Concurrent Resolution',
        'SCONRES' => 'Senate Concurrent Resolution',
    ];

    /**
     * Scope for sponsored bills.
     */
    public function scopeSponsored($query)
    {
        return $query->where('sponsor_role', 'sponsor');
    }

    /**
     * Scope for cosponsored bills.
     */
    public function scopeCosponsored($query)
    {
        return $query->where('sponsor_role', 'cosponsor');
    }

    /**
     * Scope for current congress.
     */
    public function scopeCurrentCongress($query)
    {
        return $query->where('congress', config('office.current_congress', '119'));
    }

    /**
     * Get the bill type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->bill_type] ?? $this->bill_type;
    }

    /**
     * Get Congress.gov URL.
     */
    public function getCongressGovUrlAttribute(): string
    {
        $type = strtolower($this->bill_type);
        $number = preg_replace('/[^0-9]/', '', $this->bill_number);
        return "https://www.congress.gov/bill/{$this->congress}th-congress/{$type}/{$number}";
    }
}
