<?php

namespace App\Livewire\Dashboards;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Meeting;
use App\Models\Issue;
use App\Models\Commitment;
use App\Models\MemberLocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Personal Dashboard Component
 * 
 * Individual staffer sees ONLY their own work - their meetings, their issues, their tasks.
 */
#[Layout('layouts.app')]
class PersonalDashboard extends Component
{
    /**
     * Get meetings where the current user is the owner or attendee.
     */
    public function getMyUpcomingMeetingsProperty()
    {
        $userId = Auth::id();
        $userTimezone = Auth::user()->timezone ?? 'America/New_York';
        $now = Carbon::now($userTimezone);

        return Meeting::where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('teamMembers', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        })
            ->where('meeting_date', '>=', $now->startOfDay())
            ->where('meeting_date', '<=', $now->copy()->addDays(7)->endOfDay())
            ->with(['organizations', 'people'])
            ->orderBy('meeting_date')
            ->take(10)
            ->get();
    }

    /**
     * Get issues where the current user is assigned as staff.
     */
    public function getMyAssignedIssuesProperty()
    {
        $userId = Auth::id();

        return Issue::whereHas('staff', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('status', '!=', 'Completed')
            ->orderByRaw("CASE 
            WHEN priority_level = 'Member Priority' THEN 1 
            WHEN priority_level = 'Office Priority' THEN 2 
            WHEN priority_level = 'High' THEN 3
            WHEN priority_level = 'Medium' THEN 4
            ELSE 5 END")
            ->take(8)
            ->get();
    }

    /**
     * Get action items/commitments assigned to the current user.
     */
    public function getMyActionItemsProperty()
    {
        $userId = Auth::id();

        return Commitment::where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->with(['meeting', 'assignedTo'])
            ->orderByRaw("CASE WHEN due_date < ? THEN 0 ELSE 1 END", [Carbon::now()])
            ->orderBy('due_date')
            ->take(10)
            ->get();
    }

    /**
     * Get the Member's schedule for the next 3 days.
     * This helps staff coordinate around Member's availability.
     */
    public function getMemberScheduleProperty()
    {
        // For now, return meetings flagged as requiring the member
        // or high-priority meetings in the next 3 days
        $userTimezone = Auth::user()->timezone ?? 'America/New_York';
        $now = Carbon::now($userTimezone);

        return Meeting::where('meeting_date', '>=', $now->startOfDay())
            ->where('meeting_date', '<=', $now->copy()->addDays(2)->endOfDay())
            ->where(function ($query) {
                // Meetings that might involve the Member
                $query->whereHas('issues', function ($q) {
                    $q->where('priority_level', 'Member Priority');
                })
                    ->orWhere('title', 'like', '%Member%')
                    ->orWhere('title', 'like', '%Boss%');
            })
            ->with(['organizations'])
            ->orderBy('meeting_date')
            ->take(5)
            ->get();
    }

    /**
     * Get recent activity by this user.
     */
    public function getMyRecentActivityProperty()
    {
        $userId = Auth::id();

        // Get recently updated meetings by this user
        $recentMeetings = Meeting::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($meeting) {
                return [
                    'type' => 'meeting',
                    'icon' => '📅',
                    'description' => "Updated meeting: {$meeting->title}",
                    'timestamp' => $meeting->updated_at,
                    'url' => route('meetings.show', $meeting)
                ];
            });

        // Get recently updated issues this user is assigned to
        $recentIssues = Issue::whereHas('staff', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($issue) {
                return [
                    'type' => 'issue',
                    'icon' => '📁',
                    'description' => "Updated issue: {$issue->name}",
                    'timestamp' => $issue->updated_at,
                    'url' => route('issues.show', $issue)
                ];
            });

        return $recentMeetings->merge($recentIssues)
            ->sortByDesc('timestamp')
            ->take(10)
            ->values();
    }

    /**
     * Get counts for overdue items.
     */
    public function getOverdueCountProperty()
    {
        $userId = Auth::id();

        return Commitment::where('assigned_to', $userId)
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', Carbon::now())
            ->count();
    }

    /**
     * Get meetings that need notes.
     */
    public function getMeetingsNeedingNotesProperty()
    {
        $userId = Auth::id();
        $userTimezone = Auth::user()->timezone ?? 'America/New_York';
        $now = Carbon::now($userTimezone);

        return Meeting::where('user_id', $userId)
            ->where('meeting_date', '<', $now->startOfDay())
            ->where(function ($query) {
                $query->whereNull('raw_notes')
                    ->orWhere('raw_notes', '');
            })
            ->orderBy('meeting_date', 'desc')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboards.personal-dashboard', [
            'upcomingMeetings' => $this->myUpcomingMeetings,
            'assignedIssues' => $this->myAssignedIssues,
            'actionItems' => $this->myActionItems,
            'memberSchedule' => $this->memberSchedule,
            'recentActivity' => $this->myRecentActivity,
            'overdueCount' => $this->overdueCount,
            'meetingsNeedingNotes' => $this->meetingsNeedingNotes,
        ]);
    }
}
