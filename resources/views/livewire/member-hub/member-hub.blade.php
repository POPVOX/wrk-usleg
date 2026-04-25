<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ============================================== --}}
            {{-- HERO SECTION --}}
            {{-- ============================================== --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center gap-6">
                    {{-- Member Photo --}}
                    <div class="flex-shrink-0">
                        @if($memberConfig['photo_url'] ?? null)
                            <img src="{{ $memberConfig['photo_url'] }}" alt="{{ $memberConfig['name'] }}"
                                class="w-24 h-24 rounded-xl object-cover border-4 border-indigo-100 dark:border-indigo-900">
                        @else
                            <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold">
                                {{ substr($memberConfig['first_name'] ?? 'M', 0, 1) }}
                            </div>
                        @endif
                    </div>

                    {{-- Member Info --}}
                    <div class="flex-1">
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $memberConfig['title'] ?? 'Rep.' }} {{ $memberConfig['name'] ?? 'Member Name' }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-gray-600 dark:text-gray-400">
                            @php
                                $party = $memberConfig['party'] ?? '';
                                $partyClass = match(true) {
                                    str_starts_with($party, 'D'), $party === 'Democratic' => 'text-blue-600 dark:text-blue-400',
                                    str_starts_with($party, 'R'), $party === 'Republican' => 'text-red-600 dark:text-red-400',
                                    default => 'text-gray-600 dark:text-gray-400',
                                };
                                $partyShort = match(true) {
                                    str_starts_with($party, 'D'), $party === 'Democratic' => 'D',
                                    str_starts_with($party, 'R'), $party === 'Republican' => 'R',
                                    str_starts_with($party, 'I'), $party === 'Independent' => 'I',
                                    default => substr($party, 0, 1),
                                };
                                $partyLabel = match(true) {
                                    str_starts_with($party, 'D'), $party === 'Democratic' => 'Democrat',
                                    str_starts_with($party, 'R'), $party === 'Republican' => 'Republican',
                                    str_starts_with($party, 'I'), $party === 'Independent' => 'Independent',
                                    default => $party ?: 'N/A',
                                };
                                $level = $memberConfig['level'] ?? 'federal';
                                $districtLabel = $memberConfig['district'] ? '-' . $memberConfig['district'] : '';
                            @endphp
                            <span class="{{ $partyClass }} font-semibold">{{ $partyLabel }}-{{ $memberConfig['state'] ?? 'ST' }}{{ $districtLabel }}</span>
                            @if(!empty($districtConfig['cities']))
                            <span class="text-gray-300 dark:text-gray-600">•</span>
                            <span>📍 {{ implode(', ', array_slice($districtConfig['cities'] ?? [], 0, 3)) }}</span>
                            @endif
                        </div>
                        @if(count($committees) > 0)
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="text-gray-500 dark:text-gray-400">🏛️</span>
                                @foreach($committees as $committee)
                                    <span class="px-2 py-0.5 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 rounded text-sm font-medium">
                                        {{ $committee['name'] ?? $committee }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @if($memberConfig['bio_short'] ?? null)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                {{ $memberConfig['bio_short'] }}
                            </p>
                        @endif
                    </div>

                    {{-- Quick Actions --}}
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('knowledge.hub') }}" wire:navigate
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                            Ask About Member
                        </a>
                        <a href="{{ route('issues.index') }}" wire:navigate
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            View Issues
                        </a>
                    </div>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- ALERTS & AI SUGGESTIONS ROW --}}
            {{-- ============================================== --}}
            @if($alerts->isNotEmpty() || $aiSuggestions->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Alerts --}}
                @if($alerts->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                        <span class="text-lg">🔔</span> Alerts & Updates
                    </h3>
                    <div class="space-y-3">
                        @foreach($alerts as $alert)
                            <a href="{{ $alert['url'] ?? '#' }}"
                                class="flex items-center gap-3 p-3 rounded-lg {{ $alert['type'] === 'urgent' ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' }} hover:opacity-80 transition">
                                <span class="text-xl">{{ $alert['icon'] }}</span>
                                <span class="text-sm {{ $alert['type'] === 'urgent' ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300' }}">
                                    {{ $alert['message'] }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- AI Suggestions --}}
                @if($aiSuggestions->isNotEmpty())
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800 p-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                        <span class="text-lg">💡</span> AI Suggestions
                    </h3>
                    <div class="space-y-3">
                        @foreach($aiSuggestions as $suggestion)
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-indigo-100 dark:border-indigo-800">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $suggestion->title }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $suggestion->description }}</p>
                                    </div>
                                    <button wire:click="dismissInsight({{ $suggestion->id }})"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- ============================================== --}}
            {{-- LOCATION & SCHEDULE ROW --}}
            {{-- ============================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Member Location Widget --}}
                <div class="lg:col-span-1">
                    @livewire('member-hub.member-location-widget')
                </div>

                {{-- Today's Schedule --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 h-full">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="text-lg">🗓️</span> Today's Schedule
                            </h3>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</span>
                        </div>

                        @if($todaySchedule->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($todaySchedule as $meeting)
                                    <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                                        class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition group">
                                        <div class="flex-shrink-0 text-right w-20">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $meeting->meeting_date?->format('g:i A') ?? 'TBD' }}
                                            </p>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 truncate">
                                                {{ $meeting->title }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $meeting->location ?? 'Location TBD' }}
                                            </p>
                                        </div>
                                        @if($meeting->talking_points_prepared)
                                            <span class="text-green-600 dark:text-green-400">✅</span>
                                        @else
                                            <span class="text-amber-600 dark:text-amber-400">⏳</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <span class="text-4xl">📅</span>
                                <p class="text-gray-500 dark:text-gray-400 mt-2">No Member events scheduled today</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- STATS & PRIORITY ISSUES ROW --}}
            {{-- ============================================== --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Active Issues</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $legislativeStats['active_issues'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Meetings This Month</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $legislativeStats['meetings_this_month'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pending Actions</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $legislativeStats['pending_actions'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Member Priorities</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $legislativeStats['priority_issues'] }}</p>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- PRIORITY ISSUES & CONSTITUENT FEEDBACK --}}
            {{-- ============================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Priority Issues --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-lg">🎯</span> Priority Issues
                        </h3>
                        <a href="{{ route('issues.index') }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            View All →
                        </a>
                    </div>

                    @if($priorityIssues->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($priorityIssues as $issue)
                                <a href="{{ route('issues.show', $issue) }}" wire:navigate
                                    class="flex items-center justify-between p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-700 transition group">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 truncate">
                                            {{ $issue->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ $issue->status }} • {{ $issue->priority_level }}
                                        </p>
                                    </div>
                                    @if($issue->priority_level === 'Member Priority')
                                        <span class="text-lg">🔥</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-6">No priority issues set</p>
                    @endif
                </div>

                {{-- Constituent Feedback --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-lg">👥</span> Constituent Engagement (30 days)
                        </h3>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $constituentStats['total_count'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Contacts</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600">{{ $constituentStats['by_sentiment']['positive'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Positive</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-600">{{ $constituentStats['by_sentiment']['negative'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Negative</p>
                        </div>
                    </div>

                    @if($topConstituentIssues->isNotEmpty())
                        <h4 class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2 uppercase">Top Issues</h4>
                        <div class="space-y-2">
                            @foreach($topConstituentIssues as $feedback)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feedback->issue }}</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $feedback->total_count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- COMMUNICATIONS & MEDIA ROW --}}
            {{-- ============================================== --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Recent Statements --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-lg">📢</span> Public Communications (7 days)
                        </h3>
                    </div>

                    @if($recentStatements->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($recentStatements as $statement)
                                <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                            {{ $statement->statement_type_label }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $statement->published_date->format('M j') }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $statement->title }}</p>
                                    @if($statement->media_pickups > 0)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            💬 {{ $statement->media_pickups }} media pickups
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm text-center py-6">No recent statements</p>
                    @endif
                </div>

                {{-- Media Coverage --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-lg">📰</span> Media Coverage (7 days)
                        </h3>
                        <a href="{{ route('media.index') }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                            View All →
                        </a>
                    </div>

                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $mediaCoverageStats['total'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                        </div>
                        <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-xl font-bold text-green-600">{{ $mediaCoverageStats['positive'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">😊</p>
                        </div>
                        <div class="text-center p-2 bg-gray-100 dark:bg-gray-600 rounded-lg">
                            <p class="text-xl font-bold text-gray-600 dark:text-gray-300">{{ $mediaCoverageStats['neutral'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">😐</p>
                        </div>
                        <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-xl font-bold text-red-600">{{ $mediaCoverageStats['negative'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">☹️</p>
                        </div>
                    </div>

                    @if($recentClips->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($recentClips->take(3) as $clip)
                                <a href="{{ $clip->url }}" target="_blank"
                                    class="block p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $sentimentIcon = match($clip->sentiment) {
                                                'Positive' => '😊',
                                                'Negative' => '☹️',
                                                default => '😐',
                                            };
                                        @endphp
                                        <span>{{ $sentimentIcon }}</span>
                                        <span class="text-sm text-gray-900 dark:text-white truncate flex-1">{{ $clip->title }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $clip->outlet_display_name ?? 'Unknown' }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- UPCOMING DISTRICT EVENTS --}}
            {{-- ============================================== --}}
            @if($upcomingDistrictEvents->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-lg">🏢</span> Upcoming District Events
                    </h3>
                    <a href="{{ route('meetings.index') }}" wire:navigate class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                        View All →
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($upcomingDistrictEvents as $event)
                        <a href="{{ route('meetings.show', $event) }}" wire:navigate
                            class="p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-teal-300 dark:hover:border-teal-700 transition group">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl">📍</span>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white group-hover:text-teal-600">
                                        {{ $event->title }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $event->meeting_date?->format('M j, Y @ g:i A') }}
                                    </p>
                                </div>
                            </div>
                            @if($event->location)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->location }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ============================================== --}}
            {{-- POLICY POSITIONS PREVIEW --}}
            {{-- ============================================== --}}
            @if($topPolicyPositions->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="text-lg">🎯</span> Key Policy Positions
                    </h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($topPolicyPositions as $position)
                        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $position->issue }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ $position->event_count }} recorded events
                            </p>
                            @if($position->latest_event)
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    Latest: {{ \Carbon\Carbon::parse($position->latest_event)->format('M j, Y') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

