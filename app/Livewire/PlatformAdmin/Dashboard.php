<?php

namespace App\Livewire\PlatformAdmin;

use App\Models\BetaRequest;
use App\Models\User;
use App\Models\UserFeedback;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function getStatsProperty(): array
    {
        return [
            'pending_requests' => BetaRequest::where('status', 'pending')->count(),
            'total_requests' => BetaRequest::count(),
            'approved_requests' => BetaRequest::where('status', 'approved')->count(),
            'active_offices' => 0, // TODO: Implement when office tracking is set up
            'total_users' => User::count(),
            'new_feedback' => UserFeedback::where('status', 'new')->count(),
            'total_feedback' => UserFeedback::count(),
        ];
    }

    public function getRecentRequestsProperty()
    {
        return BetaRequest::orderByDesc('created_at')
            ->take(5)
            ->get();
    }

    public function getRecentFeedbackProperty()
    {
        return UserFeedback::with('user')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.platform-admin.dashboard', [
            'stats' => $this->stats,
            'recentRequests' => $this->recentRequests,
            'recentFeedback' => $this->recentFeedback,
        ]);
    }
}

