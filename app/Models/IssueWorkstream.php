<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueWorkstream extends Model
{
    use HasFactory;

    protected $table = 'issue_workstreams';

    protected $fillable = [
        'issue_id',
        'name',
        'description',
        'color',
        'icon',
        'status',
        'sort_order',
    ];

    public const STATUSES = [
        'planning' => 'Planning',
        'active' => 'Active',
        'completed' => 'Completed',
        'paused' => 'Paused',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(IssuePublication::class, 'workstream_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(IssueEvent::class, 'workstream_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(IssueMilestone::class, 'workstream_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(IssueDocument::class, 'workstream_id');
    }
}



