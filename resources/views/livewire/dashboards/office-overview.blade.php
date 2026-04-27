<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Office Overview</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">{{ now()->format('l, F j, Y') }}</p>
                </div>
                <livewire:components.dashboard-toggle />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Top Row: Key Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            {{-- Active Issues --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Issues</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['active_issues'] }}
                        </p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Meetings This Week --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Meetings This Week</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $metrics['meetings_this_week'] }}</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Pending Actions --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Actions</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $metrics['pending_actions'] }}</p>
                    </div>
                    <div
                        class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                @if($metrics['overdue_actions'] > 0)
                    <p class="text-xs text-red-600 dark:text-red-400 mt-2">{{ $metrics['overdue_actions'] }} overdue</p>
                @endif
            </div>

            {{-- Priority Issues --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Priority Issues</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                            {{ $metrics['priority_issues'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Staff Activity This Week --}}
            <div
                class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-4 col-span-2 lg:col-span-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">This Week</p>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Meetings logged</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $staffActivity['meetings_logged'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Notes added</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $staffActivity['notes_added'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Actions completed</span>
                        <span
                            class="font-medium text-gray-900 dark:text-white">{{ $staffActivity['actions_completed'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="space-y-6">
                {{-- Member Location Widget --}}
                <livewire:components.member-location-widget />

                {{-- Priority Issues --}}
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">🎯</span>
                            Priority Issues
                        </h2>
                        <a href="{{ route('issues.index') }}"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($priorityIssues as $issue)
                            <a href="{{ route('issues.show', $issue) }}"
                                class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $issue->name }}</p>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full 
                                                    @if($issue->isPrimaryPriority()) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                                    @else bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400
                                                    @endif
                                                ">
                                                {{ $issue->priority_level }}
                                            </span>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400">{{ $issue->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No priority issues</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Center Column - Member Schedule --}}
            <div class="space-y-6">
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">📅</span>
                            Office Schedule
                        </h2>
                        <a href="{{ route('meetings.index') }}"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($memberSchedule as $date => $meetings)
                            <div class="px-6 py-3">
                                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    @if(\Carbon\Carbon::parse($date)->isToday())
                                        Today
                                    @elseif(\Carbon\Carbon::parse($date)->isTomorrow())
                                        Tomorrow
                                    @else
                                        {{ \Carbon\Carbon::parse($date)->format('l, M j') }}
                                    @endif
                                </p>
                                <div class="space-y-2">
                                    @foreach($meetings as $meeting)
                                        <a href="{{ route('meetings.show', $meeting) }}"
                                            class="block p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors">
                                            <div class="flex items-start justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {{ $meeting->title }}</p>
                                                    <div
                                                        class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $meeting->meeting_date->format('g:i A') }}
                                                        @if($meeting->organizations->count() > 0)
                                                            <span class="mx-1">•</span>
                                                            {{ $meeting->organizations->first()->name }}
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($meeting->user)
                                                    <span
                                                        class="ml-2 text-xs text-gray-400 dark:text-gray-500">{{ explode(' ', $meeting->user->name)[0] }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">No meetings scheduled</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Needs Attention --}}
                @if($needingAttention->count() > 0)
                    <div
                        class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-amber-200 dark:border-amber-800 overflow-hidden">
                        <div
                            class="px-6 py-4 border-b border-amber-100 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-200 flex items-center">
                                <span class="mr-2">⚠️</span>
                                Needs Attention
                            </h2>
                            <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Meetings missing notes</p>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                            @foreach($needingAttention as $meeting)
                                <a href="{{ route('meetings.show', $meeting) }}"
                                    class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $meeting->title }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $meeting->meeting_date->format('M j') }} •
                                                {{ $meeting->user?->name ?? 'Unassigned' }}
                                            </p>
                                        </div>
                                        <span class="text-xs text-amber-600 dark:text-amber-400">Add notes</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Upcoming Deadlines --}}
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">⏰</span>
                            Upcoming Deadlines
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($upcomingDeadlines as $deadline)
                            <div class="px-6 py-4">
                                <div class="flex items-start justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-sm font-medium text-gray-900 dark:text-white {{ $deadline['is_overdue'] ? 'text-red-600 dark:text-red-400' : '' }}">
                                            {{ $deadline['description'] }}
                                        </p>
                                        <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <span>{{ $deadline['assigned_to'] }}</span>
                                            @if($deadline['meeting'])
                                                <span class="mx-1">•</span>
                                                <span>{{ $deadline['meeting'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span
                                        class="ml-2 text-sm {{ $deadline['is_overdue'] ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $deadline['due_date']->format('M j') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming deadlines</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Active Relationships --}}
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">🤝</span>
                            Active Relationships
                        </h2>
                        <a href="{{ route('organizations.index') }}"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($activeRelationships as $org)
                            <a href="{{ route('organizations.show', $org) }}"
                                class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $org->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $org->type ?? 'Organization' }}</p>
                                    </div>
                                    <span
                                        class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">{{ $org->meetings_count }}
                                        meetings</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent interactions</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Media Attention --}}
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">📰</span>
                            Media This Week
                        </h2>
                        <a href="{{ route('media.index') }}"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($recentClips as $clip)
                            <div class="px-6 py-3">
                                <div class="flex items-start justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $clip->headline }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $clip->outlet?->name ?? 'Unknown outlet' }} •
                                            {{ $clip->publish_date?->format('M j') }}
                                        </p>
                                    </div>
                                    @if($clip->sentiment)
                                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full
                                                    @if($clip->sentiment === 'positive') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                                    @elseif($clip->sentiment === 'negative') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                                    @else bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300
                                                    @endif
                                                ">
                                            {{ ucfirst($clip->sentiment) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent coverage</p>
                            </div>
                        @endforelse

                        {{-- Pending Inquiries Alert --}}
                        @if($pendingInquiries->count() > 0)
                            <div class="px-6 py-3 bg-amber-50 dark:bg-amber-900/20">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-amber-800 dark:text-amber-200">
                                        {{ $pendingInquiries->count() }} pending
                                        inquir{{ $pendingInquiries->count() > 1 ? 'ies' : 'y' }}
                                    </span>
                                    <a href="{{ route('media.index') }}?tab=inquiries"
                                        class="text-sm text-amber-600 dark:text-amber-400 hover:underline">Review</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
