<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            @if($viewingPrompt)
                {{-- Prompt Detail View --}}
                <div>
                    <button wire:click="closePromptView" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to How AI Works
                    </button>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $prompts[$viewingPrompt]['name'] }} Prompt
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">
                        This prompt guides the AI when {{ strtolower($prompts[$viewingPrompt]['description']) }}.
                    </p>

                    {{-- System Prompt --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">System Prompt</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">This is the core instruction we send to the AI.</p>
                        
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 font-mono text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap border border-gray-200 dark:border-gray-700">{{ $prompts[$viewingPrompt]['system_prompt'] }}</div>
                    </div>

                    {{-- Explanations --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-4">Why We Wrote It This Way</h2>
                        
                        <dl class="space-y-4">
                            @foreach($prompts[$viewingPrompt]['explanations'] as $quote => $explanation)
                            <div>
                                <dt class="text-gray-900 dark:text-white font-medium mb-1">{{ $quote }}</dt>
                                <dd class="text-gray-600 dark:text-gray-400 text-sm">{{ $explanation }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>

                    {{-- Custom Instructions --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Your Office's Customizations</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Add additional instructions specific to your office. These are appended to our base prompt.</p>
                        
                        <textarea wire:model="customPrompt" rows="4" 
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Always flag if follow-up with Legislative Counsel is needed. Note any budget/appropriations implications."></textarea>
                        
                        <div class="flex gap-3 mt-4">
                            <button wire:click="saveCustomPrompt" class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition">
                                Save Changes
                            </button>
                            <button class="px-4 py-2 text-gray-600 dark:text-gray-400 font-medium hover:text-gray-900 dark:hover:text-white transition">
                                Reset to Default
                            </button>
                        </div>
                    </div>

                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Last updated by LegiDash: November 2024 · 
                        <a href="#" class="text-indigo-600 dark:text-indigo-400 hover:underline">View prompt change history →</a>
                    </p>
                </div>
            @else
                {{-- Main AI Transparency Page --}}
                
                {{-- Section 1: Overview --}}
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">How AI Works in LegiDash</h1>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        LegiDash uses artificial intelligence to help with meeting summaries, briefing preparation, and answering questions about your office's work. We believe you should understand exactly how it works—not just in LegiDash, but as a foundation for evaluating any AI tool.
                    </p>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        AI features are optional. Your office can use LegiDash with or without them.
                    </p>

                    {{-- AI Status Banner --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-700 dark:text-gray-300 font-medium">AI Features:</span>
                            @if($aiEnabled)
                                <span class="inline-flex items-center gap-2 text-green-700 dark:text-green-400 font-medium">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Enabled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 font-medium">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                    Disabled
                                </span>
                            @endif
                        </div>
                        <button wire:click="toggleAiModal" class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition">
                            Manage Settings
                        </button>
                    </div>
                </div>

                {{-- Section 2: The Model --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Current AI Stack</h2>

                    <div class="grid gap-4 md:grid-cols-3 mb-6">
                        <div class="rounded-lg border border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 p-4 dark:border-orange-800 dark:from-orange-900/20 dark:to-amber-900/20">
                            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Anthropic</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">{{ $anthropicModel }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Primary reasoning, drafting, and analysis tasks</p>
                        </div>
                        <div class="rounded-lg border border-sky-100 bg-gradient-to-r from-sky-50 to-cyan-50 p-4 dark:border-sky-800 dark:from-sky-900/20 dark:to-cyan-900/20">
                            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">OpenAI Chat</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">{{ $openAiChatModel }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">OpenAI-backed chat and assistant interactions</p>
                        </div>
                        <div class="rounded-lg border border-emerald-100 bg-gradient-to-r from-emerald-50 to-green-50 p-4 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-green-900/20">
                            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">OpenAI Embeddings</p>
                            <p class="text-lg font-bold text-gray-900 dark:text-white mt-2">{{ $openAiEmbeddingModel }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Semantic search, retrieval, and document similarity</p>
                        </div>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        LegiDash currently uses more than one model depending on the task. Anthropic powers the core reasoning and drafting workflows, while OpenAI powers chat-specific paths and embeddings for search and retrieval.
                    </p>

                    <ul class="space-y-2 mb-6">
                        <li class="flex items-start gap-2 text-gray-600 dark:text-gray-400">
                            <span class="text-gray-400 mt-1">•</span>
                            Different models are used for different jobs instead of forcing one provider to do everything
                        </li>
                        <li class="flex items-start gap-2 text-gray-600 dark:text-gray-400">
                            <span class="text-gray-400 mt-1">•</span>
                            Search and retrieval use embeddings rather than full-document keyword matching alone
                        </li>
                        <li class="flex items-start gap-2 text-gray-600 dark:text-gray-400">
                            <span class="text-gray-400 mt-1">•</span>
                            Model choices can be updated centrally through environment configuration
                        </li>
                    </ul>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">📚 Learn More</p>
                        <div class="space-y-2">
                            <a href="https://www.anthropic.com/company" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">About Anthropic →</a>
                            <a href="https://www.anthropic.com/claude" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Claude capabilities and limitations →</a>
                            <a href="https://platform.openai.com/docs/overview" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">OpenAI API overview →</a>
                        </div>
                    </div>

                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-4">
                        These model names are pulled from the app's current configuration so this page stays aligned with the live environment.
                    </p>
                </div>

                {{-- Section 3: What Data Is Shared --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">What Data Is Shared</h2>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        When you use AI features, LegiDash sends relevant content to the AI model for processing. Here's exactly what happens:
                    </p>

                    {{-- Data Flow Diagram --}}
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col items-center space-y-4 text-center">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">Your content</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-500">(meeting notes, documents, questions)</p>
                            
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            
                            <div class="bg-white dark:bg-gray-800 rounded-lg px-6 py-3 border border-gray-300 dark:border-gray-600">
                                <span class="text-gray-900 dark:text-white font-semibold">LegiDash servers</span>
                            </div>
                            
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Sent via secure API
                            </div>
                            
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            
                            <div class="bg-orange-50 dark:bg-orange-900/30 rounded-lg px-6 py-3 border border-orange-200 dark:border-orange-700">
                                <span class="text-gray-900 dark:text-white font-semibold">Anthropic or OpenAI API</span>
                            </div>
                            
                            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                Response returned
                            </div>
                            
                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span class="text-gray-700 dark:text-gray-300 font-medium">AI output</span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-500">(summary, answer, etc.)</p>
                        </div>
                    </div>

                    {{-- What This Means --}}
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">What This Means</h3>
                    
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-2">
                                <div class="flex items-start gap-2 text-green-700 dark:text-green-400">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                <span class="text-sm">Relevant content is sent to the configured AI provider needed for the feature you use</span>
                                </div>
                                <div class="flex items-start gap-2 text-green-700 dark:text-green-400">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                <span class="text-sm">Transmission is encrypted</span>
                                </div>
                                <div class="flex items-start gap-2 text-green-700 dark:text-green-400">
                                    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                <span class="text-sm">Different workflows may call Anthropic, OpenAI, or both</span>
                                </div>
                            </div>
                        <div class="space-y-2">
                            <div class="flex items-start gap-2 text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-sm">Do not paste information your office would not send to an external processor</span>
                            </div>
                            <div class="flex items-start gap-2 text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-sm">AI output can be wrong and still needs human review</span>
                            </div>
                            <div class="flex items-start gap-2 text-red-600 dark:text-red-400">
                                <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="text-sm">Provider-specific retention and privacy terms should be reviewed separately from this product overview</span>
                            </div>
                        </div>
                    </div>

                    {{-- Documentation Links --}}
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">📄 Documentation</p>
                        <div class="space-y-2">
                            <a href="https://www.anthropic.com/api-data-privacy" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Anthropic API Data Policy →</a>
                            <a href="https://openai.com/policies/how-your-data-is-used-to-improve-model-performance/" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">OpenAI API data controls →</a>
                            <a href="#" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">LegiDash Privacy Policy →</a>
                            <a href="#" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">LegiDash Terms of Service →</a>
                        </div>
                    </div>
                </div>

                {{-- Section 4: How We Guide the AI (Prompts) --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">How We Guide the AI</h2>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        AI models respond based on instructions called "prompts." The way a prompt is written significantly affects the output. We've carefully designed our prompts for accuracy, usefulness, and appropriate tone.
                    </p>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        You can view all of our prompts, and your office can add custom instructions to tailor the AI's output to your preferences.
                    </p>

                    {{-- Prompt Library --}}
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg divide-y divide-gray-200 dark:divide-gray-700 mb-6">
                        @foreach($prompts as $key => $prompt)
                        <div class="p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $prompt['name'] }}</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $prompt['description'] }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button wire:click="viewPrompt('{{ $key }}')" class="px-3 py-1.5 text-sm text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition">
                                        View Prompt
                                    </button>
                                    <button wire:click="viewPrompt('{{ $key }}')" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition">
                                        Customize
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Why Prompts Matter --}}
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-100 dark:border-amber-800">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-3">💡 Why Prompts Matter</p>
                        <p class="text-sm text-amber-900 dark:text-amber-200 leading-relaxed mb-3">
                            The same AI model can produce very different results depending on how it's prompted. For example:
                        </p>
                        <ul class="space-y-2 text-sm text-amber-800 dark:text-amber-300">
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-1">•</span>
                                Asking for "a summary" vs. "a summary focused on action items" yields different outputs
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-1">•</span>
                                Adding "do not include information not in the source" reduces hallucination
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-amber-600 mt-1">•</span>
                                Specifying tone ("professional," "concise") shapes the writing style
                            </li>
                        </ul>
                        <p class="text-sm text-amber-800 dark:text-amber-300 mt-3 font-medium">
                            When evaluating any AI tool, ask: What prompts are they using? Can you see them?
                        </p>
                    </div>
                </div>

                {{-- Section 5: Limitations & Best Practices --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Limitations & Best Practices</h2>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-6">
                        AI is a powerful tool, but it has real limitations. Understanding these helps you use it effectively.
                    </p>

                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Limitations --}}
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-5 border border-red-100 dark:border-red-800">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-300 uppercase tracking-wide mb-4">⚠️ Limitations</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="font-medium text-red-900 dark:text-red-200 mb-1">May occasionally produce inaccurate information</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">AI can "hallucinate"—generating plausible-sounding but incorrect details. Always verify important facts.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-red-900 dark:text-red-200 mb-1">Doesn't know what it doesn't know</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">AI won't tell you when it's uncertain. It may answer confidently even when it shouldn't.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-red-900 dark:text-red-200 mb-1">No real-time information</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">The AI's knowledge has a cutoff date and doesn't include recent events unless provided in context.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-red-900 dark:text-red-200 mb-1">Can reflect biases</p>
                                    <p class="text-sm text-red-700 dark:text-red-300">AI models can reflect biases present in their training data. Review outputs critically.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Best Practices --}}
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-5 border border-green-100 dark:border-green-800">
                            <p class="text-sm font-semibold text-green-800 dark:text-green-300 uppercase tracking-wide mb-4">✓ Best Practices</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="font-medium text-green-900 dark:text-green-200 mb-1">Use AI as a starting point, not final product</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">AI-generated content should be reviewed and edited before use.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-green-900 dark:text-green-200 mb-1">Verify facts and figures</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">Don't assume AI-generated statistics, dates, or quotes are accurate.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-green-900 dark:text-green-200 mb-1">Be specific in your requests</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">The more context and specificity you provide, the better the output.</p>
                                </div>
                                <div>
                                    <p class="font-medium text-green-900 dark:text-green-200 mb-1">Iterate and refine</p>
                                    <p class="text-sm text-green-700 dark:text-green-300">If the first output isn't right, provide feedback and try again.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section 6: Questions & Resources --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Questions?</h2>
                    
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed mb-4">
                        We're happy to discuss how AI works in LegiDash or answer questions about AI more broadly.
                    </p>
                    
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Contact us at <a href="mailto:hello@popvox.org" class="text-indigo-600 dark:text-indigo-400 hover:underline">hello@popvox.org</a>
                    </p>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">📚 Additional Resources</p>
                        <div class="space-y-2">
                            <a href="https://www.popvox.org/blog/ai-in-congress" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">POPVOX Foundation: AI in Congress Guide →</a>
                            <a href="https://crsreports.congress.gov/product/pdf/IF/IF11882" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">Congressional Research Service: AI Primer →</a>
                            <a href="https://www.gao.gov/products/gao-21-519sp" target="_blank" class="block text-indigo-600 dark:text-indigo-400 hover:underline text-sm">GAO: AI Accountability Framework →</a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Enable AI Modal --}}
    @if($showEnableModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showEnableModal', false)"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Enable AI Features</h2>
                
                <p class="text-gray-600 dark:text-gray-400 mb-4">Before enabling, please confirm you've reviewed:</p>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Which AI model we use and why
                    </div>
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        What data is shared with the model provider
                    </div>
                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        The limitations of AI-generated content
                    </div>
                </div>
                
                <label class="flex items-start gap-3 mb-6 cursor-pointer">
                    <input type="checkbox" wire:model.live="confirmationChecked" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-gray-700 dark:text-gray-300 text-sm">I've reviewed this information and want to enable AI features for my office.</span>
                </label>
                
                <div class="flex gap-3">
                    <button wire:click="enableAi" 
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ !$confirmationChecked ? 'disabled' : '' }}>
                        Enable AI
                    </button>
                    <button wire:click="$set('showEnableModal', false)" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 font-medium rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Disable AI Modal --}}
    @if($showDisableModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="$set('showDisableModal', false)"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">AI Settings</h2>
                
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-gray-700 dark:text-gray-300">AI Features:</span>
                    <span class="inline-flex items-center gap-2 text-green-700 dark:text-green-400 font-medium">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Enabled
                    </span>
                </div>
                
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Your office has AI features turned on. You can disable them at any time.
                </p>
                
                <button wire:click="disableAi" class="w-full px-4 py-2 text-red-600 dark:text-red-400 font-medium rounded-lg border border-red-300 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition mb-4">
                    Disable AI
                </button>
                
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Note: Disabling AI will turn off meeting summaries, briefing generation, and AI-powered search. Your existing data and manual features will not be affected.
                </p>
                
                <button wire:click="$set('showDisableModal', false)" class="w-full mt-4 px-4 py-2 text-gray-700 dark:text-gray-300 font-medium hover:text-gray-900 dark:hover:text-white transition">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>


