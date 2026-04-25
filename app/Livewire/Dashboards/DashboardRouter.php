<?php

namespace App\Livewire\Dashboards;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard Router Component
 * 
 * Routes users to the appropriate dashboard based on role/preference.
 * - Chiefs of Staff, Legislative Directors, Senior Staff → Office Overview
 * - Everyone else → Personal Dashboard
 */
class DashboardRouter extends Component
{
    public function mount()
    {
        $user = Auth::user();

        // Check user preference if they've set one
        if (session()->has('preferred_dashboard')) {
            $preference = session('preferred_dashboard');
            return redirect()->route("dashboard.{$preference}");
        }

        // Default based on role
        // Chiefs of Staff, Legislative Directors, Senior Staff → Office Overview
        // Everyone else → Personal Dashboard

        $leadershipTitles = [
            'Chief of Staff',
            'Legislative Director',
            'Deputy Chief',
            'Office Manager',
            'Communications Director',
            'Scheduler',
            'Executive Director',
            'Director',
        ];

        $leadershipRoles = ['admin', 'management'];

        // Check title, role, or is_admin flag
        $hasLeadershipTitle = in_array($user->title, $leadershipTitles);
        $hasLeadershipRole = in_array($user->role, $leadershipRoles) || in_array($user->access_level, $leadershipRoles);
        $isAdmin = $user->is_admin;

        if ($hasLeadershipTitle || $hasLeadershipRole || $isAdmin) {
            return redirect()->route('dashboard.overview');
        }

        return redirect()->route('dashboard.personal');
    }

    public function render()
    {
        return view('livewire.dashboards.dashboard-router');
    }
}
