<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LegiDash') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Ensure sidebar layout works */
        .sidebar-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 256px;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            min-width: 0;
        }

        @media (max-width: 1023px) {
            .sidebar {
                display: none;
            }

            .mobile-nav {
                display: block;
            }
        }

        @media (min-width: 1024px) {
            .mobile-nav {
                display: none;
            }
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <div class="sidebar-layout">
        <!-- Sidebar -->
        <aside class="sidebar bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700">
            <div class="flex flex-col h-screen sticky top-0">
                <!-- Logo -->
                <div class="flex items-center justify-center px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                    {{-- Dashboard with sub-menu --}}
                    <div x-data="{ expanded: {{ request()->routeIs('dashboard*') ? 'true' : 'false' }} }">
                        <button @click="expanded = !expanded"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                Dashboard
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="expanded" x-collapse class="mt-1 ml-8 space-y-1">
                            <a href="{{ route('dashboard.personal') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('dashboard.personal') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                My Dashboard
                            </a>
                            <a href="{{ route('dashboard.overview') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('dashboard.overview') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                Office Overview
                            </a>
                        </div>
                    </div>

                    {{-- Issues --}}
                    <a href="{{ route('issues.index') }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('issues.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Issues
                    </a>

                    {{-- Meetings --}}
                    <a href="{{ route('meetings.index') }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('meetings.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Meetings
                    </a>

                    {{-- Organizations --}}
                    <a href="{{ route('organizations.index') }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('organizations.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Organizations
                    </a>

                    {{-- Contacts (was People) --}}
                    <a href="{{ route('contacts.index') }}" wire:navigate
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('contacts.*') || request()->routeIs('people.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Contacts
                    </a>

                    {{-- Media & Press --}}
                    @if(Route::has('media.index'))
                        <a href="{{ route('media.index') }}" wire:navigate
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('media.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            Media & Press
                        </a>
                    @endif

                    {{-- Member Hub with sub-menu --}}
                    @if(Route::has('member.hub'))
                    <div x-data="{ expanded: {{ request()->routeIs('member.*') || request()->routeIs('setup.priorities') ? 'true' : 'false' }} }">
                        <button @click="expanded = !expanded"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('member.*') || request()->routeIs('setup.priorities') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Member Hub
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="expanded" x-collapse class="mt-1 ml-8 space-y-1">
                            <a href="{{ route('member.hub') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('member.hub') || request()->routeIs('member.dashboard') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                Overview
                            </a>
                            @if(Route::has('setup.priorities'))
                            <a href="{{ route('setup.priorities') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('setup.priorities') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                Profile & Priorities
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Knowledge Hub --}}
                    @if(Route::has('knowledge.hub'))
                        <a href="{{ route('knowledge.hub') }}" wire:navigate
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('knowledge.hub') || request()->routeIs('knowledge.base') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            Knowledge Hub
                        </a>
                    @endif

                    {{-- Team Hub with sub-menu --}}
                    @if(Route::has('team.hub'))
                    <div x-data="{ expanded: {{ request()->routeIs('team.*') || request()->routeIs('management.team') ? 'true' : 'false' }} }">
                        <button @click="expanded = !expanded"
                            class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('team.*') || request()->routeIs('management.team') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Team Hub
                            </span>
                            <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="expanded" x-collapse class="mt-1 ml-8 space-y-1">
                            <a href="{{ route('team.hub') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('team.hub') && !request()->routeIs('team.assignments') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                Directory
                            </a>
                            @if(auth()->user()?->isManagement())
                            <a href="{{ route('management.team') }}" wire:navigate
                                class="block px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('management.team') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                Coverage & Assignments
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Settings Section --}}
                    @if(auth()->user()?->isManagement() || auth()->user()?->isAdmin())
                        <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                            <div x-data="{ expanded: {{ request()->routeIs('admin.*') || request()->routeIs('setup.wizard') ? 'true' : 'false' }} }">
                                <button @click="expanded = !expanded"
                                    class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.*') || request()->routeIs('setup.wizard') ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                    <span class="flex items-center gap-3">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Settings
                                    </span>
                                    <svg class="w-4 h-4 transition-transform" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="expanded" x-collapse class="mt-1 ml-8 space-y-1">
                                    <a href="{{ route('admin.ai') }}" wire:navigate
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('admin.ai') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        AI Options
                                    </a>
                                    @if(auth()->user()?->isAdmin())
                                    <a href="{{ route('admin.settings') }}" wire:navigate
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('admin.settings') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Permissions
                                    </a>
                                    <a href="{{ route('admin.integrations') }}" wire:navigate
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('admin.integrations') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Integrations
                                    </a>
                                    <a href="{{ route('admin.billing') }}" wire:navigate
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('admin.billing') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Billing & Plan
                                    </a>
                                    {{-- Setup Wizard - only show if not completed or in debug mode --}}
                                    @if(Route::has('setup.wizard') && (!config('office.setup_completed', false) || config('app.debug')))
                                    <div class="border-t border-gray-200 dark:border-gray-600 my-2"></div>
                                    <a href="{{ route('setup.wizard') }}" wire:navigate
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm {{ request()->routeIs('setup.wizard') ? 'text-indigo-700 dark:text-indigo-300 bg-indigo-50/50 dark:bg-indigo-900/30' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Re-run Setup
                                    </a>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Platform Admin Section (Super Admins Only) --}}
                    @if(auth()->user()?->isSuperAdmin())
                        <div class="pt-4 mt-4 border-t border-purple-200 dark:border-purple-800">
                            <a href="{{ route('platform.dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('platform.*') ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300' : 'text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30' }}">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                                    L
                                </div>
                                <div class="flex-1">
                                    <span class="block">Platform Admin</span>
                                    <span class="text-xs opacity-75">Platform Management</span>
                                </div>
                            </a>
                        </div>
                    @endif
                </nav>

                <!-- User Section -->
                @auth
                    <div class="border-t border-gray-200 dark:border-gray-700 px-4 py-4" x-data="{ userMenuOpen: false }">
                        <!-- User Profile Button -->
                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen"
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0 text-left">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ auth()->user()->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ auth()->user()->email }}
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 transition-transform"
                                    :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 15l7-7 7 7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95" @click.away="userMenuOpen = false"
                                class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <a href="{{ route('profile') }}" wire:navigate
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Profile
                                </a>
                                <a href="{{ route('profile') }}" wire:navigate
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Settings
                                </a>
                                <div class="border-t border-gray-200 dark:border-gray-700"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Mobile Navigation (shows on smaller screens) -->
            <div class="mobile-nav">
                <livewire:layout.navigation />
            </div>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
    
    {{-- Feedback Widget - Available on all pages for beta users --}}
    @auth
        @livewire('feedback-widget')
    @endauth
</body>

</html>