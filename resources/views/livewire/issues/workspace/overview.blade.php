{{-- Overview Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Quick Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                        <span class="text-lg">📝</span>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $stats['publications_drafting'] }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">In Progress</div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                    <span class="text-lg">📝</span>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['publications_drafting'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">In Progress</div>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                    <span class="text-lg">🎪</span>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $stats['events_upcoming'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Upcoming Events</div>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                <span class="text-lg">🎪</span>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['events_upcoming'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Upcoming Events</div>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg flex items-center justify-center">
                <span class="text-lg">✅</span>
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $stats['milestones_completed'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Completed</div>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg flex items-center justify-center">
            <span class="text-lg">✅</span>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $stats['milestones_completed'] }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Completed</div>
        </div>
    </div>
</div>
<div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
            <span class="text-lg">📄</span>
        </div>
        <div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $stats['documents'] }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Documents</div>
        </div>
    </div>
</div>
<div class="flex items-center gap-3">
    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-lg flex items-center justify-center">
        <span class="text-lg">📄</span>
    </div>
    <div>
        <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['documents'] }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400">Documents</div>
    </div>
</div>
</div>
</div>

{{-- Upcoming Publications --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Publications</h3>
            <button wire:click="setTab('publications')"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                View all →
            </button>
        </div>
    </div>
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($issue->publications->where('status', '!=', 'published')->sortBy('target_date')->take(5) as $pub)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $pub->title }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            @if($pub->target_date)
                                Target: {{ $pub->target_date->format('M j, Y') }}
                            @endif
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Models\IssuePublication::STATUS_COLORS[$pub->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ \App\Models\IssuePublication::STATUSES[$pub->status] ?? ucfirst($pub->status) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                No upcoming publications
            </div>
        @endforelse
    </div>
</div>

{{-- Upcoming Events --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-5 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Events</h3>
            <button wire:click="setTab('events')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                View all →
            </button>
        </div>
    </div>
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($issue->events->where('status', '!=', 'completed')->sortBy('event_date')->take(4) as $event)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white flex items-center gap-2">
                            <span>{{ \App\Models\IssueEvent::TYPES[$event->type] ?? $event->type }}</span>
                            <span class="text-gray-400">•</span>
                            <span>{{ $event->title }}</span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            @if($event->event_date)
                                {{ $event->event_date->format('M j, Y \a\t g:i A') }}
                            @endif
                            @if($event->location)
                                — {{ $event->location }}
                            @endif
                        </div>
                    </div>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Models\IssueEvent::STATUS_COLORS[$event->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ \App\Models\IssueEvent::STATUSES[$event->status] ?? ucfirst($event->status) }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                No upcoming events
            </div>
        @endforelse
    </div>
</div>
</div>

{{-- Sidebar --}}
<div class="space-y-6">
    {{-- Recent Meetings --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-5 border-b border-gray-200 dark:border-gray-700">
            <h3 class="font-semibold text-gray-900 dark:text-white">Recent Meetings</h3>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-72 overflow-y-auto">
            @forelse($issue->meetings as $meeting)
                <a href="{{ route('meetings.show', $meeting) }}" wire:navigate
                    class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <div class="font-medium text-gray-900 dark:text-white text-sm">
                        {{ $meeting->title ?: 'Meeting' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $meeting->meeting_date?->format('M j, Y') }}
                    </div>
                </a>
            @empty
                <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    No meetings linked yet
                </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <button wire:click="setTab('collaborator')"
                class="w-full flex items-center gap-3 px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:from-indigo-600 hover:to-purple-600 transition-colors text-sm font-medium">
                <span>🤖</span>
                <span>Ask AI Collaborator</span>
            </button>
            <button wire:click="setTab('timeline')"
                class="w-full flex items-center gap-3 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                <span>📅</span>
                <span>View Timeline</span>
            </button>
            <a href="{{ route('issues.show', $issue) }}" wire:navigate
                class="w-full flex items-center gap-3 px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm font-medium">
                <span>📋</span>
                <span>Classic Issue View</span>
            </a>
        </div>
    </div>

    {{-- Issue Info --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Issue Info</h3>
        <dl class="space-y-3 text-sm">
            @if($issue->start_date)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Start Date</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $issue->start_date->format('M j, Y') }}
                    </dd>
                </div>
            @endif
            @if($issue->target_end_date)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Target End</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">
                        {{ $issue->target_end_date->format('M j, Y') }}
                    </dd>
                </div>
            @endif
            @if($issue->lead)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Lead</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $issue->lead }}</dd>
                </div>
            @endif
            @if($issue->scope)
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Scope</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $issue->scope }}</dd>
                </div>
            @endif
        </dl>
    </div>
</div>
</div>