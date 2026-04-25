<?php

namespace App\Livewire\Components;

use Livewire\Component;

/**
 * Dashboard Toggle Component
 * 
 * Allows users to switch between Personal and Office Overview dashboards.
 * Saves preference to session.
 */
class DashboardToggle extends Component
{
    public string $currentView = 'personal';

    public function mount()
    {
        // Detect which dashboard we're on
        $routeName = request()->route()?->getName() ?? '';
        $this->currentView = $routeName === 'dashboard.overview' ? 'overview' : 'personal';
    }

    public function switchTo(string $view)
    {
        session(['preferred_dashboard' => $view]);

        return redirect()->route("dashboard.{$view}");
    }

    public function render()
    {
        return view('livewire.components.dashboard-toggle');
    }
}
