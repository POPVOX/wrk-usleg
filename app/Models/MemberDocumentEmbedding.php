<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDocumentEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_document_id',
        'chunk_text',
        'chunk_index',
        'embedding',
        'metadata',
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
        'chunk_index' => 'integer',
    ];

    /**
     * Get the parent document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(MemberDocument::class, 'member_document_id');
    }

    /**
     * Scope ordered by chunk index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('chunk_index');
    }
}
