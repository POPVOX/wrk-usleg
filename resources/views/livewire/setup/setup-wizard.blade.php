<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:py-10">
        {{-- Header --}}
        <div class="mb-6 text-center sm:mb-8">
            <h1 class="mb-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400 sm:text-3xl">Member Hub Setup</h1>
            <p class="mx-auto max-w-2xl text-sm text-gray-600 dark:text-gray-400 sm:text-base">Configure your office in a few short steps. The flow is optimized for quick mobile entry and can be updated later.</p>
        </div>

        {{-- Progress Bar --}}
        <div class="mb-6 overflow-x-auto pb-2 sm:mb-8">
            <div class="mb-2 flex min-w-[36rem] items-center justify-between sm:min-w-0">
                @for($i = 1; $i <= $totalSteps; $i++)
                    <div class="flex flex-col items-center {{ $i <= $currentStep ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-400 dark:text-gray-500' }}">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white
                            {{ $i < $currentStep ? 'bg-green-500' : ($i === $currentStep ? 'bg-indigo-600 ring-4 ring-indigo-200 dark:ring-indigo-800' : 'bg-gray-300 dark:bg-gray-600') }}">
                            @if($i < $currentStep)
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                        <span class="text-xs mt-1 hidden sm:block {{ $i <= $currentStep ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400 dark:text-gray-500' }}">
                            {{ ['Basic Info', 'Verify', 'News', 'Import', 'Launch'][$i-1] }}
                        </span>
                    </div>
                    @if($i < $totalSteps)
                        <div class="flex-1 h-1 mx-2 rounded {{ $i < $currentStep ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                    @endif
                @endfor
            </div>
        </div>

        {{-- Card --}}
        <div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Card Header --}}
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 dark:border-zinc-700 dark:bg-zinc-700/50 sm:px-8 sm:py-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Step {{ $currentStep }} of {{ $totalSteps }}: {{ $stepTitle }}
                </h2>
            </div>

            {{-- Card Body --}}
            <div class="p-4 sm:p-8">
                {{-- Step 1: Basic Information --}}
                @if($currentStep === 1)
                    <div class="space-y-6">
                        {{-- Level --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Level of Office</label>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach(['federal' => 'Federal', 'state' => 'State', 'local' => 'Local'] as $value => $label)
                                    <label class="relative flex items-center justify-center p-4 cursor-pointer border-2 rounded-xl transition-colors
                                        {{ $level === $value ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-zinc-600 hover:border-indigo-300' }}">
                                        <input type="radio" wire:model.live="level" value="{{ $value }}" class="sr-only">
                                        <span class="{{ $level === $value ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-700 dark:text-gray-300' }} font-medium">
                                            {{ $label }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Federal: Search with Congress API --}}
                        @if($level === 'federal')
                            {{-- API Key Notice --}}
                            @if(!config('office.congress_api.key'))
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Congress API Key Not Configured</p>
                                            <p class="text-sm text-blue-700 dark:text-blue-400 mt-1">
                                                To enable member search, add <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">CONGRESS_API_KEY=your_key</code> to your <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">.env</code> file.
                                                <a href="https://api.congress.gov/sign-up/" target="_blank" class="underline hover:no-underline">Get a free API key →</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Search --}}
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-6">
                                <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">
                                    Quick Search (start typing to find members of Congress)
                                </label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.400ms="search_query"
                                        placeholder="Type a name... (e.g., 'Seth Moulton' or 'Moulton')"
                                        class="w-full rounded-lg border-indigo-200 dark:border-indigo-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white pl-10"
                                        {{ config('office.congress_api.key') ? '' : 'disabled' }}
                                    >
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg wire:loading.remove wire:target="search_query" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg wire:loading wire:target="search_query" class="w-5 h-5 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Search Results (autocomplete dropdown) --}}
                                @if(!empty($search_results))
                                    <div class="mt-2 bg-white dark:bg-zinc-700 rounded-lg shadow-lg border border-gray-200 dark:border-zinc-600 max-h-64 overflow-y-auto">
                                        @foreach($search_results as $index => $result)
                                            <button 
                                                wire:click="selectMember({{ $index }})"
                                                class="w-full text-left px-4 py-3 hover:bg-indigo-50 dark:hover:bg-indigo-800 transition-colors border-b border-gray-100 dark:border-zinc-600 last:border-0"
                                            >
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $result['name'] }}</p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $result['party'] }} - {{ $result['state'] }}{{ $result['district'] ? '-'.$result['district'] : '' }}
                                                </p>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif(strlen($search_query) >= 2 && empty($search_results) && config('office.congress_api.key'))
                                    <div wire:loading.remove wire:target="search_query" class="mt-2 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-sm text-amber-700 dark:text-amber-300">
                                        No members found for "{{ $search_query }}". Try a different spelling or enter details manually below.
                                    </div>
                                @endif
                            </div>

                            <div class="text-center text-gray-500 dark:text-gray-400">— or enter manually —</div>
                        @else
                            {{-- State/Local: Manual entry only --}}
                            <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong class="text-gray-900 dark:text-white">{{ $level === 'state' ? 'State Legislature' : 'Local Government' }} Setup</strong><br>
                                        Please enter the official's information manually below.
                                        {{ $level === 'state' ? 'We don\'t currently have API access for state legislatures.' : 'We don\'t currently have API access for local officials.' }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Name --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">First Name *</label>
                                <input type="text" wire:model="first_name" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                @error('first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Last Name *</label>
                                <input type="text" wire:model="last_name" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Title & Party --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                                <select wire:model="title" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    <option value="Representative">Representative</option>
                                    <option value="Senator">Senator</option>
                                    <option value="State Representative">State Representative</option>
                                    <option value="State Senator">State Senator</option>
                                    <option value="Mayor">Mayor</option>
                                    <option value="Council Member">Council Member</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Party</label>
                                <select wire:model="party" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    <option value="">Select...</option>
                                    <option value="Democratic">Democratic</option>
                                    <option value="Republican">Republican</option>
                                    <option value="Independent">Independent</option>
                                </select>
                            </div>
                        </div>

                        {{-- State & District --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State *</label>
                                <select wire:model="state" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    <option value="">Select State...</option>
                                    @foreach($states as $abbr => $name)
                                        <option value="{{ $abbr }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">District Number</label>
                                <input type="text" wire:model="district_number" placeholder="e.g., 6" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 2: Verify Information --}}
                @if($currentStep === 2)
                    <div class="space-y-6">
                        @if($level === 'federal' && $verified_member)
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center space-x-3">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-green-800 dark:text-green-200 font-medium">
                                    Found public record for {{ $title }} {{ $first_name }} {{ $last_name }}!
                                </span>
                            </div>

                            {{-- Official Info (Federal) --}}
                            <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Official Information</h3>
                                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Name:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $verified_member['full_name'] ?? $first_name.' '.$last_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Party:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $verified_member['party'] ?? $party }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">State:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $verified_member['state_name'] ?? $state }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">District:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $district_number }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Bioguide ID:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white font-mono">{{ $verified_member['bioguide_id'] ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">First Elected:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $verified_member['first_elected'] ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        @elseif($level === 'federal' && !$verified_member)
                            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                                <p class="text-amber-800 dark:text-amber-200">
                                    Could not find public record. You can still continue with manual setup.
                                </p>
                            </div>
                        @else
                            {{-- State/Local: Show summary of entered info --}}
                            <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Confirm Information</h3>
                                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Name:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $title }} {{ $first_name }} {{ $last_name }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Party:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $party ?: 'Not specified' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">State:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $states[$state] ?? $state }}</span>
                                    </div>
                                    @if($district_number)
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">District:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $district_number }}</span>
                                    </div>
                                    @endif
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Level:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ $level === 'state' ? 'State Legislature' : 'Local Government' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- District Geography (Federal only) --}}
                        @if($level === 'federal' && $district_geography && !empty($district_geography['cities']))
                            <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6">
                                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">District Geography ({{ $state }}-{{ $district_number }})</h3>
                                <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Counties:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">{{ implode(', ', $district_geography['counties'] ?? []) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Population:</span>
                                        <span class="ml-2 text-gray-900 dark:text-white">~{{ number_format($district_geography['population'] ?? 0) }}</span>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Major Cities:</span>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach($district_geography['cities'] ?? [] as $city)
                                            <span class="px-2 py-1 bg-white dark:bg-zinc-600 rounded text-sm text-gray-700 dark:text-gray-300">{{ $city }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Office Information --}}
                        <div class="space-y-4">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Office Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ $level === 'federal' ? 'DC Office Address' : ($level === 'state' ? 'Capitol Office Address' : 'Main Office Address') }}
                                    </label>
                                    <input type="text" wire:model="dc_address" placeholder="{{ $level === 'federal' ? 'e.g., 123 Cannon HOB' : 'Office address' }}" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ $level === 'federal' ? 'DC Phone' : 'Office Phone' }}
                                    </label>
                                    <input type="text" wire:model="dc_phone" placeholder="(555) 555-5555" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Official Website</label>
                                <input type="url" wire:model="official_website" placeholder="https://..." class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 3: News Sources --}}
                @if($currentStep === 3)
                    <div class="space-y-6">
                        <p class="text-gray-600 dark:text-gray-400">
                            We've identified relevant news sources for {{ $state }}-{{ $district_number }}. Select which sources to monitor:
                        </p>

                        @php
                            $categories = [
                                'national' => 'National Political',
                                'state' => 'State News',
                                'local' => 'Local/Regional',
                                'trade' => 'Trade Press',
                            ];
                        @endphp

                        @foreach($categories as $category => $label)
                            @php
                                $categorySources = array_filter($suggested_sources, fn($s, $i) => ($s['category'] ?? '') === $category, ARRAY_FILTER_USE_BOTH);
                            @endphp
                            @if(!empty($categorySources))
                                <div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ $label }}</h3>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        @foreach($suggested_sources as $index => $source)
                                            @if(($source['category'] ?? '') === $category)
                                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors
                                                    {{ in_array($index, $selected_sources) ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-zinc-600 hover:border-indigo-300' }}">
                                                    <input 
                                                        type="checkbox" 
                                                        wire:click="toggleSource({{ $index }})"
                                                        {{ in_array($index, $selected_sources) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    >
                                                    <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">{{ $source['name'] }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Social Media --}}
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Social Media (Optional)</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Twitter/X</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500">@</span>
                                        <input type="text" wire:model="social_media.twitter" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Facebook</label>
                                    <input type="text" wire:model="social_media.facebook" placeholder="Page or profile URL" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Instagram</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500">@</span>
                                        <input type="text" wire:model="social_media.instagram" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">YouTube</label>
                                    <input type="text" wire:model="social_media.youtube" placeholder="Channel URL" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">LinkedIn</label>
                                    <input type="text" wire:model="social_media.linkedin" placeholder="Profile URL" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Bluesky</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500">@</span>
                                        <input type="text" wire:model="social_media.bluesky" placeholder="handle.bsky.social" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">TikTok</label>
                                    <div class="flex">
                                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500">@</span>
                                        <input type="text" wire:model="social_media.tiktok" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 4: Import --}}
                @if($currentStep === 4)
                    <div class="space-y-6">
                        @if($level === 'federal')
                            <p class="text-gray-600 dark:text-gray-400">
                                Select which public records to import from Congress.gov:
                            </p>

                            <div class="space-y-3">
                                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition-colors
                                    {{ $import_biography ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-zinc-600' }}">
                                    <input type="checkbox" wire:model="import_biography" class="mt-1 rounded border-gray-300 text-indigo-600">
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Biographical Information</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Official biography from Congress.gov</p>
                                    </div>
                                </label>

                                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition-colors
                                    {{ $import_legislation ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-zinc-600' }}">
                                    <input type="checkbox" wire:model="import_legislation" class="mt-1 rounded border-gray-300 text-indigo-600">
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Legislative Record</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Bills sponsored/cosponsored, voting record</p>
                                    </div>
                                </label>

                                <label class="flex items-start p-4 border rounded-xl cursor-pointer transition-colors opacity-50
                                    {{ $import_statements ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-zinc-600' }}">
                                    <input type="checkbox" wire:model="import_statements" class="mt-1 rounded border-gray-300 text-indigo-600" disabled>
                                    <div class="ml-3">
                                        <p class="font-medium text-gray-900 dark:text-white">Public Statements</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Floor speeches, press releases (coming soon)</p>
                                    </div>
                                </label>
                            </div>

                            @if(!empty($import_results))
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                                    <h4 class="font-medium text-green-800 dark:text-green-200 mb-2">Import Complete!</h4>
                                    @if(isset($import_results['biography']))
                                        <p class="text-sm text-green-700 dark:text-green-300">✅ Biography imported</p>
                                    @endif
                                    @if(isset($import_results['legislation']))
                                        <p class="text-sm text-green-700 dark:text-green-300">
                                            ✅ {{ $import_results['legislation']['sponsored'] }} sponsored + {{ $import_results['legislation']['cosponsored'] }} cosponsored bills
                                        </p>
                                    @endif
                                </div>
                            @endif
                        @else
                            {{-- State/Local: Legislative activity URL --}}
                            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-800 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-indigo-800 dark:text-indigo-200 mb-2">
                                            Legislative Activity URL (Optional)
                                        </h4>
                                        <p class="text-indigo-700 dark:text-indigo-300 text-sm mb-4">
                                            Many {{ $level === 'state' ? 'state legislatures' : 'local governments' }} have public pages listing sponsored legislation. 
                                            If available, paste the URL and we can periodically check for updates.
                                        </p>

                                        {{-- URL Type Selection --}}
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-2">Source Type</label>
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                                @foreach([
                                                    'state_legislature' => 'State Legislature',
                                                    'city_council' => 'City/County Portal',
                                                    'custom' => 'Other Website'
                                                ] as $value => $label)
                                                    <label class="relative flex items-center p-3 cursor-pointer border-2 rounded-lg transition-colors
                                                        {{ $legislative_url_type === $value ? 'border-indigo-500 bg-white dark:bg-indigo-900/30' : 'border-indigo-200 dark:border-indigo-700 bg-white/50 dark:bg-indigo-900/10 hover:border-indigo-400' }}">
                                                        <input type="radio" wire:model.live="legislative_url_type" value="{{ $value }}" class="sr-only">
                                                        <span class="text-sm {{ $legislative_url_type === $value ? 'text-indigo-700 dark:text-indigo-200 font-medium' : 'text-indigo-600 dark:text-indigo-400' }}">
                                                            {{ $label }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- URL Input --}}
                                        <div>
                                            <label class="block text-sm font-medium text-indigo-700 dark:text-indigo-300 mb-1">
                                                Sponsor/Activity Page URL
                                            </label>
                                            <input 
                                                type="url" 
                                                wire:model="legislative_url"
                                                placeholder="https://wapp.capitol.tn.gov/apps/sponsorlist/default.aspx?ID=H550&ga=114"
                                                class="w-full rounded-lg border-indigo-200 dark:border-indigo-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400"
                                            >
                                            <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">
                                                This is the page where the official's sponsored legislation is listed
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Examples of supported sources --}}
                            @if($level === 'state')
                            <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Examples of supported state legislature sites:</p>
                                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                    <li>• <strong>Tennessee:</strong> capitol.tn.gov/apps/sponsorlist/</li>
                                    <li>• <strong>California:</strong> leginfo.legislature.ca.gov</li>
                                    <li>• <strong>New York:</strong> nysenate.gov/legislation</li>
                                    <li>• <strong>Texas:</strong> capitol.texas.gov/Members/</li>
                                    <li class="text-gray-500 dark:text-gray-500 italic">Most state legislature websites have similar pages</li>
                                </ul>
                            </div>
                            @endif

                            {{-- Social Media for Activity --}}
                            <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl p-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="text-xl">📱</span> Social Media Activity
                                </h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4">
                                    We can monitor these accounts for public statements and announcements.
                                </p>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Twitter/X</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500 text-sm">@</span>
                                            <input type="text" wire:model="social_media.twitter" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Bluesky</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500 text-sm">@</span>
                                            <input type="text" wire:model="social_media.bluesky" placeholder="handle.bsky.social" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Facebook</label>
                                        <input type="text" wire:model="social_media.facebook" placeholder="Page name or URL" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Instagram</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500 text-sm">@</span>
                                            <input type="text" wire:model="social_media.instagram" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">TikTok</label>
                                        <div class="flex">
                                            <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-zinc-600 border border-r-0 border-gray-300 dark:border-zinc-500 rounded-l-lg text-gray-500 text-sm">@</span>
                                            <input type="text" wire:model="social_media.tiktok" placeholder="handle" class="flex-1 rounded-r-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">LinkedIn</label>
                                        <input type="text" wire:model="social_media.linkedin" placeholder="Profile URL" class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white text-sm">
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                    <p class="text-xs text-amber-700 dark:text-amber-300">
                                        <strong>Note:</strong> Social media monitoring helps track public statements but requires API access. 
                                        Coming soon: Automatic detection of legislative announcements from social posts.
                                    </p>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-4">
                                <p class="text-gray-600 dark:text-gray-400 text-sm">
                                    <strong class="text-gray-900 dark:text-white">💡 Tip:</strong> 
                                    You can always add or update this information later in Office Settings.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Step 5: Review & Launch --}}
                @if($currentStep === 5)
                    <div class="space-y-6">
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6 text-center">
                            <div class="w-16 h-16 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-green-800 dark:text-green-200">Setup Complete!</h3>
                            <p class="text-green-700 dark:text-green-300 mt-2">Your Member Hub is ready to launch.</p>
                        </div>

                        <div class="bg-gray-50 dark:bg-zinc-700/50 rounded-xl p-6 space-y-4">
                            <h4 class="font-semibold text-gray-900 dark:text-white">Configuration Summary</h4>
                            
                            <div class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Member:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ $title }} {{ $first_name }} {{ $last_name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">
                                        {{ $level === 'federal' ? 'District:' : ($level === 'state' ? 'State District:' : 'Jurisdiction:') }}
                                    </span>
                                    <span class="ml-2 text-gray-900 dark:text-white">
                                        {{ $state }}{{ $district_number ? '-'.$district_number : '' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Level:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">
                                        {{ $level === 'federal' ? 'U.S. Congress' : ($level === 'state' ? 'State Legislature' : 'Local Government') }}
                                    </span>
                                </div>
                                @if($level === 'federal' && ($verified_member['bioguide_id'] ?? null))
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Bioguide ID:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white font-mono">{{ $verified_member['bioguide_id'] }}</span>
                                </div>
                                @endif
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">News Sources:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ count($selected_sources) }} configured</span>
                                </div>
                                @php
                                    $configuredSocial = array_filter($social_media);
                                @endphp
                                @if(count($configuredSocial) > 0)
                                <div>
                                    <span class="text-gray-500 dark:text-gray-400">Social Media:</span>
                                    <span class="ml-2 text-gray-900 dark:text-white">{{ count($configuredSocial) }} accounts</span>
                                </div>
                                @endif
                            </div>

                            @if($level === 'federal' && !empty($import_results) && !isset($import_results['skipped']))
                                <div class="pt-4 border-t border-gray-200 dark:border-zinc-600">
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Imported Data</h5>
                                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                        @if(isset($import_results['biography']))
                                            <li>✅ 1 biography document</li>
                                        @endif
                                        @if(isset($import_results['legislation']))
                                            <li>✅ {{ $import_results['legislation']['sponsored'] + $import_results['legislation']['cosponsored'] }} bills</li>
                                        @endif
                                    </ul>
                                </div>
                            @endif

                            @if($level !== 'federal')
                                <div class="pt-4 border-t border-gray-200 dark:border-zinc-600">
                                    <h5 class="font-medium text-gray-900 dark:text-white mb-2">Data Sources Configured</h5>
                                    <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                        @if($legislative_url)
                                            <li class="flex items-center gap-2">
                                                <span class="text-green-500">✅</span>
                                                <span>Legislative activity URL configured</span>
                                            </li>
                                        @else
                                            <li class="flex items-center gap-2 text-gray-400">
                                                <span>⏭️</span>
                                                <span>No legislative URL (can add later)</span>
                                            </li>
                                        @endif
                                        @php $socialCount = count(array_filter($social_media)); @endphp
                                        @if($socialCount > 0)
                                            <li class="flex items-center gap-2">
                                                <span class="text-green-500">✅</span>
                                                <span>{{ $socialCount }} social media account{{ $socialCount > 1 ? 's' : '' }} configured</span>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            @endif
                        </div>

                        @if($level !== 'federal')
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-xl">🚀</span>
                                <div>
                                    <p class="text-green-800 dark:text-green-200 font-medium">Ready to launch!</p>
                                    <p class="text-green-700 dark:text-green-300 text-sm mt-1">
                                        @if($legislative_url)
                                            We'll periodically check your legislative activity URL for updates.
                                        @else
                                            You can add legislative activity manually through the Member Hub, or configure a URL later in Office Settings.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Member Priorities Suggestion --}}
                        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4">
                            <div class="flex items-start gap-3">
                                <span class="text-xl">📋</span>
                                <div>
                                    <p class="text-indigo-800 dark:text-indigo-200 font-medium">Next: Set Up Member Priorities</p>
                                    <p class="text-indigo-700 dark:text-indigo-300 text-sm mt-1">
                                        After launching, we recommend filling out the Member Priorities questionnaire. 
                                        This helps personalize the Member Hub and AI suggestions based on policy interests, 
                                        communication style, and goals.
                                    </p>
                                    <a href="{{ route('setup.priorities') }}" class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 text-sm font-medium mt-2 hover:underline">
                                        Go to Member Priorities →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Card Footer --}}
            <div class="flex flex-col-reverse gap-3 border-t border-gray-200 bg-gray-50 px-4 py-4 dark:border-zinc-700 dark:bg-zinc-700/50 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-6">
                @if($currentStep > 1)
                    <button 
                        wire:click="previousStep"
                        class="w-full rounded-lg border border-gray-200 px-6 py-3 text-center text-gray-700 transition-colors hover:text-gray-900 dark:border-zinc-600 dark:text-gray-300 dark:hover:text-white sm:w-auto sm:border-0 sm:px-0 sm:py-2"
                    >
                        ← Back
                    </button>
                @else
                    <div></div>
                @endif

                @if($currentStep < $totalSteps)
                    <button 
                        wire:click="nextStep"
                        wire:loading.attr="disabled"
                        class="w-full rounded-lg bg-indigo-600 px-6 py-3 text-white transition-colors hover:bg-indigo-700 disabled:opacity-50 sm:w-auto sm:py-2"
                    >
                        <span wire:loading.remove wire:target="nextStep">Next →</span>
                        <span wire:loading wire:target="nextStep">Processing...</span>
                    </button>
                @else
                    <button 
                        wire:click="completeSetup"
                        class="w-full rounded-lg bg-green-600 px-8 py-3 font-semibold text-white transition-colors hover:bg-green-700 sm:w-auto"
                    >
                        🚀 Launch Member Hub
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
