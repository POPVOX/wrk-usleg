<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Issue model (renamed from Project)
 * 
 * Represents a policy issue, legislative priority, or initiative
 * that the congressional office is tracking or working on.
 */
class Issue extends Model
{
    use HasFactory;

    protected $table = 'issues';

    protected $fillable = [
        'name',
        'scope',
        'lead',
        'description',
        'status',
        'is_initiative',
        'issue_path',
        'success_metrics',
        'goals',
        'url',
        'tags',
        'start_date',
        'target_end_date',
        'actual_end_date',
        'created_by',
        'ai_status_summary',
        'ai_status_generated_at',
        'parent_issue_id',
        'issue_type',
        'sort_order',
        // New congressional fields
        'committee_relevance',
        'legislative_vehicle',
        'priority_level',
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_end_date' => 'date',
        'actual_end_date' => 'date',
        'ai_status_generated_at' => 'datetime',
        'tags' => 'array',
        'is_initiative' => 'boolean',
        'success_metrics' => 'array',
    ];

    public const STATUSES = [
        'planning' => 'Planning',
        'active' => 'Active',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'archived' => 'Archived',
    ];

    public const PRIORITY_LEVELS = [
        'Tracking' => 'Tracking',
        'District Priority' => 'District Priority',
        'Top Priority' => 'Top Priority',
    ];

    public const PRIMARY_PRIORITY_LEVELS = [
        'Top Priority',
        'Member Priority',
    ];

    public const SECONDARY_PRIORITY_LEVELS = [
        'District Priority',
        'Office Priority',
    ];

    // Relationships
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(Meeting::class, 'issue_meeting')
            ->withPivot('relevance_note')
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'issue_organization')
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'issue_person')
            ->withPivot(['role', 'notes'])
            ->withTimestamps();
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'issue_topic');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(IssueDecision::class)->orderBy('decision_date', 'desc');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(IssueMilestone::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(IssueQuestion::class);
    }

    public function openQuestions(): HasMany
    {
        return $this->hasMany(IssueQuestion::class)->where('status', 'open');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Staff (team members working on this issue)
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'issue_staff')
            ->withPivot('role', 'added_at')
            ->orderByPivot('added_at', 'desc');
    }

    // Documents (files and links)
    public function documents(): HasMany
    {
        return $this->hasMany(IssueDocument::class)->orderBy('created_at', 'desc');
    }

    // Notes (activity log)
    public function notes(): HasMany
    {
        return $this->hasMany(IssueNote::class)->orderBy('created_at', 'desc');
    }

    // Pinned notes
    public function pinnedNotes(): HasMany
    {
        return $this->hasMany(IssueNote::class)->where('is_pinned', true)->orderBy('created_at', 'desc');
    }

    // Workspace relationships (for initiatives)
    public function workstreams(): HasMany
    {
        return $this->hasMany(IssueWorkstream::class)->orderBy('sort_order');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(IssuePublication::class)->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(IssueEvent::class)->orderBy('event_date');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(IssueChatMessage::class)->orderBy('created_at');
    }

    // Parent/Child relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'parent_issue_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Issue::class, 'parent_issue_id')->orderBy('sort_order');
    }

    // Recursive children for tree building
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function ancestors(): array
    {
        $ancestors = [];
        $issue = $this->parent;
        while ($issue) {
            array_unshift($ancestors, $issue);
            $issue = $issue->parent;
        }
        return $ancestors;
    }

    public function rootAncestor(): ?Issue
    {
        $ancestors = $this->ancestors();
        return $ancestors[0] ?? null;
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_issue_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    // Get depth in hierarchy
    public function getDepthAttribute(): int
    {
        return count($this->ancestors());
    }

    // Get breadcrumb path
    public function getBreadcrumbAttribute(): array
    {
        $path = $this->ancestors();
        $path[] = $this;
        return $path;
    }

    // Scope: only root issues (no parent)
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_issue_id');
    }

    // Get icon based on issue type
    public function getTypeIconAttribute(): string
    {
        return match ($this->issue_type) {
            'publication' => 'document-text',
            'event' => 'calendar',
            'chapter' => 'bookmark',
            'newsletter' => 'newspaper',
            'tool' => 'wrench-screwdriver',
            'research' => 'academic-cap',
            'outreach' => 'megaphone',
            'component' => 'puzzle-piece',
            'legislation' => 'scale',
            'constituent' => 'user-group',
            default => 'folder',
        };
    }

    // Get color based on issue type
    public function getTypeColorAttribute(): string
    {
        return match ($this->issue_type) {
            'publication' => 'text-blue-500',
            'event' => 'text-purple-500',
            'chapter' => 'text-indigo-500',
            'newsletter' => 'text-green-500',
            'tool' => 'text-orange-500',
            'research' => 'text-cyan-500',
            'outreach' => 'text-pink-500',
            'component' => 'text-gray-500',
            'legislation' => 'text-red-500',
            'constituent' => 'text-amber-500',
            default => 'text-gray-400',
        };
    }

    // Get color based on priority level
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority_level) {
            'Member Priority',
            'Top Priority' => 'bg-red-100 text-red-800',
            'Office Priority',
            'District Priority' => 'bg-amber-100 text-amber-800',
            'Tracking' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeActiveOrPlanning($query)
    {
        return $query->whereIn('status', ['active', 'planning']);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('status', '!=', 'completed');
    }

    public function scopeDashboardPriority($query)
    {
        return $query->whereIn('priority_level', array_merge(
            self::PRIMARY_PRIORITY_LEVELS,
            self::SECONDARY_PRIORITY_LEVELS,
        ));
    }

    public function scopeMemberFocusedPriority($query)
    {
        return $query->whereIn('priority_level', self::PRIMARY_PRIORITY_LEVELS);
    }

    public function scopeOrderByDashboardPriority($query)
    {
        return $query->orderByRaw("CASE
            WHEN priority_level IN ('Top Priority', 'Member Priority') THEN 1
            WHEN priority_level IN ('District Priority', 'Office Priority') THEN 2
            WHEN priority_level = 'Tracking' THEN 3
            WHEN priority_level = 'High' THEN 4
            WHEN priority_level = 'Medium' THEN 5
            ELSE 6 END");
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function isPrimaryPriority(): bool
    {
        return in_array($this->priority_level, self::PRIMARY_PRIORITY_LEVELS, true);
    }

    public function isSecondaryPriority(): bool
    {
        return in_array($this->priority_level, self::SECONDARY_PRIORITY_LEVELS, true);
    }

    // AI Status Methods
    public function needsStatusRefresh(): bool
    {
        // Refresh if: never generated
        if (!$this->ai_status_generated_at) {
            return true;
        }

        // Older than 24 hours
        if ($this->ai_status_generated_at->diffInHours(now()) > 24) {
            return true;
        }

        // Issue updated since last generation
        if ($this->updated_at > $this->ai_status_generated_at) {
            return true;
        }

        // Check if any related items updated since last generation
        $latestMeeting = $this->meetings()->max('updated_at');
        $latestDecision = $this->decisions()->max('updated_at');
        $latestMilestone = $this->milestones()->max('updated_at');
        $latestQuestion = $this->questions()->max('updated_at');

        $latestActivity = collect([$latestMeeting, $latestDecision, $latestMilestone, $latestQuestion])
            ->filter()
            ->max();

        return $latestActivity && $latestActivity > $this->ai_status_generated_at;
    }

    // Accessors
    public function getOpenQuestionsCountAttribute(): int
    {
        return $this->questions()->where('status', 'open')->count();
    }

    public function getPendingMilestonesCountAttribute(): int
    {
        return $this->milestones()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function getCompletedMilestonesCountAttribute(): int
    {
        return $this->milestones()->where('status', 'completed')->count();
    }
}


