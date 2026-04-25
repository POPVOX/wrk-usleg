<?php

namespace App\Livewire\MemberHub;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Bill;
use App\Models\Vote;
use App\Models\MemberStatement;
use App\Models\MemberLocation;
use App\Models\PressClip;
use App\Models\Meeting;
use Carbon\Carbon;

/**
 * Member Dashboard (Command Center)
 * 
 * Real-time view of Member's location, schedule, legislative activity,
 * public communications, and constituent engagement.
 */
#[Layout('layouts.app')]
class MemberDashboard extends Component
{
    /**
     * Get Member's current location.
     */
    public function getCurrentLocationProperty()
    {
        return MemberLocation::getCurrentLocation();
    }

    /**
     * Get recent travel history (last 7 days).
     */
    public function getRecentTravelProperty()
    {
        return MemberLocation::where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get today's schedule (meetings requiring Member).
     */
    public function getTodayScheduleProperty()
    {
        return Meeting::whereDate('meeting_date', Carbon::today())
            ->with(['organizations', 'people'])
            ->orderBy('meeting_date')
            ->get();
    }

    /**
     * Get upcoming schedule (next 7 days).
     */
    public function getUpcomingScheduleProperty()
    {
        return Meeting::where('meeting_date', '>=', Carbon::now())
            ->where('meeting_date', '<=', Carbon::now()->addDays(7))
            ->with(['organizations'])
            ->orderBy('meeting_date')
            ->take(10)
            ->get();
    }

    /**
     * Get legislative activity stats.
     */
    public function getLegislativeStatsProperty()
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return [
            'bills_sponsored' => Bill::sponsored()->where('introduced_date', '>=', $thirtyDaysAgo)->count(),
            'bills_cosponsored' => Bill::cosponsored()->where('introduced_date', '>=', $thirtyDaysAgo)->count(),
            'votes_cast' => Vote::where('vote_date', '>=', $thirtyDaysAgo)->count(),
            'total_sponsored' => Bill::sponsored()->currentCongress()->count(),
            'total_cosponsored' => Bill::cosponsored()->currentCongress()->count(),
        ];
    }

    /**
     * Get recent bills.
     */
    public function getRecentBillsProperty()
    {
        return Bill::orderBy('introduced_date', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get recent votes.
     */
    public function getRecentVotesProperty()
    {
        return Vote::orderBy('vote_date', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get public communications stats.
     */
    public function getCommunicationsStatsProperty()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);

        return [
            'statements' => MemberStatement::where('published_date', '>=', $sevenDaysAgo)->count(),
            'press_releases' => MemberStatement::pressReleases()->where('published_date', '>=', $sevenDaysAgo)->count(),
            'speeches' => MemberStatement::speeches()->where('published_date', '>=', $sevenDaysAgo)->count(),
        ];
    }

    /**
     * Get recent statements.
     */
    public function getRecentStatementsProperty()
    {
        return MemberStatement::orderBy('published_date', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get media coverage stats.
     */
    public function getMediaStatsProperty()
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        $clips = PressClip::where('publish_date', '>=', $sevenDaysAgo)->get();

        return [
            'total' => $clips->count(),
            'positive' => $clips->where('sentiment', 'positive')->count(),
            'neutral' => $clips->where('sentiment', 'neutral')->count(),
            'negative' => $clips->where('sentiment', 'negative')->count(),
        ];
    }

    /**
     * Get recent press clips.
     */
    public function getRecentClipsProperty()
    {
        return PressClip::with(['outlet'])
            ->orderBy('publish_date', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get constituent engagement stats.
     */
    public function getConstituentStatsProperty()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        // Count meetings with constituent-related topics or organizations
        $constituentMeetings = Meeting::where('meeting_date', '>=', $thisMonth)
            ->whereHas('organizations', function ($q) {
                $q->whereIn('type', ['Constituent', 'Community', 'Local Business', 'Nonprofit']);
            })
            ->count();

        return [
            'constituent_meetings' => $constituentMeetings,
            'district_events' => Meeting::where('meeting_date', '>=', $thisMonth)
                ->where('location', 'like', '%District%')
                ->count(),
        ];
    }

    /**
     * Get key metrics for this Congress.
     */
    public function getKeyMetricsProperty()
    {
        $congressStart = Carbon::create(2025, 1, 3); // 119th Congress start

        return [
            'bills_introduced' => Bill::sponsored()->currentCongress()->count(),
            'voting_participation' => $this->calculateVotingParticipation(),
            'bipartisan_bills' => 0, // Would need cosponsors data analysis
        ];
    }

    /**
     * Calculate voting participation rate.
     */
    protected function calculateVotingParticipation(): float
    {
        $totalVotes = Vote::currentCongress()->count();
        $missedVotes = Vote::currentCongress()->where('vote_cast', 'NOT VOTING')->count();

        if ($totalVotes === 0)
            return 100.0;

        return round((($totalVotes - $missedVotes) / $totalVotes) * 100, 1);
    }

    /**
     * Get Member info from config.
     */
    public function getMemberInfoProperty()
    {
        return [
            'name' => config('office.member_name'),
            'party' => config('office.member_party'),
            'state' => config('office.member_state'),
            'district' => config('office.member_district'),
            'photo_url' => config('office.member_photo_url'),
            'title' => config('office.member_title', 'Representative'),
        ];
    }

    /**
     * Get committee assignments from config.
     */
    public function getCommitteesProperty()
    {
        return config('office.committees', []);
    }

    /**
     * Get social media accounts from config.
     */
    public function getSocialMediaProperty()
    {
        $social = config('office.social_media', []);
        return array_filter([
            'twitter' => [
                'url' => $social['twitter'] ?? null,
                'handle' => $this->extractHandle($social['twitter'] ?? '', 'twitter'),
                'icon' => 'x',
                'label' => 'X (Twitter)',
                'color' => 'text-gray-800 dark:text-gray-200',
            ],
            'facebook' => [
                'url' => $social['facebook'] ?? null,
                'handle' => $this->extractHandle($social['facebook'] ?? '', 'facebook'),
                'icon' => 'facebook',
                'label' => 'Facebook',
                'color' => 'text-blue-600',
            ],
            'instagram' => [
                'url' => $social['instagram'] ?? null,
                'handle' => $this->extractHandle($social['instagram'] ?? '', 'instagram'),
                'icon' => 'instagram',
                'label' => 'Instagram',
                'color' => 'text-pink-600',
            ],
            'youtube' => [
                'url' => $social['youtube'] ?? null,
                'handle' => $this->extractHandle($social['youtube'] ?? '', 'youtube'),
                'icon' => 'youtube',
                'label' => 'YouTube',
                'color' => 'text-red-600',
            ],
        ], fn($item) => !empty($item['url']));
    }

    /**
     * Extract social media handle from URL.
     */
    protected function extractHandle(string $url, string $platform): string
    {
        if (empty($url))
            return '';

        $path = parse_url($url, PHP_URL_PATH);
        $handle = trim($path ?? '', '/');

        // Remove trailing hashtags or query params
        $handle = explode('#', $handle)[0];
        $handle = explode('?', $handle)[0];

        return '@' . ltrim($handle, '@');
    }

    /**
     * Get news articles from configured sources (placeholder for API integration).
     */
    public function getNewsArticlesProperty()
    {
        // For now, return recent press clips as proxy for news
        // In future, could integrate with news APIs
        $sources = config('office.news_sources', []);

        return [
            'sources' => $sources,
            'articles' => PressClip::with('outlet')
                ->orderBy('publish_date', 'desc')
                ->take(10)
                ->get(),
        ];
    }

    public function render()
    {
        return view('livewire.member-hub.member-dashboard', [
            'memberInfo' => $this->memberInfo,
            'currentLocation' => $this->currentLocation,
            'recentTravel' => $this->recentTravel,
            'todaySchedule' => $this->todaySchedule,
            'upcomingSchedule' => $this->upcomingSchedule,
            'legislativeStats' => $this->legislativeStats,
            'recentBills' => $this->recentBills,
            'recentVotes' => $this->recentVotes,
            'communicationsStats' => $this->communicationsStats,
            'recentStatements' => $this->recentStatements,
            'mediaStats' => $this->mediaStats,
            'recentClips' => $this->recentClips,
            'constituentStats' => $this->constituentStats,
            'keyMetrics' => $this->keyMetrics,
            'committees' => $this->committees,
            'socialMedia' => $this->socialMedia,
            'newsArticles' => $this->newsArticles,
        ]);
    }
}
