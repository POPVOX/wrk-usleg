<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Office Settings')]
class OfficeSettings extends Component
{
    public string $officeName = '';
    public string $officeType = 'congressional';
    public string $memberName = '';
    public string $memberParty = 'D';
    public string $memberState = '';
    public string $memberDistrict = '';
    public string $dcAddress = '';
    public string $districtAddress = '';
    public string $timezone = 'America/New_York';

    public function mount(): void
    {
        $this->officeName = config('app.name', 'Congressional Office');
        $this->memberName = config('office.member.name', '');
        $this->memberParty = config('office.member.party', 'D');
        $this->memberState = config('office.member.state', '');
        $this->memberDistrict = config('office.member.district', '');
        $this->timezone = config('app.timezone', 'America/New_York');
    }

    public function getTeamMembersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function updateUserRole(int $userId, string $accessLevel): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['access_level' => $accessLevel]);
            $this->dispatch('notify', type: 'success', message: 'Role updated for ' . $user->name);
        }
    }

    public function toggleAdmin(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->update(['is_admin' => !$user->is_admin]);
            $status = $user->is_admin ? 'granted' : 'revoked';
            $this->dispatch('notify', type: 'success', message: "Admin access {$status} for {$user->name}");
        }
    }

    public function render()
    {
        return view('livewire.admin.office-settings', [
            'teamMembers' => $this->teamMembers,
        ]);
    }
}



