<div>
    {{-- Floating Feedback Button --}}
    <button
        wire:click="open"
        class="fixed bottom-6 right-6 z-40 flex items-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-full shadow-lg hover:shadow-xl transition-all duration-200 group"
        title="Send Feedback"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <span class="hidden sm:inline">Feedback</span>
    </button>

    {{-- Feedback Modal --}}
    @if($isOpen)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-data="feedbackWidget()"
            x-init="init()"
        >
            {{-- Backdrop --}}
            <div 
                class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                wire:click="close"
            ></div>

            {{-- Modal --}}
            <div class="flex min-h-full items-end sm:items-center justify-center p-4">
                <div 
                    class="relative w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-2xl transform transition-all"
                    @click.stop
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Send Feedback</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Help us improve LegiDash</p>
                            </div>
                        </div>
                        <button 
                            wire:click="close"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($submitted)
                        {{-- Success State --}}
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h4 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Thank you!</h4>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Your feedback has been submitted. We'll review it soon.</p>
                            <button 
                                wire:click="close"
                                class="px-6 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                Close
                            </button>
                        </div>
                    @else
                        {{-- Form --}}
                        <form wire:submit="submit" class="p-6 space-y-5">
                            {{-- Feedback Type --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    What type of feedback?
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all {{ $type === 'bug' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                        <input type="radio" wire:model.live="type" value="bug" class="sr-only">
                                        <span class="text-xl mr-3">🐛</span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">Report a bug</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Something's not working right</p>
                                        </div>
                                        @if($type === 'bug')
                                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </label>

                                    <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all {{ $type === 'feature' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                        <input type="radio" wire:model.live="type" value="feature" class="sr-only">
                                        <span class="text-xl mr-3">💡</span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">Suggest a feature</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">I have an idea for improvement</p>
                                        </div>
                                        @if($type === 'feature')
                                            <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </label>

                                    <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all {{ $type === 'general' ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}">
                                        <input type="radio" wire:model.live="type" value="general" class="sr-only">
                                        <span class="text-xl mr-3">💬</span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">General feedback</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Share thoughts or comments</p>
                                        </div>
                                        @if($type === 'general')
                                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </label>
                                </div>
                                @error('type')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Message --}}
                            <div>
                                <label for="feedback-message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tell us more
                                </label>
                                <textarea
                                    id="feedback-message"
                                    wire:model="message"
                                    rows="4"
                                    class="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                                    placeholder="{{ $type === 'bug' ? 'Describe what happened and what you expected to happen...' : ($type === 'feature' ? 'Describe the feature you\'d like to see...' : 'Share your thoughts...') }}"
                                ></textarea>
                                @error('message')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Screenshot Option --}}
                            <div class="flex items-center gap-3">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        wire:model.live="includeScreenshot"
                                        @change="if($event.target.checked) captureScreen()"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-indigo-600"></div>
                                </label>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Include screenshot</span>
                                </div>
                                @if($screenshotData)
                                    <span class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Captured
                                    </span>
                                @endif
                            </div>

                            {{-- Context Info (shown for bug reports) --}}
                            @if($type === 'bug' && $pageUrl)
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">We'll include this info to help debug:</p>
                                    <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
                                        <p><span class="text-gray-400">Page:</span> {{ Str::limit($pageUrl, 50) }}</p>
                                        <p><span class="text-gray-400">Browser:</span> {{ $browser ?: 'Detecting...' }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Submit Button --}}
                            <div class="pt-2">
                                <button
                                    type="submit"
                                    class="w-full px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove>Send Feedback</span>
                                    <span wire:loading class="flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Sending...
                                    </span>
                                </button>
                            </div>

                            {{-- Footer Note --}}
                            <p class="text-center text-xs text-gray-500 dark:text-gray-400">
                                Your feedback goes directly to the LegiDash team.
                            </p>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alpine.js Component for Screenshot & Context Capture --}}
        <script>
            function feedbackWidget() {
                return {
                    init() {
                        // Capture context immediately when modal opens
                        this.captureContext();
                        
                        // Listen for screenshot request
                        Livewire.on('captureScreenshotRequest', () => {
                            this.captureScreen();
                        });
                        
                        // Listen for context request
                        Livewire.on('requestContext', () => {
                            this.captureContext();
                        });
                    },
                    
                    captureContext() {
                        const context = {
                            url: window.location.href,
                            title: document.title,
                            browser: this.getBrowserInfo(),
                            device: this.getDeviceInfo(),
                            resolution: `${window.screen.width}x${window.screen.height}`,
                            errors: this.getConsoleErrors()
                        };
                        
                        @this.call('receiveContext', context);
                    },
                    
                    getBrowserInfo() {
                        const ua = navigator.userAgent;
                        let browser = 'Unknown';
                        
                        if (ua.includes('Firefox/')) {
                            browser = 'Firefox ' + ua.split('Firefox/')[1].split(' ')[0];
                        } else if (ua.includes('Chrome/') && !ua.includes('Edg/')) {
                            browser = 'Chrome ' + ua.split('Chrome/')[1].split(' ')[0];
                        } else if (ua.includes('Safari/') && !ua.includes('Chrome/')) {
                            browser = 'Safari ' + ua.split('Version/')[1]?.split(' ')[0] || '';
                        } else if (ua.includes('Edg/')) {
                            browser = 'Edge ' + ua.split('Edg/')[1].split(' ')[0];
                        }
                        
                        return browser;
                    },
                    
                    getDeviceInfo() {
                        const ua = navigator.userAgent;
                        if (/Mobi|Android/i.test(ua)) {
                            return 'Mobile';
                        } else if (/Tablet|iPad/i.test(ua)) {
                            return 'Tablet';
                        }
                        return 'Desktop';
                    },
                    
                    getConsoleErrors() {
                        // This would require setting up an error listener earlier
                        // For now, return empty array
                        return window._feedbackErrors || [];
                    },
                    
                    async captureScreen() {
                        try {
                            // Close modal temporarily to capture clean screenshot
                            const modal = document.querySelector('[wire\\:click="close"]')?.closest('.fixed');
                            if (modal) modal.style.display = 'none';
                            
                            // Use html2canvas if available, otherwise use native API
                            if (typeof html2canvas !== 'undefined') {
                                const canvas = await html2canvas(document.body, {
                                    logging: false,
                                    useCORS: true,
                                    allowTaint: true
                                });
                                const dataUrl = canvas.toDataURL('image/png');
                                @this.call('receiveScreenshot', dataUrl);
                            } else {
                                // Fallback: just note that screenshot was requested
                                console.log('Screenshot capture requested but html2canvas not available');
                            }
                            
                            // Show modal again
                            if (modal) modal.style.display = '';
                        } catch (error) {
                            console.error('Screenshot capture failed:', error);
                        }
                    }
                };
            }
            
            // Set up error capturing
            window._feedbackErrors = [];
            const originalConsoleError = console.error;
            console.error = function(...args) {
                window._feedbackErrors.push({
                    message: args.map(a => String(a)).join(' '),
                    timestamp: new Date().toISOString()
                });
                // Keep only last 10 errors
                if (window._feedbackErrors.length > 10) {
                    window._feedbackErrors.shift();
                }
                originalConsoleError.apply(console, args);
            };
        </script>
    @endif
</div>


