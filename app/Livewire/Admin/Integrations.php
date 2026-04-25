<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Integrations')]
class Integrations extends Component
{
    public function getCalendarSyncStatusProperty(): array
    {
        $usersWithCalendar = User::whereNotNull('google_token')->count();
        $usersTotal = User::count();
        
        // Check for stale syncs (more than 24 hours since last sync)
        $staleUsers = User::whereNotNull('google_token')
            ->where(function ($query) {
                $query->whereNull('calendar_last_synced')
                    ->orWhere('calendar_last_synced', '<', Carbon::now()->subDay());
            })
            ->count();

        $status = 'disconnected';
        $message = 'Calendar sync not configured';
        
        if ($usersWithCalendar > 0) {
            if ($staleUsers === 0) {
                $status = 'connected';
                $message = 'All calendars syncing normally';
            } else {
                $status = 'warning';
                $message = "{$staleUsers} team member(s) need to reconnect";
            }
        }

        return [
            'status' => $status,
            'message' => $message,
            'connected_users' => $usersWithCalendar,
            'total_users' => $usersTotal,
            'stale_users' => $staleUsers,
        ];
    }

    public function getAiStatusProperty(): array
    {
        $enabled = config('services.openai.api_key') || config('services.anthropic.api_key');
        
        return [
            'status' => $enabled ? 'connected' : 'disconnected',
            'message' => $enabled ? 'AI features enabled' : 'No AI API keys configured',
        ];
    }

    public function render()
    {
        return view('livewire.admin.integrations', [
            'calendarSync' => $this->calendarSyncStatus,
            'aiStatus' => $this->aiStatus,
        ]);
    }
}



