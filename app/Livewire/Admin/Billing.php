<?php

namespace App\Livewire\Admin;

use App\Models\AiUsage;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Billing & Plan')]
class Billing extends Component
{
    public bool $showRequestUsersModal = false;
    public int $additionalUsersRequested = 1;

    /**
     * Get current plan based on office configuration
     */
    public function getCurrentPlanProperty(): array
    {
        $level = config('office.government_level', 'federal');
        $chamber = config('office.chamber', 'house');

        $planKey = match ($level) {
            'federal' => $chamber === 'senate' ? 'senate' : 'house',
            'state' => 'state',
            'local', 'city', 'county', 'municipal' => 'local',
            default => 'house',
        };

        return config("billing.plans.{$planKey}", config('billing.plans.house'));
    }

    public function getPlanKeyProperty(): string
    {
        $level = config('office.government_level', 'federal');
        $chamber = config('office.chamber', 'house');

        return match ($level) {
            'federal' => $chamber === 'senate' ? 'senate' : 'house',
            'state' => 'state',
            'local', 'city', 'county', 'municipal' => 'local',
            default => 'house',
        };
    }

    public function getTeamMemberCountProperty(): int
    {
        return User::count();
    }

    public function getMaxUsersProperty(): int
    {
        return $this->currentPlan['max_users'] ?? 25;
    }

    public function getOfficeNameProperty(): string
    {
        return config('office.member.name', 'Your Office');
    }

    public function getIsBetaProperty(): bool
    {
        return config('billing.beta_mode', true);
    }

    public function getMemberSinceProperty(): string
    {
        $firstUser = User::orderBy('created_at', 'asc')->first();
        return $firstUser ? $firstUser->created_at->format('F Y') : now()->format('F Y');
    }

    public function getBillingResetDateProperty(): string
    {
        // For now, just show start of next month
        return Carbon::now()->addMonth()->startOfMonth()->format('M j');
    }

    /**
     * AI Usage Stats
     */
    public function getAiUsageThisMonthProperty(): int
    {
        return AiUsage::getMonthlyUsageCount();
    }

    public function getAiAllocationProperty(): int
    {
        return $this->currentPlan['ai_requests_monthly'] ?? 1000;
    }

    public function getAiUsagePercentProperty(): int
    {
        if ($this->aiAllocation === 0) return 0;
        return min(100, round(($this->aiUsageThisMonth / $this->aiAllocation) * 100));
    }

    public function getAiUsageByFeatureProperty(): array
    {
        $usage = AiUsage::getMonthlyUsageByFeature();
        $labels = AiUsage::FEATURES;
        
        $result = [];
        foreach ($usage as $feature => $count) {
            $result[] = [
                'feature' => $feature,
                'label' => $labels[$feature] ?? ucfirst($feature),
                'count' => $count,
            ];
        }
        
        // Sort by count descending
        usort($result, fn($a, $b) => $b['count'] <=> $a['count']);
        
        return $result;
    }

    public function getBonusCreditsProperty(): int
    {
        // Sum of all users' bonus credits (office-level in future)
        return User::sum('ai_credits_bonus');
    }

    /**
     * Pricing helpers
     */
    public function getMonthlyPriceProperty(): string
    {
        return '$' . number_format($this->currentPlan['monthly_price'] / 100);
    }

    public function getAnnualPriceProperty(): string
    {
        return '$' . number_format($this->currentPlan['annual_price'] / 100);
    }

    public function getAnnualSavingsProperty(): string
    {
        $monthlyCost = ($this->currentPlan['monthly_price'] * 12) / 100;
        $annualCost = $this->currentPlan['annual_price'] / 100;
        return '$' . number_format($monthlyCost - $annualCost);
    }

    public function getAllPlansProperty(): array
    {
        return config('billing.plans', []);
    }

    public function getIncludedFeaturesProperty(): array
    {
        return config('billing.included_features', []);
    }

    /**
     * Actions
     */
    public function openRequestUsersModal(): void
    {
        $this->showRequestUsersModal = true;
    }

    public function closeRequestUsersModal(): void
    {
        $this->showRequestUsersModal = false;
        $this->additionalUsersRequested = 1;
    }

    public function submitUserRequest(): void
    {
        // For now, just show a success message
        // In Phase 2, this will create a request in the LegiDash Admin panel
        session()->flash('billing-message', "Request submitted for {$this->additionalUsersRequested} additional user(s). We'll be in touch soon!");
        $this->closeRequestUsersModal();
    }

    public function render()
    {
        return view('livewire.admin.billing', [
            'currentPlan' => $this->currentPlan,
            'planKey' => $this->planKey,
            'teamMemberCount' => $this->teamMemberCount,
            'maxUsers' => $this->maxUsers,
            'officeName' => $this->officeName,
            'isBeta' => $this->isBeta,
            'memberSince' => $this->memberSince,
            'billingResetDate' => $this->billingResetDate,
            'aiUsageThisMonth' => $this->aiUsageThisMonth,
            'aiAllocation' => $this->aiAllocation,
            'aiUsagePercent' => $this->aiUsagePercent,
            'aiUsageByFeature' => $this->aiUsageByFeature,
            'bonusCredits' => $this->bonusCredits,
            'monthlyPrice' => $this->monthlyPrice,
            'annualPrice' => $this->annualPrice,
            'annualSavings' => $this->annualSavings,
            'allPlans' => $this->allPlans,
            'includedFeatures' => $this->includedFeatures,
        ]);
    }
}
