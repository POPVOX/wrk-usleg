<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Platform Admin' }} - LegiDash Management Console</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="h-full bg-gray-900 text-white font-sans antialiased">
    <div class="min-h-full flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800 border-r border-gray-700 flex flex-col">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white font-bold text-lg">L</span>
                    </div>
                    <div>
                        <h1 class="font-bold text-white">Platform Admin</h1>
                        <p class="text-xs text-gray-400">LegiDash Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-1">
                <a href="{{ route('platform.dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('platform.beta-requests') }}" 
                   class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.beta-requests*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        Beta Requests
                    </span>
                    @php $pendingCount = \App\Models\BetaRequest::where('status', 'pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="px-2 py-0.5 text-xs font-medium bg-amber-500 text-white rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>

                <a href="{{ route('platform.offices') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.offices*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Offices
                </a>

                <a href="{{ route('platform.metrics') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.metrics') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Usage Metrics
                </a>

                <a href="{{ route('platform.feedback') }}" 
                   class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.feedback*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <span class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Feedback
                    </span>
                    @php $newFeedbackCount = \App\Models\UserFeedback::where('status', 'new')->count(); @endphp
                    @if($newFeedbackCount > 0)
                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">{{ $newFeedbackCount }}</span>
                    @endif
                </a>

                <a href="{{ route('platform.insights') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.insights') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    AI Insights
                </a>

                <a href="{{ route('platform.outreach') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.outreach*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Outreach
                </a>

                <div class="pt-4 mt-4 border-t border-gray-700">
                    <a href="{{ route('platform.settings') }}" 
                       class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('platform.settings') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Admin Settings
                    </a>
                </div>
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-medium text-sm">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">Platform Admin</p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('dashboard') }}" class="flex-1 px-3 py-1.5 text-xs text-center text-gray-300 bg-gray-700 hover:bg-gray-600 rounded transition-colors">
                        Back to LegiDash
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-1.5 text-xs text-gray-300 bg-gray-700 hover:bg-gray-600 rounded transition-colors">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
