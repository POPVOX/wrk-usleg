<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $currentSection = match (true) {
        request()->routeIs('dashboard*') => 'Home',
        request()->routeIs('meetings.*') => 'Meetings',
        request()->routeIs('issues.*') => 'Issues',
        request()->routeIs('contacts.*'), request()->routeIs('people.*') => 'Contacts',
        request()->routeIs('organizations.*') => 'Organizations',
        request()->routeIs('media.*') => 'Media',
        request()->routeIs('member.*'), request()->routeIs('setup.priorities') => 'Member',
        request()->routeIs('knowledge.*') => 'Knowledge',
        request()->routeIs('team.*'), request()->routeIs('management.*') => 'Team',
        request()->routeIs('admin.*'), request()->routeIs('setup.wizard') => 'Office Settings',
        request()->routeIs('platform.*') => 'Platform Admin',
        default => 'Workspace',
    };

    $moreActive = request()->routeIs('organizations.*')
        || request()->routeIs('media.*')
        || request()->routeIs('member.*')
        || request()->routeIs('knowledge.*')
        || request()->routeIs('team.*')
        || request()->routeIs('management.*')
        || request()->routeIs('admin.*')
        || request()->routeIs('platform.*')
        || request()->routeIs('setup.*')
        || request()->routeIs('profile');

    $bottomTabs = [
        [
            'label' => 'Home',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard*'),
            'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        ],
        [
            'label' => 'Meetings',
            'href' => route('meetings.index'),
            'active' => request()->routeIs('meetings.*'),
            'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        ],
        [
            'label' => 'Issues',
            'href' => route('issues.index'),
            'active' => request()->routeIs('issues.*'),
            'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        ],
        [
            'label' => 'Contacts',
            'href' => route('contacts.index'),
            'active' => request()->routeIs('contacts.*') || request()->routeIs('people.*'),
            'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        ],
    ];
@endphp

<nav x-data="{ open: false }" class="lg:hidden">
    <div class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
        <div class="flex h-16 items-center justify-between px-4">
            <div class="flex min-w-0 items-center gap-3">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="LegiDash" class="h-8 w-auto">
                </a>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $currentSection }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->name }}</p>
                </div>
            </div>

            <button
                @click="open = true"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                aria-label="Open navigation"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="fixed inset-0 z-50">
        <div
            x-show="open"
            x-transition.opacity
            @click="open = false"
            class="absolute inset-0 bg-gray-950/45 backdrop-blur-sm"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-[86vw] max-w-sm flex-col bg-white shadow-2xl dark:bg-gray-800"
        >
            <div class="border-b border-gray-200 px-5 py-5 dark:border-gray-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600 dark:text-indigo-400">LegiDash</p>
                        <h2 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
                    </div>
                    <button
                        @click="open = false"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400"
                        aria-label="Close navigation"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex-1 space-y-6 overflow-y-auto px-4 py-5">
                <section>
                    <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">Core</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('dashboard') }}" wire:navigate @click="open = false" class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('dashboard*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">
                            <span>Home</span>
                            <span class="text-xs text-gray-400">Dashboard</span>
                        </a>
                        <a href="{{ route('meetings.index') }}" wire:navigate @click="open = false" class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('meetings.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">
                            <span>Meetings</span>
                            <span class="text-xs text-gray-400">Schedule + notes</span>
                        </a>
                        <a href="{{ route('issues.index') }}" wire:navigate @click="open = false" class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('issues.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">
                            <span>Issues</span>
                            <span class="text-xs text-gray-400">Workstreams</span>
                        </a>
                        <a href="{{ route('contacts.index') }}" wire:navigate @click="open = false" class="flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('contacts.*') || request()->routeIs('people.*') ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">
                            <span>Contacts</span>
                            <span class="text-xs text-gray-400">People</span>
                        </a>
                    </div>
                </section>

                <section>
                    <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">Research & strategy</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('organizations.index') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('organizations.*') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Organizations</a>
                        @if(Route::has('media.index'))
                            <a href="{{ route('media.index') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('media.*') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Media & Press</a>
                        @endif
                        @if(Route::has('member.hub'))
                            <a href="{{ route('member.hub') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('member.*') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Member Hub</a>
                        @endif
                        @if(Route::has('setup.priorities'))
                            <a href="{{ route('setup.priorities') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('setup.priorities') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Member Priorities</a>
                        @endif
                        @if(Route::has('knowledge.hub'))
                            <a href="{{ route('knowledge.hub') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('knowledge.*') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Knowledge Hub</a>
                        @endif
                        @if(Route::has('team.hub'))
                            <a href="{{ route('team.hub') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('team.*') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Team Hub</a>
                        @endif
                        @if(auth()->user()?->isManagement())
                            <a href="{{ route('management.team') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('management.team') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Coverage & Assignments</a>
                        @endif
                    </div>
                </section>

                @if(auth()->user()->isAdmin() || auth()->user()->isManagement())
                    <section>
                        <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">Office admin</p>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('admin.ai') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.ai') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">AI Options</a>
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.settings') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Office Settings</a>
                                <a href="{{ route('admin.integrations') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.integrations') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Integrations</a>
                                <a href="{{ route('admin.billing') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.billing') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Billing & Plan</a>
                                <a href="{{ route('admin.permissions') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('admin.permissions') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Permissions</a>
                            @endif
                            @if(Route::has('setup.wizard'))
                                <a href="{{ route('setup.wizard') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('setup.wizard') ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70' }}">Setup Wizard</a>
                            @endif
                        </div>
                    </section>
                @endif

                @if(auth()->user()->isSuperAdmin())
                    <section>
                        <p class="px-2 text-xs font-semibold uppercase tracking-[0.2em] text-purple-400">Platform</p>
                        <div class="mt-3 space-y-1">
                            <a href="{{ route('platform.dashboard') }}" wire:navigate @click="open = false" class="flex items-center rounded-2xl px-3 py-3 text-sm font-medium {{ request()->routeIs('platform.*') ? 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'text-purple-700 hover:bg-purple-50 dark:text-purple-300 dark:hover:bg-purple-900/20' }}">
                                Platform Admin
                            </a>
                        </div>
                    </section>
                @endif
            </div>

            <div class="border-t border-gray-200 px-4 py-4 dark:border-gray-700">
                <a href="{{ route('profile') }}" wire:navigate @click="open = false" class="mb-3 flex items-center rounded-2xl px-3 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700/70">
                    Profile
                </a>
                <button wire:click="logout" class="flex w-full items-center rounded-2xl px-3 py-3 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                    Log Out
                </button>
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
        <div class="mx-auto grid max-w-md grid-cols-5 px-2 pb-2 pt-2">
            @foreach($bottomTabs as $tab)
                <a
                    href="{{ $tab['href'] }}"
                    wire:navigate
                    class="flex flex-col items-center justify-center rounded-2xl px-2 py-2 text-[11px] font-medium transition {{ $tab['active'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}"
                >
                    <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}" />
                    </svg>
                    <span>{{ $tab['label'] }}</span>
                </a>
            @endforeach

            <button
                type="button"
                @click="open = true"
                class="flex flex-col items-center justify-center rounded-2xl px-2 py-2 text-[11px] font-medium transition {{ $moreActive ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}"
            >
                <svg class="mb-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <span>More</span>
            </button>
        </div>
    </div>
</nav>
