<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 h-full">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
        <span class="text-lg">📍</span> Member Location
    </h3>

    @if($currentLocation)
        <div class="space-y-4">
            {{-- Location & Time --}}
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <span class="text-2xl">📍</span>
                </div>
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $currentLocation->location_name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Updated {{ $currentLocation->updated_at->diffForHumans() }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <span class="text-2xl">🕐</span>
                </div>
                <div>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $currentTime }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $currentLocation->timezone_label }}</p>
                </div>
            </div>

            @if($currentLocation->current_activity)
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <span class="text-2xl">🗓️</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $currentLocation->current_activity }}</p>
                        @if($currentLocation->activity_until)
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Until {{ $currentLocation->activity_until->format('g:i A') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-6">
            <span class="text-4xl">📍</span>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Member location not set</p>
        </div>
    @endif

    {{-- Update Button --}}
    @if(!$showUpdateForm)
        <button wire:click="toggleUpdateForm"
            class="mt-4 w-full text-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium">
            Update Location
        </button>
    @endif

    {{-- Update Form --}}
    @if($showUpdateForm)
        <form wire:submit="updateLocation" class="mt-4 space-y-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Location</label>
                <select wire:model="location_name"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">Select location...</option>
                    @foreach($locationOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('location_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timezone</label>
                <select wire:model="timezone"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="America/New_York">Eastern</option>
                    <option value="America/Chicago">Central</option>
                    <option value="America/Denver">Mountain</option>
                    <option value="America/Los_Angeles">Pacific</option>
                    <option value="America/Anchorage">Alaska</option>
                    <option value="Pacific/Honolulu">Hawaii</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Current Activity (optional)</label>
                <input type="text" wire:model="current_activity"
                    placeholder="e.g., In committee hearing"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Until (optional)</label>
                <input type="datetime-local" wire:model="activity_until"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                    Update
                </button>
                <button type="button" wire:click="toggleUpdateForm"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 text-sm">
                    Cancel
                </button>
            </div>
        </form>
    @endif
</div>



