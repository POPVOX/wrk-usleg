<?php

namespace App\Livewire\MemberHub;

use App\Models\Action;
use App\Models\AiInsight;
use App\Models\ConstituentFeedback;
use App\Models\Issue;
use App\Models\Meeting;
use App\Models\MemberLocation;
use App\Models\MemberStatement;
use App\Models\PositionEvolution;
use App\Models\PressClip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Member Hub')]
class MemberHub extends Component
{
    public function getMemberConfigProperty(): array
    {
        return [
            'name' => config('office.member_name', 'Member Name'),
            'first_name' => config('office.member_first_name', ''),
            'last_name' => config('office.member_last_name', ''),
            'title' => config('office.member_title', 'Representative'),
            'party' => config('office.member_party', ''),
            'state' => config('office.member_state', ''),
            'district' => config('office.member_district', ''),
            'bioguide_id' => config('office.member_bioguide_id', ''),
            'photo_url' => config('office.member_photo_url'),
            'level' => config('office.government_level', 'federal'),
            'chamber' => config('office.chamber', 'House'),
            'first_elected' => config('office.first_elected'),
            'official_website' => config('office.official_website', ''),
            'social_media' => config('office.social_media', []),
        ];
    }

    public function getDistrictConfigProperty(): array
    {
        return [
            'state' => config('office.member_state', ''),
            'district' => config('office.member_district', ''),
            'cities' => config('office.district_cities', []),
            'counties' => config('office.district_counties', []),
        ];
    }

    public function getCommitteesProperty(): array
    {
        return config('office.committees', []);
    }

    public function getOfficeConfigProperty(): array
    {
        return [
            'dc_office' => config('office.dc_office', []),
            'district_offices' => config('office.district_offices', []),
        ];
    }

    public function getMemberLocationProperty(): ?MemberLocation
    {
        return MemberLocation::getCurrent();
    }

    public function getAlertsProperty()
    {
        $alerts = collect();

        // Upcoming meetings requiring member in next 24 hours
        $urgentMeetings = Meeting::where('requires_member', true)
            ->where('meeting_date', '>=', now())
            ->where('meeting_date', '<=', now()->addDay())
            ->orderBy('meeting_date')
            ->get()
            ->map(fn($m) => [
                'type' => $m->meeting_date->diffInHours(now()) < 2 ? 'urgent' : 'warning',
                'icon' => '🔔',
                'message' => $m->title . ' in ' . $m->meeting_date->diffForHumans(),
                'url' => route('meetings.show', $m),
            ]);

        // Negative press clips in last 48 hours
        $negativeClips = PressClip::where('sentiment', 'Negative')
            ->where('created_at', '>=', now()->subDays(2))
            ->get()
            ->map(fn($c) => [
                'type' => 'warning',
                'icon' => '📰',
                'message' => "Negative coverage: {$c->title}",
                'url' => $c->url,
            ]);

        // Overdue actions
        $overdueActions = Action::where('status', 'pending')
            ->where('due_date', '<', now())
            ->count();

        if ($overdueActions > 0) {
            $alerts->push([
                'type' => 'warning',
                'icon' => '⚠️',
                'message' => "{$overdueActions} overdue action items",
                'url' => route('meetings.index'),
            ]);
        }

        return $alerts
            ->merge($urgentMeetings)
            ->merge($negativeClips)
            ->sortBy(fn($a) => $a['type'] === 'urgent' ? 0 : 1)
            ->take(5);
    }

    public function getAiSuggestionsProperty()
    {
        if (!config('office.features.ai_insights')) {
            return collect();
        }

        return AiInsight::getActive(3);
    }

    public function getTodayScheduleProperty()
    {
        return Meeting::where('requires_member', true)
            ->whereDate('meeting_date', today())
            ->orderBy('meeting_date')
            ->get();
    }

    public function getUpcomingScheduleProperty()
    {
        return Meeting::where('requires_member', true)
            ->where('meeting_date', '>=', now())
            ->where('meeting_date', '<=', now()->addDays(3))
            ->orderBy('meeting_date')
            ->take(10)
            ->get();
    }

    public function getLegislativeStatsProperty(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'active_issues' => Issue::whereIn('status', ['active', 'planning'])->count(),
            'meetings_this_month' => Meeting::where('meeting_date', '>=', $thirtyDaysAgo)->count(),
            'pending_actions' => Action::where('status', 'pending')->count(),
            'priority_issues' => Issue::where('priority_level', 'Member Priority')->count(),
        ];
    }

    public function getPriorityIssuesProperty()
    {
        return Issue::whereIn('priority_level', ['Member Priority', 'Office Priority'])
            ->whereIn('status', ['active', 'planning'])
            ->orderByRaw("CASE 
                WHEN priority_level = 'Member Priority' THEN 1 
                WHEN priority_level = 'Office Priority' THEN 2 
                ELSE 3 END")
            ->take(5)
            ->get();
    }

    public function getRecentStatementsProperty()
    {
        return MemberStatement::getRecent(7, 5);
    }

    public function getMediaCoverageStatsProperty(): array
    {
        $clips = PressClip::where('created_at', '>=', now()->subDays(7))->get();

        return [
            'total' => $clips->count(),
            'positive' => $clips->where('sentiment', 'Positive')->count(),
            'neutral' => $clips->where('sentiment', 'Neutral')->count(),
            'negative' => $clips->where('sentiment', 'Negative')->count(),
        ];
    }

    public function getRecentClipsProperty()
    {
        return PressClip::orderByDesc('created_at')
            ->take(5)
            ->get();
    }

    public function getConstituentStatsProperty(): array
    {
        return ConstituentFeedback::getStats(30);
    }

    public function getTopConstituentIssuesProperty()
    {
        return ConstituentFeedback::getTopIssues(30, 5);
    }

    public function getTopPolicyPositionsProperty()
    {
        return PositionEvolution::getTopIssues(3);
    }

    public function getUpcomingDistrictEventsProperty()
    {
        return Meeting::where('meeting_type', 'district_event')
            ->where('meeting_date', '>=', now())
            ->orderBy('meeting_date')
            ->take(5)
            ->get();
    }

    public function dismissInsight(int $id): void
    {
        $insight = AiInsight::find($id);
        if ($insight) {
            $insight->dismiss(Auth::id());
            $this->dispatch('notify', type: 'success', message: 'Suggestion dismissed');
        }
    }

    public function render()
    {
        return view('livewire.member-hub.member-hub', [
            'memberConfig' => $this->memberConfig,
            'districtConfig' => $this->districtConfig,
            'committees' => $this->committees,
            'officeConfig' => $this->officeConfig,
            'memberLocation' => $this->memberLocation,
            'alerts' => $this->alerts,
            'aiSuggestions' => $this->aiSuggestions,
            'todaySchedule' => $this->todaySchedule,
            'upcomingSchedule' => $this->upcomingSchedule,
            'legislativeStats' => $this->legislativeStats,
            'priorityIssues' => $this->priorityIssues,
            'recentStatements' => $this->recentStatements,
            'mediaCoverageStats' => $this->mediaCoverageStats,
            'recentClips' => $this->recentClips,
            'constituentStats' => $this->constituentStats,
            'topConstituentIssues' => $this->topConstituentIssues,
            'topPolicyPositions' => $this->topPolicyPositions,
            'upcomingDistrictEvents' => $this->upcomingDistrictEvents,
        ]);
    }
}


