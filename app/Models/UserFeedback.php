<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFeedback extends Model
{
    use HasFactory;

    protected $table = 'user_feedback';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'office_name',
        'type',
        'message',
        'screenshot_path',
        'page_url',
        'page_title',
        'browser',
        'device',
        'screen_resolution',
        'console_errors',
        'status',
        'admin_notes',
        'assigned_to',
        'resolved_at',
    ];

    protected $casts = [
        'console_errors' => 'array',
        'resolved_at' => 'datetime',
    ];

    public const TYPES = [
        'bug' => 'Bug Report',
        'feature' => 'Feature Request',
        'general' => 'General Feedback',
    ];

    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'in_progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            'bug' => '🐛',
            'feature' => '💡',
            'general' => '💬',
            default => '📝',
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'new' => 'blue',
            'reviewing' => 'yellow',
            'in_progress' => 'purple',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }
}


