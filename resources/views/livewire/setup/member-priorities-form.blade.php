<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                @if($governmentLevel === 'local')
                    Official Priorities & Interests
                @elseif($governmentLevel === 'state')
                    Legislator Priorities & Interests
                @else
                    Member Priorities & Interests
                @endif
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Help us understand what matters most to {{ config('office.member_title', 'the Member') }} {{ config('office.member_name', '') }}</p>
            
            {{-- Level indicator --}}
            <div class="mt-3 inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium
                @if($governmentLevel === 'local') bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300
                @elseif($governmentLevel === 'state') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                @else bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                @endif">
                @if($governmentLevel === 'local')
                    🏛️ Local Government
                @elseif($governmentLevel === 'state')
                    🏛️ State Legislature
                @else
                    🏛️ Federal Office
                @endif
            </div>
        </div>

        {{-- Progress Tabs --}}
        <div class="mb-8">
            <div class="flex flex-wrap justify-center gap-2">
                @php
                    $districtLabel = match($governmentLevel) {
                        'local' => 'Community',
                        default => 'District',
                    };
                @endphp
                @foreach([
                    1 => ['icon' => '📋', 'label' => 'Policy'],
                    2 => ['icon' => '⚖️', 'label' => 'Positioning'],
                    3 => ['icon' => '👥', 'label' => $districtLabel],
                    4 => ['icon' => '📖', 'label' => 'Background'],
                    5 => ['icon' => '💬', 'label' => 'Communication'],
                    6 => ['icon' => '🎯', 'label' => 'Goals & AI'],
                ] as $num => $info)
                    <button 
                        wire:click="goToSection({{ $num }})"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all
                            {{ $currentSection === $num 
                                ? 'bg-indigo-600 text-white shadow-lg' 
                                : ($currentSection > $num 
                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' 
                                    : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-700') }}"
                    >
                        <span>{{ $info['icon'] }}</span>
                        <span class="hidden sm:inline">{{ $info['label'] }}</span>
                        @if($currentSection > $num)
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
                {{ session('message') }}
            </div>
        @endif

        {{-- Main Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden">
            {{-- Section Header --}}
            <div class="px-8 py-6 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">
                        {{ ['📋', '⚖️', '👥', '📖', '💬', '🎯'][$currentSection - 1] }}
                    </span>
                    <div>
                        <h2 class="text-xl font-semibold">{{ $sectionTitle }}</h2>
                        <p class="text-indigo-100 text-sm mt-1">{{ $sectionDescription }}</p>
                    </div>
                </div>
            </div>

            {{-- Section Content --}}
            <div class="p-8">
                {{-- Section 1: Policy Priorities --}}
                @if($currentSection === 1)
                    <div class="space-y-8">
                        {{-- Top Policy Areas --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Top Policy Areas
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">(drag to reorder priority)</span>
                            </h3>
                            
                            {{-- Add new --}}
                            <div class="flex gap-2 mb-4">
                                <select wire:model="new_policy_area" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Select a policy area...</option>
                                    @foreach($policyAreaOptions as $option)
                                        @if(!in_array($option, array_column($top_policy_areas, 'area')))
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button wire:click="addPolicyArea" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    Add
                                </button>
                            </div>

                            {{-- List --}}
                            <div class="space-y-3">
                                @forelse($top_policy_areas as $index => $area)
                                    <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <div class="flex flex-col gap-1">
                                            <button wire:click="movePolicyAreaUp({{ $index }})" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30" {{ $index === 0 ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                                            </button>
                                            <button wire:click="movePolicyAreaDown({{ $index }})" class="p-1 text-gray-400 hover:text-gray-600 disabled:opacity-30" {{ $index === count($top_policy_areas) - 1 ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                            </button>
                                        </div>
                                        <span class="w-8 h-8 flex items-center justify-center bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-bold">
                                            {{ $area['priority_rank'] }}
                                        </span>
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $area['area'] }}</p>
                                            <input 
                                                type="text" 
                                                wire:change="updatePolicyAreaNotes({{ $index }}, $event.target.value)"
                                                value="{{ $area['notes'] ?? '' }}"
                                                placeholder="Add notes (e.g., specific focus within this area)..."
                                                class="mt-1 w-full text-sm border-0 bg-transparent text-gray-500 dark:text-gray-400 placeholder-gray-400 focus:ring-0 p-0"
                                            >
                                        </div>
                                        <button wire:click="removePolicyArea({{ $index }})" class="p-2 text-red-400 hover:text-red-600 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No policy areas added yet. Select from the dropdown above.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Signature Issues --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Signature Issues</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                @if($governmentLevel === 'local')
                                    What specific issues does the official want to be known for in the community?
                                @elseif($governmentLevel === 'state')
                                    What specific issues does the legislator want to be known for in the legislature?
                                @else
                                    What specific issues does the Member want to be known for?
                                @endif
                            </p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_signature_issue" wire:keydown.enter="addItem('signature_issues', 'new_signature_issue')" placeholder="{{ $this->getPlaceholder('signature_issues') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('signature_issues', 'new_signature_issue')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($signature_issues as $index => $issue)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                        {{ $issue }}
                                        <button wire:click="removeItem('signature_issues', {{ $index }})" class="ml-1 hover:text-purple-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Emerging Interests --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Emerging Interests</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                New areas the {{ $memberTerm }} is exploring or developing expertise in
                            </p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_emerging_interest" wire:keydown.enter="addItem('emerging_interests', 'new_emerging_interest')" placeholder="{{ $this->getPlaceholder('emerging_interests') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('emerging_interests', 'new_emerging_interest')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($emerging_interests as $index => $interest)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                                        {{ $interest }}
                                        <button wire:click="removeItem('emerging_interests', {{ $index }})" class="ml-1 hover:text-amber-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 2: Political Positioning --}}
                @if($currentSection === 2)
                    <div class="space-y-8">
                        {{-- Skip Section Option --}}
                        <div class="bg-gray-50 dark:bg-gray-700/30 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        @if($governmentLevel === 'local')
                                            Many local offices are officially non-partisan. Skip this section if political positioning isn't relevant.
                                        @else
                                            This section is optional. Skip if you prefer not to characterize political positioning.
                                        @endif
                                    </p>
                                </div>
                                <button wire:click="skipPositioningSection" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                                    Skip This Section →
                                </button>
                            </div>
                        </div>

                        {{-- Governing Philosophy --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                @if($governmentLevel === 'local')
                                    Governing Approach
                                @else
                                    Governing Philosophy
                                @endif
                            </h3>
                            @if($governmentLevel === 'local')
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Select whichever best describes your approach, or skip if not applicable to your non-partisan role.
                                </p>
                            @endif
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($philosophyOptions as $value => $label)
                                    <label class="relative flex items-start p-4 cursor-pointer border-2 rounded-xl transition-all
                                        {{ $governing_philosophy === $value ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300' }}">
                                        <input type="radio" wire:model.live="governing_philosophy" value="{{ $value }}" class="sr-only">
                                        <div>
                                            <p class="font-medium {{ $governing_philosophy === $value ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-900 dark:text-white' }}">
                                                {{ explode(' — ', $label)[0] }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ explode(' — ', $label)[1] ?? '' }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Philosophy Description --}}
                        <div>
                            <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Describe in Their Own Words</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">How would the {{ $memberTerm }} describe their approach to governance?</p>
                            <textarea wire:model="philosophy_description" rows="3" placeholder="e.g., 'I believe in finding common ground where we can, but standing firm on principles that matter...'" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        {{-- Non-Negotiables --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">"Red Line" Issues</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Positions the {{ $memberTerm }} will not compromise on</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_non_negotiable" wire:keydown.enter="addItem('non_negotiables', 'new_non_negotiable')" placeholder="{{ $this->getPlaceholder('non_negotiables') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('non_negotiables', 'new_non_negotiable')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($non_negotiables as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full">
                                        🚫 {{ $item }}
                                        <button wire:click="removeItem('non_negotiables', {{ $index }})" class="ml-1 hover:text-red-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Bipartisan/Coalition Openings --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $this->getLabel('bipartisan_label') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $this->getLabel('bipartisan_helper') }}</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_bipartisan_opening" wire:keydown.enter="addItem('bipartisan_openings', 'new_bipartisan_opening')" placeholder="{{ $this->getPlaceholder('bipartisan_openings') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('bipartisan_openings', 'new_bipartisan_opening')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($bipartisan_openings as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                        🤝 {{ $item }}
                                        <button wire:click="removeItem('bipartisan_openings', {{ $index }})" class="ml-1 hover:text-purple-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 3: District Focus --}}
                @if($currentSection === 3)
                    <div class="space-y-8">
                        {{-- Key Demographics --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $this->getLabel('demographics_label') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $this->getLabel('demographics_helper') }}</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_demographic" wire:keydown.enter="addItem('key_demographics', 'new_demographic')" placeholder="{{ $this->getPlaceholder('key_demographics') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('key_demographics', 'new_demographic')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($key_demographics as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full">
                                        {{ $item }}
                                        <button wire:click="removeItem('key_demographics', {{ $index }})" class="ml-1 hover:text-blue-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Economic Priorities --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $this->getLabel('economic_label') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Key industries, job sectors, or economic development focuses</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_economic_priority" wire:keydown.enter="addItem('economic_priorities', 'new_economic_priority')" placeholder="{{ $this->getPlaceholder('economic_priorities') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('economic_priorities', 'new_economic_priority')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($economic_priorities as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                        💼 {{ $item }}
                                        <button wire:click="removeItem('economic_priorities', {{ $index }})" class="ml-1 hover:text-green-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Constituent Concerns --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                @if($governmentLevel === 'local')
                                    Top Resident Concerns
                                @else
                                    Top Constituent Concerns
                                @endif
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">What do constituents most frequently raise?</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_constituent_concern" wire:keydown.enter="addItem('constituent_concerns', 'new_constituent_concern')" placeholder="{{ $this->getPlaceholder('constituent_concerns') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('constituent_concerns', 'new_constituent_concern')" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($constituent_concerns as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                                        📢 {{ $item }}
                                        <button wire:click="removeItem('constituent_concerns', {{ $index }})" class="ml-1 hover:text-amber-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 4: Personal Background --}}
                @if($currentSection === 4)
                    <div class="space-y-8">
                        {{-- Professional Background --}}
                        <div>
                            <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Professional Background</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Career experience that shapes the {{ $memberTerm }}'s perspective</p>
                            <textarea wire:model="professional_background" rows="4" placeholder="{{ $this->getPlaceholder('professional_background') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        {{-- State Legislature: Other Occupation --}}
                        @if($governmentLevel === 'state')
                            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                                <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Primary Occupation (Outside Legislature)</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">If this is a part-time legislature, what is the legislator's primary occupation?</p>
                                <input type="text" wire:model="other_occupation" placeholder="e.g., Attorney, Teacher, Business Owner, Farmer" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </div>
                        @endif

                        {{-- Formative Experiences --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Formative Experiences</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Life experiences that significantly shaped the {{ $memberTerm }}'s views</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_formative_experience" wire:keydown.enter="addItem('formative_experiences', 'new_formative_experience')" placeholder="{{ $this->getPlaceholder('formative_experiences') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('formative_experiences', 'new_formative_experience')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="space-y-2">
                                @foreach($formative_experiences as $index => $item)
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <span class="text-xl">📖</span>
                                        <span class="flex-1 text-gray-700 dark:text-gray-300">{{ $item }}</span>
                                        <button wire:click="removeItem('formative_experiences', {{ $index }})" class="text-red-400 hover:text-red-600">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Personal Connections --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Personal Connections to Issues</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Personal ties that make certain issues meaningful</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_personal_connection" wire:keydown.enter="addItem('personal_connections', 'new_personal_connection')" placeholder="{{ $this->getPlaceholder('personal_connections') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('personal_connections', 'new_personal_connection')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="space-y-2">
                                @foreach($personal_connections as $index => $item)
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                        <span class="text-xl">❤️</span>
                                        <span class="flex-1 text-gray-700 dark:text-gray-300">{{ $item }}</span>
                                        <button wire:click="removeItem('personal_connections', {{ $index }})" class="text-red-400 hover:text-red-600">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 5: Communication Style --}}
                @if($currentSection === 5)
                    <div class="space-y-8">
                        {{-- Preferred Tone --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Preferred Communication Tone</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($toneOptions as $value => $label)
                                    <label class="relative flex items-start p-4 cursor-pointer border-2 rounded-xl transition-all
                                        {{ $preferred_tone === $value ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-indigo-300' }}">
                                        <input type="radio" wire:model.live="preferred_tone" value="{{ $value }}" class="sr-only">
                                        <div>
                                            <p class="font-medium {{ $preferred_tone === $value ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-900 dark:text-white' }}">
                                                {{ explode(' — ', $label)[0] }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ explode(' — ', $label)[1] ?? '' }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Key Phrases --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Key Phrases & Language</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Phrases the {{ $memberTerm }} likes to use or that resonate with their style</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_key_phrase" wire:keydown.enter="addItem('key_phrases', 'new_key_phrase')" placeholder="{{ $this->getPlaceholder('key_phrases') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('key_phrases', 'new_key_phrase')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($key_phrases as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full">
                                        "{{ $item }}"
                                        <button wire:click="removeItem('key_phrases', {{ $index }})" class="ml-1 hover:text-indigo-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Topics to Emphasize --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Topics to Emphasize</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Topics the {{ $memberTerm }} loves to discuss</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_topic_emphasize" wire:keydown.enter="addItem('topics_to_emphasize', 'new_topic_emphasize')" placeholder="{{ $this->getPlaceholder('topics_emphasize') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('topics_to_emphasize', 'new_topic_emphasize')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($topics_to_emphasize as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full">
                                        ✅ {{ $item }}
                                        <button wire:click="removeItem('topics_to_emphasize', {{ $index }})" class="ml-1 hover:text-green-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Topics to Avoid --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Topics to Avoid or Handle Carefully</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Sensitive topics that require special care</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_topic_avoid" wire:keydown.enter="addItem('topics_to_avoid', 'new_topic_avoid')" placeholder="e.g., Personal family matters, Specific past controversies" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('topics_to_avoid', 'new_topic_avoid')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($topics_to_avoid as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full">
                                        ⚠️ {{ $item }}
                                        <button wire:click="removeItem('topics_to_avoid', {{ $index }})" class="ml-1 hover:text-red-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Section 6: Goals & AI Settings --}}
                @if($currentSection === 6)
                    <div class="space-y-8">
                        {{-- State Legislature: Session Type --}}
                        @if($governmentLevel === 'state')
                            <div class="bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                                <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Legislative Session Type</label>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">How does your state's legislative session affect your work?</p>
                                <select wire:model="session_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Select session type...</option>
                                    @foreach($sessionTypeOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">State-Federal Issues</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Federal issues or agencies particularly relevant to your state work</p>
                                
                                <div class="flex gap-2 mb-3">
                                    <input type="text" wire:model="new_state_federal_issue" wire:keydown.enter="addItem('state_federal_issues', 'new_state_federal_issue')" placeholder="e.g., Medicaid waiver negotiations, EPA regulations, Military bases" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <button wire:click="addItem('state_federal_issues', 'new_state_federal_issue')" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">Add</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($state_federal_issues as $index => $item)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 rounded-full">
                                            🏛️ {{ $item }}
                                            <button wire:click="removeItem('state_federal_issues', {{ $index }})" class="ml-1 hover:text-amber-900">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Local Government: Role & Structure --}}
                        @if($governmentLevel === 'local')
                            <div class="bg-teal-50 dark:bg-teal-900/20 rounded-lg p-4 border border-teal-200 dark:border-teal-800 space-y-4">
                                <div>
                                    <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Role Type</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">What best describes your role?</p>
                                    <select wire:model="local_role_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select role type...</option>
                                        @foreach($localRoleOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Governance Structure</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">How is your local government structured?</p>
                                    <select wire:model="governance_structure" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select structure...</option>
                                        @foreach($governanceStructureOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Relationship with Administration</label>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">How would you describe your working relationship with the city/county manager or administration?</p>
                                    <select wire:model="admin_relationship" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select relationship type...</option>
                                        @foreach($adminRelationshipOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Boards & Commissions</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Boards or commissions you serve on or oversee</p>
                                
                                <div class="flex gap-2 mb-3">
                                    <input type="text" wire:model="new_board_commission" wire:keydown.enter="addItem('boards_commissions', 'new_board_commission')" placeholder="e.g., Planning Commission liaison, Parks Board, Housing Authority" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <button wire:click="addItem('boards_commissions', 'new_board_commission')" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">Add</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($boards_commissions as $index => $item)
                                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-full">
                                            📋 {{ $item }}
                                            <button wire:click="removeItem('boards_commissions', {{ $index }})" class="ml-1 hover:text-teal-900">×</button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Term Goals --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">This Term Goals</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $this->getLabel('term_goals_helper') }}</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_term_goal" wire:keydown.enter="addItem('term_goals', 'new_term_goal')" placeholder="{{ $this->getPlaceholder('term_goals') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('term_goals', 'new_term_goal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Add</button>
                            </div>
                            <div class="space-y-2">
                                @foreach($term_goals as $index => $item)
                                    <div class="flex items-center gap-3 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                        <span class="text-xl">🎯</span>
                                        <span class="flex-1 text-gray-700 dark:text-gray-300">{{ $item }}</span>
                                        <button wire:click="removeItem('term_goals', {{ $index }})" class="text-red-400 hover:text-red-600">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Long Term Vision --}}
                        <div>
                            <label class="block text-lg font-semibold text-gray-900 dark:text-white mb-2">Long-Term Vision</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Where does the {{ $memberTerm }} want to take their career and impact?</p>
                            <textarea wire:model="long_term_vision" rows="3" placeholder="{{ $this->getPlaceholder('long_term_vision') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                        </div>

                        {{-- Legacy Items --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Legacy Items</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">What does the {{ $memberTerm }} want to be remembered for?</p>
                            
                            <div class="flex gap-2 mb-3">
                                <input type="text" wire:model="new_legacy_item" wire:keydown.enter="addItem('legacy_items', 'new_legacy_item')" placeholder="{{ $this->getPlaceholder('legacy_items') }}" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <button wire:click="addItem('legacy_items', 'new_legacy_item')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">Add</button>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($legacy_items as $index => $item)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full">
                                        🏆 {{ $item }}
                                        <button wire:click="removeItem('legacy_items', {{ $index }})" class="ml-1 hover:text-purple-900">×</button>
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- AI Settings --}}
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                                <span class="text-xl">🤖</span> AI System Settings
                            </h3>
                            
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6 space-y-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" wire:model="use_in_prompts" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">Use this information in AI prompts</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">When enabled, AI features will use these priorities to provide more relevant suggestions</p>
                                    </div>
                                </label>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Additional Notes for AI Context</label>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Any special instructions or context for the AI system</p>
                                    <div class="flex gap-2 mb-3">
                                        <input type="text" wire:model="new_ai_note" wire:keydown.enter="addItem('ai_context_notes', 'new_ai_note')" placeholder="e.g., Always mention military service when discussing defense" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                                        <button wire:click="addItem('ai_context_notes', 'new_ai_note')" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm">Add</button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($ai_context_notes as $index => $item)
                                            <div class="flex items-center gap-2 p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                                <span class="text-gray-400">💡</span>
                                                <span class="flex-1 text-sm text-gray-700 dark:text-gray-300">{{ $item }}</span>
                                                <button wire:click="removeItem('ai_context_notes', {{ $index }})" class="text-red-400 hover:text-red-600">×</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer Navigation --}}
            <div class="px-8 py-6 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                @if($currentSection > 1)
                    <button wire:click="previousSection" class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                        ← Previous
                    </button>
                @else
                    <a href="{{ route('admin.settings') }}" class="px-6 py-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        Cancel
                    </a>
                @endif

                <div class="flex gap-3">
                    <button wire:click="saveAndContinue" class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        Save Progress
                    </button>

                    @if($currentSection < $totalSections)
                        <button wire:click="nextSection" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Next →
                        </button>
                    @else
                        <button wire:click="completeForm" class="px-8 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold">
                            ✅ Complete
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Help Text --}}
        <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>This information helps personalize the Member Hub and AI suggestions. You can update it anytime.</p>
        </div>
    </div>
</div>

