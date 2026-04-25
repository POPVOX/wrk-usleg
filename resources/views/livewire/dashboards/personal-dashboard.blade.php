<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Good {{ now()->format('H') < 12 ? 'Morning' : (now()->format('H') < 17 ? 'Afternoon' : 'Evening') }}, {{ explode(' ', auth()->user()->name)[0] }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Here's what's on your plate today</p>
                </div>
                <livewire:components.dashboard-toggle />
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Alert Bar --}}
        @if($overdueCount > 0 || $meetingsNeedingNotes->count() > 0)
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-amber-800 dark:text-amber-200 font-medium">
                        @if($overdueCount > 0)
                            {{ $overdueCount }} overdue action{{ $overdueCount > 1 ? 's' : '' }}
                        @endif
                        @if($overdueCount > 0 && $meetingsNeedingNotes->count() > 0) • @endif
                        @if($meetingsNeedingNotes->count() > 0)
                            {{ $meetingsNeedingNotes->count() }} meeting{{ $meetingsNeedingNotes->count() > 1 ? 's' : '' }} need notes
                        @endif
                    </span>
                </div>
            </div>
        @endif

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column (2 cols wide) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- My Upcoming Meetings --}}
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">📅</span>
                            My Upcoming Meetings
                        </h2>
                        <a href="{{ route('meetings.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($upcomingMeetings as $meeting)
                            <a href="{{ route('meetings.show', $meeting) }}" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">{{ $meeting->title }}</p>
                                        <div class="flex items-center mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $meeting->meeting_date->format('D, M j') }} 
                                            </span>
                                            @if($meeting->organizations->count() > 0)
                                                <span class="mx-2">•</span>
                                                <span>{{ $meeting->organizations->first()->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($meeting->meeting_link)
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full 
                                            @if(str_contains($meeting->meeting_link, 'zoom')) bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
                                            @elseif(str_contains($meeting->meeting_link, 'meet.google')) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                            @elseif(str_contains($meeting->meeting_link, 'teams')) bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                                            @else bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300
                                            @endif
                                        ">
                                            Video
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">No upcoming meetings</p>
                                <a href="{{ route('meetings.create') }}" class="mt-2 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Schedule a meeting</a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- My Assigned Issues --}}
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">📁</span>
                            My Assigned Issues
                        </h2>
                        <a href="{{ route('issues.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View All</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($assignedIssues as $issue)
                            <a href="{{ route('issues.show', $issue) }}" class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center min-w-0">
                                        <span class="text-xl mr-3">{{ $issue->type_icon }}</span>
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 dark:text-white truncate">{{ $issue->name }}</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $issue->type ?? 'Issue' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $issue->priority_color }} bg-opacity-10">
                                            {{ $issue->priority_level ?? 'Normal' }}
                                        </span>
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-zinc-700 text-gray-600 dark:text-gray-300">
                                            {{ $issue->status }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">No assigned issues</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- My Action Items --}}
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">✓</span>
                            My Action Items
                            @if($overdueCount > 0)
                                <span class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 rounded-full">{{ $overdueCount }} overdue</span>
                            @endif
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($actionItems as $action)
                            <div class="px-6 py-4 flex items-start justify-between hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-start space-x-3 flex-1 min-w-0">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-5 h-5 rounded border-2 {{ $action->due_date && $action->due_date->isPast() ? 'border-red-400' : 'border-gray-300 dark:border-zinc-600' }}"></div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-gray-900 dark:text-white {{ $action->due_date && $action->due_date->isPast() ? 'font-medium' : '' }}">{{ $action->commitment }}</p>
                                        @if($action->meeting)
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                From: {{ $action->meeting->title }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if($action->due_date)
                                    <span class="ml-4 text-sm {{ $action->due_date->isPast() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $action->due_date->format('M j') }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center">
                                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400">All caught up! No pending actions.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Member's Schedule (Mini View) --}}
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">⭐</span>
                            Member Schedule
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Next 3 days</p>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($memberSchedule as $meeting)
                            <div class="px-6 py-3">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $meeting->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $meeting->meeting_date->format('D g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No priority meetings scheduled</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Meetings Needing Notes --}}
                @if($meetingsNeedingNotes->count() > 0)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-amber-200 dark:border-amber-800 overflow-hidden">
                        <div class="px-6 py-4 border-b border-amber-100 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                            <h2 class="text-lg font-semibold text-amber-800 dark:text-amber-200 flex items-center">
                                <span class="mr-2">📝</span>
                                Needs Notes
                            </h2>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                            @foreach($meetingsNeedingNotes as $meeting)
                                <a href="{{ route('meetings.show', $meeting) }}" class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $meeting->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $meeting->meeting_date->format('M j, Y') }}</p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- My Recent Activity --}}
                <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                            <span class="mr-2">🕐</span>
                            My Recent Activity
                        </h2>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-zinc-700">
                        @forelse($recentActivity as $activity)
                            <a href="{{ $activity['url'] }}" class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <span class="text-lg">{{ $activity['icon'] }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900 dark:text-white truncate">{{ $activity['description'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $activity['timestamp']->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-6 py-6 text-center">
                                <p class="text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
