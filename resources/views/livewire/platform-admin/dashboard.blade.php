<div class="p-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Dashboard</h1>
        <p class="text-gray-400">LegiDash Beta Program Overview</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">Pending Requests</span>
                <span class="text-2xl">📥</span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['pending_requests'] }}</p>
            <a href="{{ route('platform.beta-requests') }}" class="text-sm text-indigo-400 hover:text-indigo-300">View all →</a>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">Total Users</span>
                <span class="text-2xl">👥</span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['total_users'] }}</p>
            <p class="text-sm text-gray-500">Across all offices</p>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">New Feedback</span>
                <span class="text-2xl">💬</span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['new_feedback'] }}</p>
            <a href="{{ route('platform.feedback') }}" class="text-sm text-indigo-400 hover:text-indigo-300">Review →</a>
        </div>

        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">Approved</span>
                <span class="text-2xl">✅</span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['approved_requests'] }}</p>
            <p class="text-sm text-gray-500">Beta approvals</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Beta Requests --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Recent Beta Requests</h2>
                <a href="{{ route('platform.beta-requests') }}" class="text-sm text-indigo-400 hover:text-indigo-300">View all</a>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($recentRequests as $request)
                    <div class="p-4 hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-white">{{ $request->name }}</span>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @switch($request->status)
                                    @case('pending') bg-amber-500/20 text-amber-400 @break
                                    @case('approved') bg-green-500/20 text-green-400 @break
                                    @case('onboarded') bg-blue-500/20 text-blue-400 @break
                                    @case('declined') bg-red-500/20 text-red-400 @break
                                    @default bg-gray-500/20 text-gray-400
                                @endswitch
                            ">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-400">{{ $request->title }} · {{ $request->elected_official_name }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $request->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        No beta requests yet
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Feedback --}}
        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Recent Feedback</h2>
                <a href="{{ route('platform.feedback') }}" class="text-sm text-indigo-400 hover:text-indigo-300">View all</a>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($recentFeedback as $feedback)
                    <div class="p-4 hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-lg">{{ $feedback->getTypeIcon() }}</span>
                            <span class="font-medium text-white">{{ $feedback->getTypeLabel() }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full 
                                @switch($feedback->status)
                                    @case('new') bg-blue-500/20 text-blue-400 @break
                                    @case('reviewing') bg-yellow-500/20 text-yellow-400 @break
                                    @case('resolved') bg-green-500/20 text-green-400 @break
                                    @default bg-gray-500/20 text-gray-400
                                @endswitch
                            ">
                                {{ $feedback->getStatusLabel() }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-300 line-clamp-2">{{ $feedback->message }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $feedback->user_name }} · {{ $feedback->created_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        No feedback yet
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-8 bg-gray-800 rounded-xl border border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('platform.beta-requests') }}?status=pending" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors">
                Review Pending Requests
            </a>
            <a href="{{ route('platform.feedback') }}?filterStatus=new" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Review New Feedback
            </a>
            <a href="{{ route('platform.insights') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                View AI Insights
            </a>
        </div>
    </div>
</div>
