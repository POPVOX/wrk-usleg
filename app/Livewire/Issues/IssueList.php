<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Issues')]
class IssueList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public string $filterScope = '';
    public string $filterLead = '';
    public string $filterPriority = '';
    public string $sortBy = 'date'; // 'date', 'alpha', 'lead', 'status', 'priority'
    public string $viewMode = 'grid'; // 'grid', 'list', 'tree', 'timeline'

    // Hierarchy filter: 'roots' (parent issues only), 'all' (flat list)
    public string $hierarchyFilter = 'roots';

    // Track expanded issues for grid/list views
    public array $expanded = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function setViewMode(string $mode)
    {
        $this->viewMode = $mode;
    }

    public function setSortBy(string $sort)
    {
        $this->sortBy = $sort;
    }

    public function toggleExpand(int $issueId): void
    {
        if (in_array($issueId, $this->expanded)) {
            $this->expanded = array_values(array_diff($this->expanded, [$issueId]));
        } else {
            $this->expanded[] = $issueId;
        }
    }

    public function expandAll(): void
    {
        $this->expanded = Issue::roots()->pluck('id')->toArray();
    }

    public function collapseAll(): void
    {
        $this->expanded = [];
    }

    public function updateIssueStatus(int $issueId, string $status)
    {
        $issue = Issue::find($issueId);
        if ($issue && array_key_exists($status, Issue::STATUSES)) {
            $issue->update(['status' => $status]);
            $this->dispatch('notify', type: 'success', message: 'Status updated!');
        }
    }

    public function render()
    {
        $query = Issue::query()
            ->with(['parent', 'children'])
            ->withCount(['meetings', 'decisions', 'milestones', 'questions', 'children'])
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterScope, function ($q) {
                $q->where('scope', $this->filterScope);
            })
            ->when($this->filterLead, function ($q) {
                $q->where('lead', $this->filterLead);
            })
            ->when($this->filterPriority, function ($q) {
                $q->where('priority_level', $this->filterPriority);
            });

        // Apply hierarchy filter (except for tree view which always shows roots)
        if ($this->viewMode === 'tree' || $this->hierarchyFilter === 'roots') {
            $query->whereNull('parent_issue_id');
        }

        // Apply sorting
        switch ($this->sortBy) {
            case 'alpha':
                $query->orderBy('name', 'asc');
                break;
            case 'lead':
                $query->orderBy('lead', 'asc')->orderBy('start_date', 'asc');
                break;
            case 'status':
                $query->orderByRaw("CASE status 
                    WHEN 'planning' THEN 1 
                    WHEN 'active' THEN 2 
                    WHEN 'on_hold' THEN 3 
                    WHEN 'completed' THEN 4 
                    WHEN 'archived' THEN 5 
                    ELSE 6 END")->orderBy('start_date', 'asc');
                break;
            case 'priority':
                $query->orderByRaw("CASE priority_level 
                    WHEN 'Top Priority' THEN 1 
                    WHEN 'District Priority' THEN 2 
                    WHEN 'Tracking' THEN 3 
                    ELSE 4 END")->orderBy('start_date', 'asc');
                break;
            case 'date':
            default:
                $query->orderBy('sort_order')->orderBy('start_date', 'asc');
                break;
        }

        // Get unique leads for filter
        $leads = Issue::whereNotNull('lead')->distinct()->pluck('lead')->sort()->values();

        // For timeline view, group issues by month
        $timelineData = [];
        if ($this->viewMode === 'timeline') {
            $allIssues = $query->get();

            // Generate months from Jan 2026 to Dec 2026
            for ($month = 1; $month <= 12; $month++) {
                $monthDate = Carbon::create(2026, $month, 1);
                $monthKey = $monthDate->format('Y-m');
                $monthName = $monthDate->format('M Y');

                $monthIssues = $allIssues->filter(function ($issue) use ($monthDate) {
                    if (!$issue->start_date && !$issue->target_end_date) {
                        return false;
                    }

                    $start = $issue->start_date ?? $issue->target_end_date;
                    $end = $issue->target_end_date ?? $issue->start_date;

                    $monthStart = $monthDate->copy()->startOfMonth();
                    $monthEnd = $monthDate->copy()->endOfMonth();

                    // Issue overlaps with this month
                    return $start <= $monthEnd && $end >= $monthStart;
                });

                $timelineData[$monthKey] = [
                    'name' => $monthName,
                    'issues' => $monthIssues,
                ];
            }
        }

        // For tree view, load recursive children
        $treeIssues = collect();
        if ($this->viewMode === 'tree') {
            $treeIssues = $query->with('childrenRecursive')->get();
        }

        return view('livewire.issues.issue-list', [
            'issues' => in_array($this->viewMode, ['grid', 'list']) ? $query->paginate(12) : collect(),
            'treeIssues' => $treeIssues,
            'timelineData' => $timelineData,
            'statuses' => Issue::STATUSES,
            'priorityLevels' => Issue::PRIORITY_LEVELS,
            'scopes' => ['District' => 'District', 'National' => 'National'],
            'leads' => $leads,
        ]);
    }
}



