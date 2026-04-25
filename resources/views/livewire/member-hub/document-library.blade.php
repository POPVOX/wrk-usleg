<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    {{-- Flash Messages --}}
    @if(session('message'))
        <div class="fixed top-4 right-4 z-50 px-4 py-3 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 rounded-lg shadow-lg"
            x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('message') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-3">📚</span>
                        Member Document Library
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        {{ $libraryStats['total'] }} documents • {{ $libraryStats['indexed'] }} indexed for AI
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('member.dashboard') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        Dashboard
                    </a>
                    <a href="{{ route('member.hub') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        Ask About Member
                    </a>
                    <button wire:click="openUploadModal"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Upload Document
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search documents..."
                    class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <select wire:model.live="filterType"
                class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach($documentTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Document Grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="space-y-4">
            @forelse($documents as $document)
                <div
                    class="bg-white dark:bg-zinc-800 rounded-xl border border-gray-200 dark:border-zinc-700 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4 min-w-0 flex-1">
                            <div class="text-3xl">{{ $document->type_icon }}</div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $document->title }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300">
                                        {{ $document->type_label }}
                                    </span>
                                    @if($document->document_date)
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $document->document_date->format('M j, Y') }}
                                        </span>
                                    @endif
                                    @if($document->indexed)
                                        <span class="inline-flex items-center text-xs text-green-600 dark:text-green-400">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Indexed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs text-amber-600 dark:text-amber-400">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Not indexed
                                        </span>
                                    @endif
                                </div>
                                @if($document->summary)
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $document->summary }}
                                    </p>
                                @elseif($document->description)
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $document->description }}
                                    </p>
                                @endif
                                @if($document->tags && count($document->tags) > 0)
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach(array_slice($document->tags, 0, 5) as $tag)
                                            <span
                                                class="px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button wire:click="viewDocument({{ $document->id }})"
                                class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors"
                                title="View">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            @if(!$document->indexed)
                                <button wire:click="reindexDocument({{ $document->id }})"
                                    class="p-2 text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors"
                                    title="Index for AI">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                            @endif
                            @if(!$document->summary)
                                <button wire:click="generateSummary({{ $document->id }})"
                                    class="p-2 text-gray-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors"
                                    title="Generate Summary">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </button>
                            @endif
                            <button wire:click="deleteDocument({{ $document->id }})"
                                wire:confirm="Are you sure you want to delete this document?"
                                class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                                title="Delete">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-zinc-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No documents yet</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Upload documents about the Member to power the AI
                        assistant.</p>
                    <button wire:click="openUploadModal" class="mt-4 text-indigo-600 dark:text-indigo-400 hover:underline">
                        Upload your first document →
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $documents->links() }}
        </div>
    </div>

    {{-- Upload Modal --}}
    @if($showUploadModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeUploadModal"></div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="uploadDocument">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upload Document</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title
                                    *</label>
                                <input type="text" wire:model="title"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                                    required>
                                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Document Type
                                    *</label>
                                <select wire:model="document_type"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                                    required>
                                    <option value="">Select type...</option>
                                    @foreach($documentTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('document_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Document
                                    Date</label>
                                <input type="date" wire:model="document_date"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea wire:model="description" rows="2"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload
                                    File</label>
                                <input type="file" wire:model="file" accept=".pdf,.txt,.md,.docx"
                                    class="w-full text-sm text-gray-500 dark:text-gray-400">
                                @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-gray-400 mt-1">Supports PDF, TXT, MD (max 10MB)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Or Paste Text
                                    Content</label>
                                <textarea wire:model="content" rows="4"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                                    placeholder="Paste document text here..."></textarea>
                                @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Source
                                    (optional)</label>
                                <input type="text" wire:model="source" placeholder="e.g., Official Website, Campaign"
                                    class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            </div>

                            <div class="flex items-center space-x-6">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="auto_index"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Index for AI search</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model="generate_summary"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Generate AI summary</span>
                                </label>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-50 dark:bg-zinc-700/50 flex justify-end space-x-3">
                            <button type="button" wire:click="closeUploadModal"
                                class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-lg transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                Upload & Index
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedDocument)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailModal"></div>

                <div
                    class="inline-block align-bottom bg-white dark:bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $selectedDocument->title }}</h3>
                        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 max-h-96 overflow-y-auto">
                        <div class="flex items-center gap-2 mb-4">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300">
                                {{ $selectedDocument->type_label }}
                            </span>
                            @if($selectedDocument->document_date)
                                <span
                                    class="text-sm text-gray-500">{{ $selectedDocument->document_date->format('F j, Y') }}</span>
                            @endif
                            @if($selectedDocument->indexed)
                                <span class="text-xs text-green-600">✓ Indexed</span>
                            @endif
                        </div>

                        @if($selectedDocument->summary)
                            <div class="mb-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300 mb-1">AI Summary</p>
                                <p class="text-sm text-indigo-600 dark:text-indigo-400">{{ $selectedDocument->summary }}</p>
                            </div>
                        @endif

                        @if($selectedDocument->description)
                            <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $selectedDocument->description }}</p>
                        @endif

                        @if($selectedDocument->content)
                            <div class="prose dark:prose-invert prose-sm max-w-none">
                                {!! nl2br(e(\Str::limit($selectedDocument->content, 3000))) !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>