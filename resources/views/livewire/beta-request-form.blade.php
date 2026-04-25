<div>
    {{-- Modal Backdrop --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Backdrop --}}
            <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-80" wire:click="closeModal"></div>

            {{-- Modal Panel --}}
            <div class="relative inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white dark:bg-gray-800 shadow-xl rounded-2xl sm:p-8">
                
                @if(!$submitted)
                    {{-- Header --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Request Beta Access</h2>
                            <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            We're currently in beta and onboarding offices gradually. Tell us about yourself and we'll be in touch soon.
                        </p>
                    </div>

                    {{-- Form --}}
                    <form wire:submit="submit" class="space-y-5">
                        {{-- Full Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full name</label>
                            <input type="text" wire:model="full_name" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Your full name">
                            @error('full_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Work email</label>
                            <input type="email" wire:model="email" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="you@office.gov">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">We'll use this to contact you about access</p>
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Role Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">I am a...</label>
                            <div class="space-y-2">
                                @foreach($roleTypes as $value => $label)
                                <label class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 {{ $role_type === $value ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                    <input type="radio" wire:model.live="role_type" value="{{ $value }}" 
                                        class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('role_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Official Name (conditional) --}}
                        @if($role_type === 'staff_member')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Which elected official do you work for?</label>
                            <input type="text" wire:model="official_name" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., Senator Jane Smith">
                            @error('official_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        {{-- Government Level --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Level of government</label>
                            <select wire:model.live="government_level" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select...</option>
                                @foreach($governmentLevels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('government_level') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Government Level Other (conditional) --}}
                        @if($government_level === 'other')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Please specify</label>
                            <input type="text" wire:model="government_level_other" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="e.g., Tribal government">
                            @error('government_level_other') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        {{-- State & District --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State</label>
                                <select wire:model="state" 
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select...</option>
                                    @foreach($usStates as $abbr => $name)
                                        <option value="{{ $abbr }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('state') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">District <span class="text-gray-400">(optional)</span></label>
                                <input type="text" wire:model="district" 
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="e.g., CA-12">
                            </div>
                        </div>

                        {{-- Primary Interest --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">What brings you here?</label>
                            <select wire:model="primary_interest" 
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select...</option>
                                @foreach($primaryInterests as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('primary_interest') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Additional Info --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Anything else you'd like us to know? <span class="text-gray-400">(optional)</span>
                            </label>
                            <textarea wire:model="additional_info" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                placeholder="Tell us about your current challenges or what you're looking for..."
                                maxlength="500"></textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-right">{{ strlen($additional_info) }}/500</p>
                        </div>

                        {{-- Submit --}}
                        <div class="pt-4">
                            <button type="submit" 
                                class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Submit Request
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Success State --}}
                    <div class="text-center py-8">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Thanks for your interest!</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            We're onboarding offices gradually during our beta period. We'll review your request and be in touch at <strong class="text-gray-900 dark:text-white">{{ $submittedEmail }}</strong> soon.
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-500">
                            In the meantime, feel free to reach out to <a href="mailto:{{ config('mail.from.address', 'hello@legidash.com') }}" class="text-blue-600 hover:underline">{{ config('mail.from.address', 'hello@legidash.com') }}</a> with any questions.
                        </p>
                        <button wire:click="closeModal" 
                            class="mt-6 px-6 py-2.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Close
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>


