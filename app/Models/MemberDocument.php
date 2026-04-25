<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'document_type',
        'description',
        'file_path',
        'content',
        'metadata',
        'document_date',
        'is_public',
        'source',
        'tags',
        'indexed',
        'summary',
        'uploaded_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'document_date' => 'date',
        'is_public' => 'boolean',
        'tags' => 'array',
        'indexed' => 'boolean',
    ];

    /**
     * Document types.
     */
    public const TYPES = [
        'biography' => 'Biography',
        'position_paper' => 'Position Paper',
        'speech' => 'Speech/Statement',
        'interview' => 'Interview',
        'campaign_material' => 'Campaign Material',
        'awards' => 'Awards/Recognition',
        'personal_history' => 'Personal History',
        'district_connection' => 'District Connection',
        'voting_explanation' => 'Voting Explanation',
        'constituent_letter' => 'Constituent Letter',
        'policy_brief' => 'Policy Brief',
    ];

    /**
     * Get the user who uploaded this document.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the embeddings for this document.
     */
    public function embeddings(): HasMany
    {
        return $this->hasMany(MemberDocumentEmbedding::class);
    }

    /**
     * Scope for indexed documents.
     */
    public function scopeIndexed($query)
    {
        return $query->where('indexed', true);
    }

    /**
     * Scope for documents not yet indexed.
     */
    public function scopeNeedsIndexing($query)
    {
        return $query->where('indexed', false)
            ->whereNotNull('content')
            ->where('content', '!=', '');
    }

    /**
     * Scope by document type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope for public documents.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get the type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get the type icon.
     */
    public function getTypeIconAttribute(): string
    {
        return match ($this->document_type) {
            'biography' => '📋',
            'position_paper' => '📝',
            'speech' => '🎤',
            'interview' => '🎙️',
            'campaign_material' => '🗳️',
            'awards' => '🏆',
            'personal_history' => '👤',
            'district_connection' => '🏠',
            'voting_explanation' => '🗳️',
            'constituent_letter' => '✉️',
            'policy_brief' => '📊',
            default => '📄',
        };
    }

    /**
     * Check if the document has file.
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    /**
     * Get storage path for file.
     */
    public function getFileFullPathAttribute(): ?string
    {
        if (!$this->file_path)
            return null;
        return storage_path('app/' . $this->file_path);
    }
}
