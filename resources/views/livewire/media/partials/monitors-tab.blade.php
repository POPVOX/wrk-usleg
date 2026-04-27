<div class="space-y-6">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active monitors</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $monitorSummary['active'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total monitors</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $monitorSummary['total'] }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Due now</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $monitorSummary['due'] }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Automated Coverage Monitors</h2>
                <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
                    Track a lawmaker, issue, or topic with an automated search feed. New matches are added as press clips and held for review by default.
                </p>
            </div>
            @if($canManageMonitors)
                <button wire:click="openMonitorForm"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                    Add Monitor
                </button>
            @endif
        </div>

        @if($canManageMonitors && $showMonitorForm)
            <form wire:submit.prevent="saveMonitor"
                class="mt-5 space-y-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 dark:border-indigo-900/40 dark:bg-indigo-900/10">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Monitor name</label>
                        <input type="text" wire:model="monitorForm.name"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                            placeholder="Member name watch">
                        @error('monitorForm.name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                        <select wire:model="monitorForm.monitor_type"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            <option value="member">Member</option>
                            <option value="topic">Topic</option>
                            <option value="issue">Issue</option>
                            <option value="custom">Custom</option>
                        </select>
                        @error('monitorForm.monitor_type') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Search query</label>
                    <input type="text" wire:model="monitorForm.query"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white"
                        placeholder='"Rep. Example" OR "Example Office" OR "farm bill"'>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use quotes and ORs for better matching. This phase uses Google News RSS search.</p>
                    @error('monitorForm.query') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Cadence</label>
                        <select wire:model="monitorForm.cadence"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            <option value="hourly">Hourly</option>
                            <option value="three_times_daily">3x daily</option>
                            <option value="daily">Daily</option>
                        </select>
                        @error('monitorForm.cadence') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Link to issue</label>
                        <select wire:model="monitorForm.issue_id"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            <option value="">None</option>
                            @foreach($issues as $issue)
                                <option value="{{ $issue->id }}">{{ $issue->name }}</option>
                            @endforeach
                        </select>
                        @error('monitorForm.issue_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Link to topic</label>
                        <select wire:model="monitorForm.topic_id"
                            class="w-full rounded-lg border-gray-300 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white">
                            <option value="">None</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name }}</option>
                            @endforeach
                        </select>
                        @error('monitorForm.topic_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900/40 sm:flex-row sm:items-center sm:justify-between">
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="monitorForm.is_active" class="rounded border-gray-300 text-indigo-600">
                        Start active
                    </label>
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="monitorForm.auto_approve" class="rounded border-gray-300 text-indigo-600">
                        Auto-approve clips immediately
                    </label>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cancelMonitorForm"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700">
                        Cancel
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        {{ $editingMonitorId ? 'Save Changes' : 'Create Monitor' }}
                    </button>
                </div>
            </form>
        @endif
    </div>

    <div class="space-y-4">
        @forelse($monitors as $monitor)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $monitor->name }}</h3>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $monitor->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $monitor->is_active ? 'Active' : 'Paused' }}
                            </span>
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ $monitor->monitor_type_label }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {{ $monitor->cadence_label }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $monitor->query }}</p>

                        <div class="mt-3 grid grid-cols-1 gap-3 text-sm text-gray-500 dark:text-gray-400 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Issue:</span>
                                {{ $monitor->issue?->name ?? 'None' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Topic:</span>
                                {{ $monitor->topic?->name ?? 'None' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Last checked:</span>
                                {{ $monitor->last_checked_at?->diffForHumans() ?? 'Never' }}
                            </div>
                            <div>
                                <span class="font-medium text-gray-700 dark:text-gray-300">Clips added:</span>
                                {{ $monitor->clips_found }}
                            </div>
                        </div>

                        @if($monitor->last_error)
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-900/40 dark:bg-red-900/10 dark:text-red-300">
                                <span class="font-medium">Last error:</span> {{ $monitor->last_error }}
                            </div>
                        @endif
                    </div>

                    @if($canManageMonitors)
                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            <button wire:click="runMonitorNow({{ $monitor->id }})"
                                class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-50 dark:border-indigo-800 dark:text-indigo-300 dark:hover:bg-indigo-900/20">
                                Run now
                            </button>
                            <button wire:click="openMonitorForm({{ $monitor->id }})"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700">
                                Edit
                            </button>
                            <button wire:click="toggleMonitor({{ $monitor->id }})"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-zinc-600 dark:text-gray-300 dark:hover:bg-zinc-700">
                                {{ $monitor->is_active ? 'Pause' : 'Enable' }}
                            </button>
                            <button wire:click="deleteMonitor({{ $monitor->id }})"
                                class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 dark:border-red-900/40 dark:text-red-300 dark:hover:bg-red-900/20">
                                Delete
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No monitors yet</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Create a monitor for a lawmaker name or policy topic, and the system will watch for new coverage.
                </p>
                @if($canManageMonitors)
                    <button wire:click="openMonitorForm"
                        class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">
                        Create your first monitor
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</div>
