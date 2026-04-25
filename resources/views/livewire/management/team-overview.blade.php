<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Overview</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">See who's covering what across your office.</p>
            </div>

            {{-- Filters & Sort --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex flex-wrap gap-4 items-center">
                    {{-- Role Filter --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                        <select wire:model.live="filterRole" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">All Roles</option>
                            @foreach($availableRoles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Location Filter --}}
                    @if(count($availableLocations) > 0)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Location</label>
                        <select wire:model.live="filterLocation" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                            <option value="">All Locations</option>
                            @foreach($availableLocations as $location)
                                <option value="{{ $location }}">{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    {{-- Sort --}}
                    <div class="ml-auto flex items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Sort by:</span>
                        <button wire:click="sort('name')" class="px-3 py-1.5 rounded text-sm {{ $sortBy === 'name' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Name
                            @if($sortBy === 'name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                        <button wire:click="sort('issues')" class="px-3 py-1.5 rounded text-sm {{ $sortBy === 'issues' ? 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            Issues
                            @if($sortBy === 'issues')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            {{-- Unassigned Issues Alert --}}
            @if($unassignedIssues->count() > 0)
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <p class="font-medium text-amber-800 dark:text-amber-300">{{ $unassignedIssues->count() }} issue(s) without assignees</p>
                            <p class="text-sm text-amber-700 dark:text-amber-400">Click "Assign Issue" on a team member to assign coverage.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Team Member Cards --}}
            @if($teamMembers->count() > 0)
                <div class="space-y-4">
                    @foreach($teamMembers as $member)
                        @php
                            $activity = $this->getActivityForUser($member->id);
                            $issues = $this->getIssuesForUser($member->id);
                            $isExpanded = $expandedMemberId === $member->id;
                        @endphp
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                            {{-- Member Row --}}
                            <div class="p-4">
                                <div class="flex items-center gap-4">
                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg font-bold">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </div>
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $member->name }}</h3>
                                            @if($member->office_location)
                                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                                    {{ $member->office_location }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $member->title ?? 'Staff' }}</p>
                                    </div>

                                    {{-- Issue Count --}}
                                    <div class="text-center px-4">
                                        <p class="text-2xl font-bold {{ $issues->count() > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500' }}">
                                            {{ $issues->count() }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Issues</p>
                                    </div>

                                    {{-- Activity Summary --}}
                                    <div class="hidden md:flex items-center gap-6 text-center">
                                        <div title="Meetings (30 days)">
                                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $activity['meetings'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">📅 Mtgs</p>
                                        </div>
                                        <div title="Contacts added (30 days)">
                                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $activity['contacts'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">👤 Contacts</p>
                                        </div>
                                        <div title="Documents (30 days)">
                                            <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $activity['documents'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">📄 Docs</p>
                                        </div>
                                    </div>

                                    {{-- Actions --}}
                                    <div class="flex items-center gap-2">
                                        <button wire:click="openAssignModal({{ $member->id }})"
                                            class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                            Assign Issue
                                        </button>
                                        <button wire:click="toggleExpand({{ $member->id }})"
                                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                            <svg class="w-5 h-5 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Expanded Issues List --}}
                            @if($isExpanded)
                                <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4">
                                    @if($issues->count() > 0)
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Assigned Issues</h4>
                                        <div class="space-y-2">
                                            @foreach($issues as $issue)
                                                <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                                    <div class="flex items-center gap-3">
                                                        @if($issue->priority_level === 'Member Priority')
                                                            <span class="text-lg" title="Member Priority">🔥</span>
                                                        @elseif($issue->priority_level === 'Office Priority')
                                                            <span class="text-lg" title="Office Priority">⭐</span>
                                                        @endif
                                                        <div>
                                                            <a href="{{ route('issues.show', $issue) }}" wire:navigate
                                                                class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                                {{ $issue->name }}
                                                            </a>
                                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $issue->status }} • {{ $issue->priority_level ?? 'Normal' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <button wire:click="unassignIssue({{ $member->id }}, {{ $issue->id }})"
                                                        wire:confirm="Remove {{ $member->name }} from this issue?"
                                                        class="text-sm text-red-600 dark:text-red-400 hover:underline">
                                                        Remove
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-6">
                                            <p class="text-gray-500 dark:text-gray-400">No issues assigned</p>
                                            <button wire:click="openAssignModal({{ $member->id }})"
                                                class="mt-2 text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                                                Assign their first issue →
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <span class="text-5xl">👥</span>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No team members found</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Add team members in Admin → Office Settings</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Assign Issue Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAssignModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Assign Issues to {{ User::find($assignToUserId)?->name }}
                        </h3>

                        <div class="max-h-96 overflow-y-auto space-y-2">
                            @foreach($allActiveIssues as $issue)
                                @php
                                    $isAssigned = $issue->staff->contains('id', $assignToUserId);
                                @endphp
                                <label class="flex items-center gap-3 p-3 rounded-lg border {{ $isAssigned ? 'border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-600' }} hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" wire:model="selectedIssueIds" value="{{ $issue->id }}"
                                        {{ $isAssigned ? 'disabled' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $issue->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $issue->priority_level ?? 'Normal' }}
                                            @if($isAssigned)
                                                • <span class="text-green-600 dark:text-green-400">Already assigned</span>
                                            @endif
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button wire:click="closeAssignModal"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg">
                                Cancel
                            </button>
                            <button wire:click="assignIssues"
                                class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                {{ empty($selectedIssueIds) ? 'disabled' : '' }}>
                                Assign Selected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>



