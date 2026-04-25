<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Billing & Plan</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your subscription and usage</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('billing-message'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-xl text-green-700 dark:text-green-300">
            {{ session('billing-message') }}
        </div>
    @endif

    {{-- Current Plan Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Current Plan</h2>
            </div>
            @if($isBeta)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-emerald-500 to-teal-500 text-white">
                    🎉 Beta Access
                </span>
            @endif
        </div>

        @if($isBeta)
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl p-5 border border-emerald-200 dark:border-emerald-800">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">FREE</span>
                    <span class="text-sm text-emerald-600 dark:text-emerald-400">during beta</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    You have full access to all LegiDash features during our beta period.
                    We'll give you at least 30 days notice before billing begins.
                </p>
                <div class="border-t border-emerald-200 dark:border-emerald-700 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Your office type:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $currentPlan['name'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Users:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $teamMemberCount }} of {{ $maxUsers }} included</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">AI requests this month:</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ number_format($aiUsageThisMonth) }} of {{ number_format($aiAllocation) }}</span>
                    </div>
                </div>
                <div class="border-t border-emerald-200 dark:border-emerald-700 pt-4 mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-700 dark:text-gray-300">After beta, your plan will be:</span><br>
                        {{ $currentPlan['name'] }} — {{ $monthlyPrice }}/month (or {{ $annualPrice }}/year, save {{ $annualSavings }})
                    </p>
                </div>
            </div>
        @else
            {{-- Post-beta active subscription view --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $currentPlan['name'] }}</span>
                        <span class="ml-2 text-lg text-gray-600 dark:text-gray-400">{{ $monthlyPrice }}/mo</span>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Billed monthly</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Next billing date: {{ now()->addMonth()->format('F j, Y') }}
                </p>
                <div class="flex gap-3">
                    <button class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors">
                        Switch to Annual (Save {{ $annualSavings }}/year)
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors">
                        Change Plan
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- AI Usage Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">AI Usage</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">Resets {{ $billingResetDate }}</span>
        </div>

        <div class="mb-4">
            {{-- Progress Bar --}}
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">This Month</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ number_format($aiUsageThisMonth) }} / {{ number_format($aiAllocation + $bonusCredits) }}
                    <span class="text-gray-500 dark:text-gray-400">({{ $aiUsagePercent }}%)</span>
                </span>
            </div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div 
                    class="h-full rounded-full transition-all duration-500 {{ $aiUsagePercent >= 80 ? ($aiUsagePercent >= 100 ? 'bg-red-500' : 'bg-amber-500') : 'bg-gradient-to-r from-indigo-500 to-purple-500' }}"
                    style="width: {{ min(100, $aiUsagePercent) }}%"
                ></div>
            </div>
            @if($bonusCredits > 0)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Includes {{ number_format($bonusCredits) }} bonus credits
                </p>
            @endif
        </div>

        {{-- Usage Breakdown --}}
        @if(count($aiUsageByFeature) > 0)
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Breakdown:</p>
                <div class="space-y-2">
                    @foreach($aiUsageByFeature as $usage)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">{{ $usage['label'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($usage['count']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mb-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">No AI usage this month yet.</p>
            </div>
        @endif

        {{-- Purchase Credits --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Need more? Purchase additional AI credits anytime.</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">$10 for 500 requests (credits don't expire)</p>
                </div>
                <button 
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                    @if(!$isBeta) @endif
                    disabled
                    title="Coming soon"
                >
                    Purchase AI Credits
                </button>
            </div>
        </div>
    </div>

    {{-- Team Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Team</h2>
        
        <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">Users</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $teamMemberCount }} / {{ $maxUsers }}</span>
            </div>
            <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                @php $userPercent = min(100, round(($teamMemberCount / $maxUsers) * 100)); @endphp
                <div 
                    class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full transition-all duration-500"
                    style="width: {{ $userPercent }}%"
                ></div>
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Need more seats? Contact us to add additional users.</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Additional users: $5/user/month</p>
                </div>
                <button 
                    wire:click="openRequestUsersModal"
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                >
                    Request Additional Users
                </button>
            </div>
        </div>
    </div>

    {{-- Future Pricing Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Future Pricing</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Here's what LegiDash will cost after the beta period ends:</p>

        {{-- Pricing Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($allPlans as $key => $plan)
                <div class="relative rounded-xl border-2 transition-all {{ $planKey === $key ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50' }} p-4">
                    @if($planKey === $key)
                        <div class="absolute -top-2.5 left-1/2 -translate-x-1/2">
                            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-500 text-white rounded-full">Your Plan</span>
                        </div>
                    @endif
                    <div class="text-center">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">{{ str_replace('U.S. ', '', $plan['name']) }}</h3>
                        <div class="mt-2">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">${{ $plan['monthly_price'] / 100 }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">/mo</span>
                        </div>
                        <div class="mt-3 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            <p>{{ $plan['max_users'] }} users</p>
                            <p>{{ number_format($plan['ai_requests_monthly']) }} AI/mo</p>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">{{ $plan['description'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Annual Discount Note --}}
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-4">
            <p class="text-sm text-amber-800 dark:text-amber-300">
                <span class="font-medium">💰 Save with annual billing:</span> 2 months free when you pay annually
            </p>
        </div>

        {{-- Included Features --}}
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">All plans include:</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($includedFeatures as $feature)
                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $feature }}
                    </div>
                @endforeach
            </div>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
            Need more users or AI? Add-ons available.
        </p>
    </div>

    {{-- Payment Method Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Method</h2>
        
        @if($isBeta)
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    No payment method required during beta.
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mb-4">
                    Want to set up billing early? You won't be charged until the beta period ends.
                </p>
                <button 
                    class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors"
                    disabled
                    title="Coming soon"
                >
                    Add Payment Method
                </button>
            </div>
        @else
            {{-- Post-beta with payment method --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">•••• •••• •••• 4242</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Expires 12/2027</p>
                    </div>
                </div>
                <button class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Update
                </button>
            </div>
        @endif
    </div>

    {{-- Support Footer --}}
    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
        Questions about billing? Contact us at 
        <a href="mailto:{{ config('billing.support_email', 'billing@wrk.gov') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
            {{ config('billing.support_email', 'billing@wrk.gov') }}
        </a>
    </div>

    {{-- Request Additional Users Modal --}}
    @if($showRequestUsersModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" wire:click="closeRequestUsersModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Request Additional Users</h3>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Your current plan includes {{ $maxUsers }} users. Additional users are $5/user/month.
                        </p>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                How many additional users do you need?
                            </label>
                            <input 
                                type="number" 
                                wire:model="additionalUsersRequested"
                                min="1" 
                                max="50"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                            @if($additionalUsersRequested > 0)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    Additional cost: ${{ $additionalUsersRequested * 5 }}/month
                                </p>
                            @endif
                        </div>

                        <div class="flex gap-3 justify-end">
                            <button 
                                wire:click="closeRequestUsersModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancel
                            </button>
                            <button 
                                wire:click="submitUserRequest"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
                            >
                                Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
