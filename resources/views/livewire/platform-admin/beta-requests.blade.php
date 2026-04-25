<div class="p-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Beta Requests</h1>
        <p class="text-gray-400">Manage incoming access requests</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-amber-900/30 rounded-xl p-4 border border-amber-700">
            <p class="text-sm text-amber-400">Pending</p>
            <p class="text-2xl font-bold text-amber-300">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-green-900/30 rounded-xl p-4 border border-green-700">
            <p class="text-sm text-green-400">Approved</p>
            <p class="text-2xl font-bold text-green-300">{{ $stats['approved'] }}</p>
        </div>
        <div class="bg-red-900/30 rounded-xl p-4 border border-red-700">
            <p class="text-sm text-red-400">Declined</p>
            <p class="text-2xl font-bold text-red-300">{{ $stats['declined'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-sm text-gray-400">Total</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800 rounded-xl p-4 mb-6 border border-gray-700">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by name, email, or office..."
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400"
                >
            </div>
            <div>
                <select wire:model.live="filterStatus" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="declined">Declined</option>
                </select>
            </div>
            <div>
                <select wire:model.live="filterLevel" class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">All Levels</option>
                    <option value="federal">Federal</option>
                    <option value="state">State</option>
                    <option value="local">Local</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Requests List --}}
    <div class="space-y-4">
        @forelse($requests as $request)
            <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 hover:border-gray-600 transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-lg font-semibold text-white">{{ $request->name }}</h3>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @switch($request->status)
                                    @case('pending') bg-amber-500/20 text-amber-400 @break
                                    @case('approved') bg-green-500/20 text-green-400 @break
                                    @case('declined') bg-red-500/20 text-red-400 @break
                                    @default bg-gray-500/20 text-gray-400
                                @endswitch
                            ">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-400">{{ $request->title }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $request->created_at->diffForHumans() }}</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
                    <div>
                        <span class="text-gray-500">Office:</span>
                        <p class="text-gray-200">{{ $request->elected_official_name }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Level:</span>
                        <p class="text-gray-200">{{ ucfirst($request->level) }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">Email:</span>
                        <p class="text-gray-200">{{ $request->email }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500">State:</span>
                        <p class="text-gray-200">{{ $request->state ?? 'N/A' }}</p>
                    </div>
                </div>

                @if($request->interests)
                    <div class="mb-4">
                        <span class="text-gray-500 text-sm">Interests:</span>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($request->interests as $interest)
                                <span class="px-2 py-0.5 bg-gray-700 text-gray-300 rounded text-xs">{{ $interest }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($request->notes)
                    <div class="p-3 bg-gray-700/50 rounded-lg text-sm text-gray-300 mb-4">
                        "{{ $request->notes }}"
                    </div>
                @endif

                @if($request->status === 'pending')
                    <div class="flex gap-2">
                        <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition-colors">
                            Approve
                        </button>
                        <button class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded-lg transition-colors">
                            Decline
                        </button>
                        <button class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 text-sm rounded-lg transition-colors">
                            View Details
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-gray-800 rounded-xl p-12 text-center border border-gray-700">
                <div class="w-16 h-16 mx-auto bg-gray-700 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No requests found</h3>
                <p class="text-gray-400">Beta requests will appear here.</p>
            </div>
        @endforelse

        {{ $requests->links() }}
    </div>
</div>

