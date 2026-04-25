<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueQuestion extends Model
{
    protected $table = 'issue_questions';

    protected $fillable = [
        'issue_id',
        'question',
        'context',
        'status',
        'answer',
        'answered_date',
        'answered_in_meeting_id',
        'raised_by',
    ];

    protected $casts = [
        'answered_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function answeredInMeeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'answered_in_meeting_id');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }
}



