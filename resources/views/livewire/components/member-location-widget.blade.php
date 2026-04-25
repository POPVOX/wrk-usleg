<div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100 dark:border-zinc-700 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <span class="mr-2">📍</span>
            Member Location
        </h3>
        @if(session('location-updated'))
            <span class="text-sm text-green-600 dark:text-green-400 animate-pulse">Updated!</span>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-6">
        @if($currentLocation)
            <div class="space-y-4">
                {{-- Location --}}
                <div class="flex items-start space-x-3">
                    <div
                        class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-lg text-gray-900 dark:text-white">{{ $currentLocation->location_name }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Updated
                            {{ $currentLocation->updated_at->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Time --}}
                <div class="flex items-start space-x-3">
                    <div
                        class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-lg text-gray-900 dark:text-white">{{ $currentTime }} <span
                                class="text-gray-500">{{ $timezoneAbbrev }}</span></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentLocation->timezone_label }}</p>
                    </div>
                </div>

                {{-- Current Activity --}}
                @if($currentLocation->current_activity)
                    <div class="flex items-start space-x-3">
                        <div
                            class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $currentLocation->current_activity }}</p>
                            @if($currentLocation->activity_until)
                                @if($currentLocation->isActivityExpired())
                                    <p class="text-sm text-red-500 dark:text-red-400">Activity ended</p>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Until
                                        {{ $currentLocation->activity_until->format('g:i A') }}</p>
                                @endif
                            @endif
                        </div>
                        <button wire:click="clearActivity" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <div class="text-center py-4">
                <div
                    class="w-12 h-12 rounded-full bg-gray-100 dark:bg-zinc-700 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400">Member location not set</p>
            </div>
        @endif

        {{-- Update Button --}}
        @if(!$showUpdateForm)
            <button wire:click="toggleUpdateForm"
                class="mt-4 w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Update Location
            </button>
        @endif

        {{-- Update Form --}}
        @if($showUpdateForm)
            <form wire:submit.prevent="updateLocation"
                class="mt-4 space-y-4 pt-4 border-t border-gray-100 dark:border-zinc-700">
                {{-- Location --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                    <select wire:model="location_name"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select location</option>
                        @foreach($locationOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('location_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                    <select wire:model="timezone"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($timezoneOptions as $tz => $label)
                            <option value="{{ $tz }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('timezone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Current Activity --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Activity <span
                            class="text-gray-400">(optional)</span></label>
                    <input type="text" wire:model="current_activity" placeholder="e.g., In committee hearing"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Activity Until --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Until <span
                            class="text-gray-400">(optional)</span></label>
                    <input type="datetime-local" wire:model="activity_until"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Buttons --}}
                <div class="flex space-x-3 pt-2">
                    <button type="submit"
                        class="flex-1 flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update
                    </button>
                    <button type="button" wire:click="toggleUpdateForm"
                        class="px-4 py-2 bg-gray-100 dark:bg-zinc-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-zinc-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>