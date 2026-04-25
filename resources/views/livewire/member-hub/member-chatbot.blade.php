<div class="min-h-screen bg-gray-50 dark:bg-zinc-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                        <span class="mr-3">💬</span>
                        Ask About {{ config('office.member_name', 'the Member') }}
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">
                        AI-powered Q&A based on {{ $documentCount }} indexed documents
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('member.dashboard') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('member.documents') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Documents
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Questions --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-wrap gap-2">
            @foreach($quickQuestions as $question)
                <button wire:click="askQuickQuestion('{{ $question }}')"
                    class="px-3 py-1.5 text-sm bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-full text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:border-indigo-300 dark:hover:border-indigo-700 transition-colors">
                    {{ $question }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Chat Container --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-32">
        <div class="space-y-4">
            @foreach($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] {{ $message['role'] === 'user'
                ? 'bg-indigo-600 text-white rounded-2xl rounded-br-md'
                : 'bg-white dark:bg-zinc-800 text-gray-900 dark:text-white rounded-2xl rounded-bl-md border border-gray-200 dark:border-zinc-700' 
                            }} px-5 py-4 shadow-sm">
                            {{-- Message Content --}}
                            <div
                                class="prose dark:prose-invert prose-sm max-w-none {{ $message['role'] === 'user' ? 'text-white' : '' }}">
                                {!! \Str::markdown($message['content']) !!}
                            </div>

                            {{-- Sources (for assistant messages) --}}
                            @if($message['role'] === 'assistant' && !empty($message['sources']))
                                <div
                                    class="mt-4 pt-3 border-t {{ $message['role'] === 'user' ? 'border-indigo-500' : 'border-gray-200 dark:border-zinc-700' }}">
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Sources:</p>
                                    <div class="space-y-1">
                                        @foreach($message['sources'] as $source)
                                            <a href="{{ route('member.documents') }}?document={{ $source['document_id'] ?? '' }}"
                                                class="block text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                                • {{ $source['title'] }} ({{ $source['type'] }})
                                                @if($source['date']) - {{ $source['date'] }} @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Timestamp --}}
                            <p class="mt-2 text-xs {{ $message['role'] === 'user' ? 'text-indigo-200' : 'text-gray-400' }}">
                                {{ \Carbon\Carbon::parse($message['timestamp'])->format('g:i A') }}
                            </p>
                        </div>
                    </div>
            @endforeach

            {{-- Processing Indicator --}}
            @if($isProcessing)
                <div class="flex justify-start">
                    <div
                        class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-2xl rounded-bl-md px-5 py-4 shadow-sm">
                        <div class="flex items-center space-x-2">
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce" style="animation-delay: 0ms">
                                </div>
                                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"
                                    style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-indigo-600 rounded-full animate-bounce"
                                    style="animation-delay: 300ms"></div>
                            </div>
                            <span class="text-sm text-gray-500 dark:text-gray-400">Searching documents...</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Input Bar (Fixed Bottom) --}}
    <div
        class="fixed bottom-0 left-0 right-0 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700 p-4">
        <div class="max-w-4xl mx-auto">
            <form wire:submit.prevent="sendMessage" class="flex items-center space-x-3">
                <div class="flex-1 relative">
                    <input type="text" wire:model="newMessage"
                        placeholder="Ask about {{ config('office.member_name', 'the Member') }}..."
                        class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-zinc-600 rounded-xl bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        {{ $isProcessing ? 'disabled' : '' }}>
                </div>
                <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center"
                    {{ $isProcessing ? 'disabled' : '' }}>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
                <button type="button" wire:click="clearConversation"
                    class="p-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Clear conversation">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </form>
            <p class="text-xs text-center text-gray-400 mt-2">
                Responses are based on uploaded documents and live data.
                <a href="{{ route('member.documents') }}"
                    class="text-indigo-600 dark:text-indigo-400 hover:underline">Upload more documents</a> to improve
                accuracy.
            </p>
        </div>
    </div>
</div>