<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueDecision extends Model
{
    protected $table = 'issue_decisions';

    protected $fillable = [
        'issue_id',
        'title',
        'description',
        'rationale',
        'context',
        'meeting_id',
        'decision_date',
        'decided_by',
        'created_by',
    ];

    protected $casts = [
        'decision_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}



