<?php

namespace App\Livewire\Management;

use App\Models\Issue;
use App\Models\Meeting;
use App\Models\Person;
use App\Models\PressClip;
use App\Models\IssueDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Team Overview')]
class TeamOverview extends Component
{
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public string $filterRole = '';
    public string $filterLocation = '';
    
    public ?int $expandedMemberId = null;
    public bool $showAssignModal = false;
    public ?int $assignToUserId = null;
    public array $selectedIssueIds = [];

    public function mount(): void
    {
        // Check if user has management access
        if (!Auth::user()?->isManagement()) {
            abort(403, 'Unauthorized');
        }
    }

    public function getTeamMembersProperty()
    {
        $query = User::query()
            ->where('id', '!=', Auth::id()) // Exclude self, or include if desired
            ->withCount(['assignedIssues as issues_count' => function ($q) {
                $q->where('status', '!=', 'Completed');
            }]);

        // Apply filters
        if ($this->filterRole) {
            $query->where('title', $this->filterRole);
        }

        if ($this->filterLocation) {
            $query->where('office_location', $this->filterLocation);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'issues':
                $query->orderBy('issues_count', $this->sortDirection);
                break;
            case 'name':
            default:
                $query->orderBy('name', $this->sortDirection);
                break;
        }

        return $query->get();
    }

    public function getAvailableRolesProperty(): array
    {
        return User::distinct()
            ->whereNotNull('title')
            ->pluck('title')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getAvailableLocationsProperty(): array
    {
        return User::distinct()
            ->whereNotNull('office_location')
            ->pluck('office_location')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getUnassignedIssuesProperty()
    {
        return Issue::whereDoesntHave('staff')
            ->where('status', '!=', 'Completed')
            ->orderBy('priority_level')
            ->orderBy('name')
            ->get();
    }

    public function getAllActiveIssuesProperty()
    {
        return Issue::where('status', '!=', 'Completed')
            ->orderBy('priority_level')
            ->orderBy('name')
            ->get();
    }

    public function getActivityForUser(int $userId): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Get issue IDs for this user
        $issueIds = Issue::whereHas('staff', fn($q) => $q->where('user_id', $userId))
            ->pluck('id');

        return [
            'meetings' => Meeting::where('user_id', $userId)
                ->where('meeting_date', '>=', $thirtyDaysAgo)
                ->count(),
            'contacts' => Person::where('created_by', $userId)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
            'press_clips' => PressClip::where('created_at', '>=', $thirtyDaysAgo)
                ->whereHas('issues', fn($q) => $q->whereIn('issues.id', $issueIds))
                ->count(),
            'documents' => IssueDocument::whereIn('issue_id', $issueIds)
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->count(),
        ];
    }

    public function getIssuesForUser(int $userId)
    {
        return Issue::whereHas('staff', fn($q) => $q->where('user_id', $userId))
            ->where('status', '!=', 'Completed')
            ->orderByRaw("CASE 
                WHEN priority_level = 'Member Priority' THEN 1 
                WHEN priority_level = 'Office Priority' THEN 2 
                ELSE 3 END")
            ->get();
    }

    public function toggleExpand(int $userId): void
    {
        $this->expandedMemberId = $this->expandedMemberId === $userId ? null : $userId;
    }

    public function openAssignModal(int $userId): void
    {
        $this->assignToUserId = $userId;
        $this->selectedIssueIds = [];
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignToUserId = null;
        $this->selectedIssueIds = [];
    }

    public function assignIssues(): void
    {
        if (!$this->assignToUserId || empty($this->selectedIssueIds)) {
            return;
        }

        $user = User::find($this->assignToUserId);
        if (!$user) {
            return;
        }

        foreach ($this->selectedIssueIds as $issueId) {
            $issue = Issue::find($issueId);
            if ($issue) {
                // Sync user to issue's staff (won't duplicate)
                $issue->staff()->syncWithoutDetaching([$this->assignToUserId]);
            }
        }

        $this->dispatch('notify', type: 'success', message: count($this->selectedIssueIds) . ' issue(s) assigned to ' . $user->name);
        $this->closeAssignModal();
    }

    public function unassignIssue(int $userId, int $issueId): void
    {
        $issue = Issue::find($issueId);
        if ($issue) {
            $issue->staff()->detach($userId);
            $this->dispatch('notify', type: 'success', message: 'Issue unassigned');
        }
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        return view('livewire.management.team-overview', [
            'teamMembers' => $this->teamMembers,
            'availableRoles' => $this->availableRoles,
            'availableLocations' => $this->availableLocations,
            'unassignedIssues' => $this->unassignedIssues,
            'allActiveIssues' => $this->allActiveIssues,
        ]);
    }
}


