<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Office Settings</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Configure your office and manage team members.</p>
            </div>

            {{-- Office Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Office Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Member Name</label>
                        <input type="text" wire:model="memberName" disabled
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm bg-gray-50">
                        <p class="text-xs text-gray-500 mt-1">Configure in Setup Wizard</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Party / State / District</label>
                        <input type="text" value="{{ $memberParty }}-{{ $memberState }}-{{ $memberDistrict }}" disabled
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm bg-gray-50">
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('setup.wizard') }}" wire:navigate
                        class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                        Edit member information in Setup Wizard →
                    </a>
                </div>
            </div>

            {{-- Team Members --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Members</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $teamMembers->count() }} members</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                <th class="py-3 pr-4">Name</th>
                                <th class="py-3 pr-4">Email</th>
                                <th class="py-3 pr-4">Title</th>
                                <th class="py-3 pr-4">Access Level</th>
                                <th class="py-3 pr-4">Admin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($teamMembers as $member)
                                <tr>
                                    <td class="py-3 pr-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                                                {{ strtoupper(substr($member->name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $member->email }}
                                    </td>
                                    <td class="py-3 pr-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $member->title ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <select wire:change="updateUserRole({{ $member->id }}, $event.target.value)"
                                            class="text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                            <option value="staff" {{ $member->access_level === 'staff' ? 'selected' : '' }}>Staff</option>
                                            <option value="management" {{ $member->access_level === 'management' ? 'selected' : '' }}>Management</option>
                                            <option value="admin" {{ $member->access_level === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <button wire:click="toggleAdmin({{ $member->id }})"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $member->is_admin ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600' }}"
                                            role="switch"
                                            aria-checked="{{ $member->is_admin ? 'true' : 'false' }}">
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $member->is_admin ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <strong>Access Levels:</strong>
                        <span class="ml-2"><strong>Staff</strong> — Standard access</span>
                        <span class="ml-2"><strong>Management</strong> — Can view Team Overview</span>
                        <span class="ml-2"><strong>Admin</strong> — Full access to all settings</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>



