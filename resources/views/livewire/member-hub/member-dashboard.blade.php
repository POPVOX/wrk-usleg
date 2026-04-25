<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    @if($memberInfo['photo_url'])
                        <img src="{{ $memberInfo['photo_url'] }}" alt="{{ $memberInfo['name'] }}" 
                            class="w-16 h-16 rounded-full object-cover border-2 border-indigo-500">
                    @else
                        <div class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xl font-bold">
                            {{ substr($memberInfo['name'], 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $memberInfo['name'] }}
                        </h1>
                        <p class="text-gray-500 dark:text-gray-400">
                            ({{ $memberInfo['party'] }}-{{ $memberInfo['state'] }}{{ $memberInfo['district'] ? '-'.$memberInfo['district'] : '' }})
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('member.hub') }}" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Ask About Member
                    </a>
                    <a href="{{ route('member.documents') }}" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Top Row: Location & Today's Schedule --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Location Widget --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📍</span>
                        Current Location
                    </h2>
                </div>
                <div class="p-6">
                    @if($currentLocation)
                        <div class="flex items-start space-x-4">
                            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $currentLocation->location_name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::now($currentLocation->timezone)->format('g:i A') }} {{ $currentLocation->timezone_label }}
                                </p>
                                @if($currentLocation->current_activity)
                                    <p class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">
                                        {{ $currentLocation->current_activity }}
                                        @if($currentLocation->activity_until)
                                            (until {{ $currentLocation->activity_until->format('g:i A') }})
                                        @endif
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-gray-400">Updated {{ $currentLocation->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 dark:text-gray-400">Location not set</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Today's Schedule --}}
            <div class="lg:col-span-2 bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📅</span>
                        Today's Schedule
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j') }}</span>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-zinc-700 max-h-64 overflow-y-auto">
                    @forelse($todaySchedule as $meeting)
                        <a href="{{ route('meetings.show', $meeting) }}" class="block px-6 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $meeting->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $meeting->meeting_date->format('g:i A') }}
                                        @if($meeting->location) • {{ $meeting->location }} @endif
                                    </p>
                                </div>
                                @if($meeting->organizations->first())
                                    <span class="text-xs px-2 py-1 bg-gray-100 dark:bg-zinc-700 rounded text-gray-600 dark:text-gray-400">
                                        {{ $meeting->organizations->first()->name }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-gray-500 dark:text-gray-400">No meetings scheduled for today</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Legislative Activity Row --}}
        <div class="mb-8">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📜</span>
                        Legislative Activity
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Last 30 Days</span>
                </div>
                <div class="p-6">
                    {{-- Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center p-4 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $legislativeStats['bills_sponsored'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Bills Sponsored</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $legislativeStats['bills_cosponsored'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Bills Cosponsored</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $legislativeStats['votes_cast'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Votes Cast</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $keyMetrics['voting_participation'] }}%</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Voting Participation</p>
                        </div>
                    </div>

                    {{-- Recent Bills --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Recent Legislation</h3>
                            <div class="space-y-2">
                                @forelse($recentBills->take(4) as $bill)
                                    <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                                        <div class="flex items-start justify-between">
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                    {{ $bill->bill_number }}: {{ \Str::limit($bill->title, 60) }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ ucfirst($bill->sponsor_role) }} • {{ $bill->introduced_date->format('M j, Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No recent legislation</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Recent Votes --}}
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Recent Votes</h3>
                            <div class="space-y-2">
                                @forelse($recentVotes->take(4) as $vote)
                                    <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg flex items-center justify-between">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-gray-900 dark:text-white truncate">
                                                {{ \Str::limit($vote->question, 50) }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $vote->vote_date->format('M j') }}</p>
                                        </div>
                                        <span class="ml-2 px-2 py-1 text-xs font-semibold rounded {{ $vote->vote_color_class }}">
                                            {{ $vote->vote_cast }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No recent votes</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Row: Communications & Media --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Public Communications --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📢</span>
                        Public Communications
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Last 7 Days</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $communicationsStats['statements'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Statements</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $communicationsStats['press_releases'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Press Releases</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $communicationsStats['speeches'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Speeches</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @forelse($recentStatements as $statement)
                            <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                                <div class="flex items-start space-x-2">
                                    <span class="text-lg">{{ $statement->type_icon }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $statement->title }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $statement->type_label }} • {{ $statement->published_date->format('M j') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">No recent statements</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Media Coverage --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📰</span>
                        Media Coverage
                    </h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Last 7 Days</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        <div class="text-center p-2 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $mediaStats['total'] }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                        <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-xl font-bold text-green-600 dark:text-green-400">{{ $mediaStats['positive'] }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400">Positive</p>
                        </div>
                        <div class="text-center p-2 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                            <p class="text-xl font-bold text-gray-600 dark:text-gray-400">{{ $mediaStats['neutral'] }}</p>
                            <p class="text-xs text-gray-500">Neutral</p>
                        </div>
                        <div class="text-center p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <p class="text-xl font-bold text-red-600 dark:text-red-400">{{ $mediaStats['negative'] }}</p>
                            <p class="text-xs text-red-600 dark:text-red-400">Negative</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        @forelse($recentClips as $clip)
                            <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $clip->headline }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $clip->outlet?->name ?? 'Unknown' }} • {{ $clip->publish_date?->format('M j') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">No recent coverage</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Social Media & News Sources Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            {{-- Social Media Links --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">🔗</span>
                        Social Media
                    </h2>
                </div>
                <div class="p-6">
                    @if(count($socialMedia) > 0)
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($socialMedia as $platform => $data)
                                <a href="{{ $data['url'] }}" target="_blank" rel="noopener noreferrer"
                                    class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-zinc-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-600/50 transition-colors group">
                                    @if($platform === 'twitter')
                                        <svg class="w-6 h-6 {{ $data['color'] }}" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                    @elseif($platform === 'facebook')
                                        <svg class="w-6 h-6 {{ $data['color'] }}" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    @elseif($platform === 'instagram')
                                        <svg class="w-6 h-6 {{ $data['color'] }}" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                        </svg>
                                    @elseif($platform === 'youtube')
                                        <svg class="w-6 h-6 {{ $data['color'] }}" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $data['label'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $data['handle'] }}</p>
                                    </div>
                                    <svg class="w-4 h-4 ml-auto text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No social media accounts configured</p>
                            <a href="{{ route('setup.wizard') }}" class="text-indigo-600 hover:text-indigo-500 text-sm mt-2 inline-block">Configure in Setup Wizard →</a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- News Sources --}}
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-2">📰</span>
                        Configured News Sources
                    </h2>
                    <span class="text-xs px-2 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full">
                        {{ count($newsArticles['sources']) }} sources
                    </span>
                </div>
                <div class="p-6">
                    @if(count($newsArticles['sources']) > 0)
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($newsArticles['sources'] as $source)
                                <span class="px-3 py-1 bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-full text-sm">
                                    {{ $source }}
                                </span>
                            @endforeach
                        </div>
                        <div class="border-t border-gray-100 dark:border-zinc-700 pt-4">
                            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Recent Articles</h3>
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                @forelse($newsArticles['articles'] as $article)
                                    <div class="p-3 bg-gray-50 dark:bg-zinc-700/50 rounded-lg">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $article->headline }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $article->outlet?->name ?? 'Unknown Source' }} • {{ $article->publish_date?->format('M j, Y') }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No articles found yet. Articles will appear as they're imported.</p>
                                @endforelse
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No news sources configured</p>
                            <a href="{{ route('setup.wizard') }}" class="text-indigo-600 hover:text-indigo-500 text-sm mt-2 inline-block">Configure in Setup Wizard →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
