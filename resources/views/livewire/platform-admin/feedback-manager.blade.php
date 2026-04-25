<div class="p-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">User Feedback</h1>
        <p class="text-gray-400">Review and manage feedback from beta users</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl p-4 border border-gray-700">
            <p class="text-sm text-gray-400">Total</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-blue-900/30 rounded-xl p-4 border border-blue-700">
            <p class="text-sm text-blue-400">New</p>
            <p class="text-2xl font-bold text-blue-300">{{ $stats['new'] }}</p>
        </div>
        <div class="bg-yellow-900/30 rounded-xl p-4 border border-yellow-700">
            <p class="text-sm text-yellow-400">In Review</p>
            <p class="text-2xl font-bold text-yellow-300">{{ $stats['in_review'] }}</p>
        </div>
        <div class="bg-red-900/30 rounded-xl p-4 border border-red-700">
            <p class="text-sm text-red-400">Open Bugs</p>
            <p class="text-2xl font-bold text-red-300">{{ $stats['bugs'] }}</p>
        </div>
        <div class="bg-amber-900/30 rounded-xl p-4 border border-amber-700">
            <p class="text-sm text-amber-400">Feature Requests</p>
            <p class="text-2xl font-bold text-amber-300">{{ $stats['features'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800 rounded-xl p-4 mb-6 border border-gray-700">
        <div class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search feedback..."
                    class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                >
            </div>
            <div>
                <select
                    wire:model.live="filterType"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                >
                    <option value="">All Types</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select
                    wire:model.live="filterStatus"
                    class="px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                >
                    <option value="">All Statuses</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Feedback List --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse($feedbacks as $feedback)
                <div 
                    wire:click="selectFeedback({{ $feedback->id }})"
                    class="bg-gray-800 rounded-xl p-4 cursor-pointer transition-all border {{ $selectedFeedbackId === $feedback->id ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-700 hover:border-gray-600' }}"
                >
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">{{ $feedback->getTypeIcon() }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1 flex-wrap">
                                <span class="font-medium text-white">{{ $feedback->user_name }}</span>
                                <span class="text-xs text-gray-500">{{ $feedback->created_at->diffForHumans() }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full 
                                    @switch($feedback->status)
                                        @case('new') bg-blue-500/20 text-blue-400 @break
                                        @case('reviewing') bg-yellow-500/20 text-yellow-400 @break
                                        @case('in_progress') bg-purple-500/20 text-purple-400 @break
                                        @case('resolved') bg-green-500/20 text-green-400 @break
                                        @default bg-gray-500/20 text-gray-400
                                    @endswitch
                                ">
                                    {{ $feedback->getStatusLabel() }}
                                </span>
                            </div>
                            @if($feedback->office_name)
                                <p class="text-xs text-gray-500 mb-1">{{ $feedback->office_name }}</p>
                            @endif
                            <p class="text-sm text-gray-300 line-clamp-2">{{ $feedback->message }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                <span>{{ $feedback->getTypeLabel() }}</span>
                                @if($feedback->screenshot_path)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Screenshot
                                    </span>
                                @endif
                                @if($feedback->page_url)
                                    <span class="truncate max-w-[200px]">{{ parse_url($feedback->page_url, PHP_URL_PATH) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-gray-800 rounded-xl p-12 text-center border border-gray-700">
                    <div class="w-16 h-16 mx-auto bg-gray-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-white mb-2">No feedback yet</h3>
                    <p class="text-gray-400">Feedback from beta users will appear here.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $feedbacks->links() }}
            </div>
        </div>

        {{-- Detail Panel --}}
        <div class="lg:col-span-1">
            @if($selectedFeedback)
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-5 sticky top-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Details</h3>
                        <button wire:click="closeDetail" class="p-1 text-gray-400 hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Type --}}
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">{{ $selectedFeedback->getTypeIcon() }}</span>
                            <span class="font-medium text-white">{{ $selectedFeedback->getTypeLabel() }}</span>
                        </div>

                        {{-- User Info --}}
                        <div class="p-3 bg-gray-700/50 rounded-lg">
                            <p class="font-medium text-white">{{ $selectedFeedback->user_name }}</p>
                            <p class="text-sm text-gray-400">{{ $selectedFeedback->user_email }}</p>
                            @if($selectedFeedback->office_name)
                                <p class="text-sm text-gray-400 mt-1">{{ $selectedFeedback->office_name }}</p>
                            @endif
                        </div>

                        {{-- Message --}}
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Message</label>
                            <p class="text-sm text-gray-200 whitespace-pre-wrap">{{ $selectedFeedback->message }}</p>
                        </div>

                        {{-- Context --}}
                        @if($selectedFeedback->page_url)
                            <div class="p-3 bg-gray-700/50 rounded-lg text-xs space-y-1">
                                <p><span class="text-gray-500">Page:</span> <span class="text-gray-300">{{ $selectedFeedback->page_url }}</span></p>
                                <p><span class="text-gray-500">Browser:</span> <span class="text-gray-300">{{ $selectedFeedback->browser }}</span></p>
                                <p><span class="text-gray-500">Device:</span> <span class="text-gray-300">{{ $selectedFeedback->device }}</span></p>
                                <p><span class="text-gray-500">Screen:</span> <span class="text-gray-300">{{ $selectedFeedback->screen_resolution }}</span></p>
                                <p><span class="text-gray-500">Submitted:</span> <span class="text-gray-300">{{ $selectedFeedback->created_at->format('M j, Y g:i A') }}</span></p>
                            </div>
                        @endif

                        {{-- Console Errors --}}
                        @if($selectedFeedback->console_errors && count($selectedFeedback->console_errors) > 0)
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Console Errors</label>
                                <div class="p-2 bg-red-900/30 rounded-lg text-xs text-red-300 max-h-32 overflow-y-auto border border-red-800">
                                    @foreach($selectedFeedback->console_errors as $error)
                                        <p class="mb-1 font-mono">{{ $error['message'] ?? $error }}</p>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <hr class="border-gray-700">

                        {{-- Status Update --}}
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Status</label>
                            <select
                                wire:model="newStatus"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                            >
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Admin Notes --}}
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Internal Notes</label>
                            <textarea
                                wire:model="adminNotes"
                                rows="3"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white resize-none placeholder-gray-500"
                                placeholder="Notes for the team..."
                            ></textarea>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2">
                            <button
                                wire:click="updateFeedback"
                                class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors"
                            >
                                Save
                            </button>
                            <button
                                wire:click="deleteFeedback({{ $selectedFeedback->id }})"
                                wire:confirm="Delete this feedback?"
                                class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 font-medium rounded-lg transition-colors"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 text-center">
                    <div class="w-12 h-12 mx-auto bg-gray-700 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-400">Select feedback to view details</p>
                </div>
            @endif
        </div>
    </div>
</div>

