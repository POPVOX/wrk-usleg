<div class="flex items-center space-x-1 bg-gray-100 dark:bg-zinc-800 rounded-lg p-1">
    <button wire:click="switchTo('personal')"
        class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $currentView === 'personal' ? 'bg-white dark:bg-zinc-700 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        My Dashboard
    </button>
    <button wire:click="switchTo('overview')"
        class="flex items-center px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $currentView === 'overview' ? 'bg-white dark:bg-zinc-700 shadow text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
        Office Overview
    </button>
</div>