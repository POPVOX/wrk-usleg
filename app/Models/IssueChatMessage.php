<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueChatMessage extends Model
{
    use HasFactory;

    protected $table = 'issue_chat_messages';

    protected $fillable = [
        'issue_id',
        'user_id',
        'role',
        'content',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}



