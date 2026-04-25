<?php

namespace App\Livewire\MemberHub;

use App\Models\MemberLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MemberLocationWidget extends Component
{
    public bool $showUpdateForm = false;

    public string $location_name = '';
    public string $timezone = 'America/New_York';
    public ?string $current_activity = null;
    public ?string $activity_until = null;

    public function mount(): void
    {
        $current = MemberLocation::getCurrent();

        if ($current) {
            $this->location_name = $current->location_name;
            $this->timezone = $current->timezone;
            $this->current_activity = $current->current_activity;
            $this->activity_until = $current->activity_until?->format('Y-m-d\TH:i');
        }
    }

    public function toggleUpdateForm(): void
    {
        $this->showUpdateForm = !$this->showUpdateForm;
    }

    public function updateLocation(): void
    {
        $this->validate([
            'location_name' => 'required|string|max:255',
            'timezone' => 'required|string',
        ]);

        MemberLocation::updateLocation([
            'location_name' => $this->location_name,
            'timezone' => $this->timezone,
            'current_activity' => $this->current_activity,
            'activity_until' => $this->activity_until ? Carbon::parse($this->activity_until) : null,
        ], Auth::id());

        $this->showUpdateForm = false;
        $this->dispatch('notify', type: 'success', message: 'Member location updated');
    }

    public function getCurrentLocationProperty(): ?MemberLocation
    {
        return MemberLocation::getCurrent();
    }

    public function getCurrentTimeProperty(): string
    {
        $location = $this->currentLocation;
        $tz = $location?->timezone ?? 'America/New_York';

        return Carbon::now($tz)->format('g:i A T');
    }

    public function getLocationOptionsProperty(): array
    {
        $offices = config('office.offices', []);
        $options = ['Washington, DC', 'In District', 'Traveling'];

        foreach ($offices as $key => $office) {
            if (!empty($office['name']) && $key !== 'dc') {
                $options[] = $office['name'];
            }
        }

        return array_unique($options);
    }

    public function render()
    {
        return view('livewire.member-hub.member-location-widget', [
            'currentLocation' => $this->currentLocation,
            'currentTime' => $this->currentTime,
            'locationOptions' => $this->locationOptions,
        ]);
    }
}



