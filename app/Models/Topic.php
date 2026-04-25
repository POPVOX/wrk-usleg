<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Topic model (renamed from Issue)
 * 
 * Simple tags/topics for categorizing meetings and issues.
 * Think of these as policy areas or subject tags.
 */
class Topic extends Model
{
    use HasFactory;

    protected $table = 'topics';

    protected $fillable = [
        'name',
    ];

    /**
     * Get the meetings that discuss this topic.
     */
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'meeting_topic')
            ->withTimestamps();
    }

    /**
     * Get the issues related to this topic.
     */
    public function issues(): BelongsToMany
    {
        return $this->belongsToMany(Issue::class, 'issue_topic')
            ->withTimestamps();
    }
}



