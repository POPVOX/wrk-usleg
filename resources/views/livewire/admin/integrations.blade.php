<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Integrations</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Manage connected services and sync status.</p>
            </div>

            {{-- System Status Banner --}}
            @php
                $allGood = $calendarSync['status'] !== 'warning' && $aiStatus['status'] !== 'disconnected';
            @endphp
            <div class="mb-6 p-4 rounded-lg {{ $allGood ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' }}">
                <div class="flex items-center gap-3">
                    @if($allGood)
                        <span class="text-xl">✓</span>
                        <span class="font-medium text-green-800 dark:text-green-300">All systems operational</span>
                    @else
                        <span class="text-xl">⚠️</span>
                        <span class="font-medium text-amber-800 dark:text-amber-300">Some integrations need attention</span>
                    @endif
                </div>
            </div>

            {{-- Calendar Sync --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">Google Calendar</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Sync meetings with Google Calendar</p>
                        </div>
                    </div>

                    @php
                        $statusColors = [
                            'connected' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                            'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                            'disconnected' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$calendarSync['status']] }}">
                        {{ $calendarSync['status'] === 'connected' ? '✓ Connected' : ($calendarSync['status'] === 'warning' ? '⚠️ Needs Attention' : 'Not Connected') }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $calendarSync['connected_users'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Connected</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $calendarSync['total_users'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Users</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold {{ $calendarSync['stale_users'] > 0 ? 'text-amber-600' : 'text-gray-900 dark:text-white' }}">{{ $calendarSync['stale_users'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Need Reconnect</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm {{ $calendarSync['status'] === 'connected' ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                        {{ $calendarSync['message'] }}
                    </p>
                </div>
            </div>

            {{-- AI Features --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">AI Features</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">AI-powered insights and assistance</p>
                        </div>
                    </div>

                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$aiStatus['status']] }}">
                        {{ $aiStatus['status'] === 'connected' ? '✓ Enabled' : 'Not Configured' }}
                    </span>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $aiStatus['message'] }}
                    </p>
                </div>
            </div>

            {{-- Future Integrations --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Coming Soon</h3>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Email Integration</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Connect office email for correspondence tracking</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                        <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-700 dark:text-gray-300">Congress.gov API</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Automatic bill and vote tracking</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



