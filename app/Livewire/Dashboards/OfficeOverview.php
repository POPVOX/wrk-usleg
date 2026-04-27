<?php

namespace App\Livewire\Dashboards;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Meeting;
use App\Models\Issue;
use App\Models\Commitment;
use App\Models\PressClip;
use App\Models\Inquiry;
use App\Models\MemberLocation;
use App\Models\Organization;
use Carbon\Carbon;

/**
 * Office Overview Dashboard Component
 * 
 * Leadership sees a meta view of ALL office activity.
 * Used by Chief of Staff, Legislative Director, etc.
 */
#[Layout('layouts.app')]
class OfficeOverview extends Component
{
    /**
     * Get the current member location.
     */
    public function getMemberLocationProperty()
    {
        return MemberLocation::getCurrent();
    }

    /**
     * Get office-wide metrics.
     */
    public function getOfficeMetricsProperty()
    {
        return [
            'active_issues' => Issue::active()->count(),
            'meetings_this_week' => Meeting::whereBetween('meeting_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'pending_actions' => Commitment::where('status', 'open')->count(),
            'overdue_actions' => Commitment::where('status', 'open')
                ->where('due_date', '<', Carbon::now())->count(),
            'priority_issues' => Issue::dashboardPriority()
                ->incomplete()
                ->count(),
        ];
    }

    /**
     * Get the member's schedule for today + next 3 days.
     */
    public function getMemberScheduleProperty()
    {
        return Meeting::where('meeting_date', '>=', Carbon::now()->startOfDay())
            ->where('meeting_date', '<=', Carbon::now()->addDays(3)->endOfDay())
            ->with(['organizations', 'people', 'user'])
            ->orderBy('meeting_date')
            ->get()
            ->groupBy(function ($meeting) {
                return $meeting->meeting_date->format('Y-m-d');
            });
    }

    /**
     * Get priority issues.
     */
    public function getPriorityIssuesProperty()
    {
        return Issue::dashboardPriority()
            ->incomplete()
            ->with([
                'staff',
                'milestones' => function ($query) {
                    $query->where('completed', false)->orderBy('target_date');
                }
            ])
            ->orderByDashboardPriority()
            ->take(10)
            ->get();
    }

    /**
     * Get upcoming deadlines across all issues and commitments.
     */
    public function getUpcomingDeadlinesProperty()
    {
        $commitments = Commitment::where('status', 'open')
            ->where('due_date', '>=', Carbon::now())
            ->where('due_date', '<=', Carbon::now()->addDays(14))
            ->with(['meeting', 'assignedTo'])
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(function ($c) {
                return [
                    'type' => 'commitment',
                    'icon' => '✓',
                    'description' => $c->description,
                    'due_date' => $c->due_date,
                    'assigned_to' => $c->assignee?->name ?? 'Unassigned',
                    'meeting' => $c->meeting?->title,
                    'is_overdue' => $c->due_date->isPast(),
                ];
            });

        return $commitments->sortBy('due_date')->take(10);
    }

    /**
     * Get recent media attention.
     */
    public function getMediaAttentionProperty()
    {
        return PressClip::where('publish_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('publish_date', 'desc')
            ->with(['outlet', 'journalist'])
            ->take(5)
            ->get();
    }

    /**
     * Get pending media inquiries.
     */
    public function getPendingInquiriesProperty()
    {
        return Inquiry::where('status', '!=', 'responded')
            ->where('status', '!=', 'declined')
            ->orderByRaw("CASE 
                WHEN urgency = 'breaking' THEN 1 
                WHEN urgency = 'urgent' THEN 2 
                ELSE 3 END")
            ->orderBy('deadline')
            ->take(5)
            ->get();
    }

    /**
     * Get most active relationships (organizations with most meetings).
     */
    public function getActiveRelationshipsProperty()
    {
        return Organization::withCount([
            'meetings' => function ($query) {
                $query->where('meeting_date', '>=', Carbon::now()->subMonth());
            }
        ])
            ->whereHas('meetings', function ($query) {
                $query->where('meeting_date', '>=', Carbon::now()->subMonth());
            })
            ->orderBy('meetings_count', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get staff activity summary.
     */
    public function getStaffActivityProperty()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        return [
            'meetings_logged' => Meeting::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count(),
            'notes_added' => Meeting::whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                ->whereNotNull('raw_notes')
                ->where('raw_notes', '!=', '')
                ->count(),
            'issues_updated' => Issue::whereBetween('updated_at', [$startOfWeek, $endOfWeek])->count(),
            'actions_completed' => Commitment::where('status', 'completed')
                ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])->count(),
        ];
    }

    /**
     * Get meetings needing attention (no notes, past meetings).
     */
    public function getNeedingAttentionProperty()
    {
        return Meeting::where('meeting_date', '<', Carbon::now()->startOfDay())
            ->where('meeting_date', '>=', Carbon::now()->subDays(14))
            ->where(function ($query) {
                $query->whereNull('raw_notes')
                    ->orWhere('raw_notes', '');
            })
            ->with(['organizations', 'user'])
            ->orderBy('meeting_date', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.office-overview', [
            'memberLocation' => $this->memberLocation,
            'metrics' => $this->officeMetrics,
            'memberSchedule' => $this->memberSchedule,
            'priorityIssues' => $this->priorityIssues,
            'upcomingDeadlines' => $this->upcomingDeadlines,
            'recentClips' => $this->mediaAttention,
            'pendingInquiries' => $this->pendingInquiries,
            'activeRelationships' => $this->activeRelationships,
            'staffActivity' => $this->staffActivity,
            'needingAttention' => $this->needingAttention,
        ]);
    }
}
