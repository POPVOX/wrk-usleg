<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\MemberLocation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Member Location Widget Component
 * 
 * Displays and allows updating of the Member's current location and timezone.
 * Critical for staff coordination across DC and district offices.
 */
class MemberLocationWidget extends Component
{
    public bool $showUpdateForm = false;
    public bool $isCompact = false;

    // Form fields
    public string $location_name = '';
    public string $timezone = 'America/New_York';
    public ?string $current_activity = null;
    public ?string $activity_until = null;

    public function mount(bool $isCompact = false)
    {
        $this->isCompact = $isCompact;
        $this->loadCurrentLocation();
    }

    protected function loadCurrentLocation()
    {
        $current = MemberLocation::getCurrentLocation();

        if ($current) {
            $this->location_name = $current->location_name;
            $this->timezone = $current->timezone;
            $this->current_activity = $current->current_activity;
            $this->activity_until = $current->activity_until?->format('Y-m-d\TH:i');
        }
    }

    public function toggleUpdateForm()
    {
        $this->showUpdateForm = !$this->showUpdateForm;

        if ($this->showUpdateForm) {
            $this->loadCurrentLocation();
        }
    }

    public function updateLocation()
    {
        $this->validate([
            'location_name' => 'required|string|max:100',
            'timezone' => 'required|string|timezone',
            'current_activity' => 'nullable|string|max:255',
            'activity_until' => 'nullable|date',
        ]);

        // Set all previous locations to not current
        MemberLocation::where('is_current', true)->update(['is_current' => false]);

        // Create new current location
        MemberLocation::create([
            'location_name' => $this->location_name,
            'timezone' => $this->timezone,
            'current_activity' => $this->current_activity,
            'activity_until' => $this->activity_until ? Carbon::parse($this->activity_until) : null,
            'is_current' => true,
            'updated_by' => Auth::id(),
        ]);

        $this->showUpdateForm = false;

        session()->flash('location-updated', 'Member location updated successfully.');

        $this->dispatch('location-updated');
    }

    public function clearActivity()
    {
        $current = MemberLocation::getCurrentLocation();

        if ($current) {
            $current->update([
                'current_activity' => null,
                'activity_until' => null,
            ]);
        }

        $this->current_activity = null;
        $this->activity_until = null;
    }

    public function getCurrentLocationProperty()
    {
        return MemberLocation::getCurrentLocation();
    }

    public function getCurrentTimeProperty()
    {
        $location = $this->currentLocation;
        $timezone = $location?->timezone ?? 'America/New_York';

        return Carbon::now($timezone)->format('g:i A');
    }

    public function getTimezoneAbbrevProperty()
    {
        $location = $this->currentLocation;
        $timezone = $location?->timezone ?? 'America/New_York';

        return Carbon::now($timezone)->format('T');
    }

    public function getLocationOptionsProperty()
    {
        return [
            'Washington, DC',
            'District Office 1',
            'District Office 2',
            'In District',
            'Traveling',
            'On Recess',
            'Other',
        ];
    }

    public function getTimezoneOptionsProperty()
    {
        return MemberLocation::TIMEZONE_OPTIONS;
    }

    public function render()
    {
        return view('livewire.components.member-location-widget', [
            'currentLocation' => $this->currentLocation,
            'currentTime' => $this->currentTime,
            'timezoneAbbrev' => $this->timezoneAbbrev,
            'locationOptions' => $this->locationOptions,
            'timezoneOptions' => $this->timezoneOptions,
        ]);
    }
}
