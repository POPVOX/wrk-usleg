<?php

namespace App\Livewire;

use App\Models\BetaRequest;
use Livewire\Component;

class BetaRequestForm extends Component
{
    public bool $showModal = false;
    public bool $submitted = false;
    public string $submittedEmail = '';

    // Form fields
    public string $full_name = '';
    public string $email = '';
    public string $role_type = '';
    public string $official_name = '';
    public string $government_level = '';
    public string $government_level_other = '';
    public string $state = '';
    public string $district = '';
    public string $primary_interest = '';
    public string $additional_info = '';

    protected $listeners = ['openBetaModal' => 'openModal'];

    protected function rules(): array
    {
        $rules = [
            'full_name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'role_type' => 'required|in:elected_official,staff_member,other',
            'government_level' => 'required|in:us_congress,state_legislature,city_municipal,county,other',
            'state' => 'required|string|max:50',
            'primary_interest' => 'required|string',
            'additional_info' => 'nullable|string|max:500',
            'district' => 'nullable|string|max:100',
        ];

        if ($this->role_type === 'staff_member') {
            $rules['official_name'] = 'required|string|max:255';
        }

        if ($this->government_level === 'other') {
            $rules['government_level_other'] = 'required|string|max:255';
        }

        return $rules;
    }

    protected $messages = [
        'full_name.required' => 'Please enter your name.',
        'email.required' => 'Please enter your work email.',
        'email.email' => 'Please enter a valid email address.',
        'role_type.required' => 'Please select your role.',
        'government_level.required' => 'Please select your level of government.',
        'state.required' => 'Please select your state.',
        'primary_interest.required' => 'Please tell us what brings you here.',
        'official_name.required' => 'Please enter the official\'s name.',
    ];

    public function openModal(): void
    {
        $this->showModal = true;
        $this->submitted = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['full_name', 'email', 'role_type', 'official_name', 'government_level', 'government_level_other', 'state', 'district', 'primary_interest', 'additional_info']);
    }

    public function submit(): void
    {
        $this->validate();

        BetaRequest::create([
            'full_name' => $this->full_name,
            'email' => $this->email,
            'role_type' => $this->role_type,
            'official_name' => $this->role_type === 'staff_member' ? $this->official_name : null,
            'government_level' => $this->government_level,
            'government_level_other' => $this->government_level === 'other' ? $this->government_level_other : null,
            'state' => $this->state,
            'district' => $this->district ?: null,
            'primary_interest' => $this->primary_interest,
            'additional_info' => $this->additional_info ?: null,
            'utm_source' => request()->query('utm_source'),
            'utm_medium' => request()->query('utm_medium'),
            'utm_campaign' => request()->query('utm_campaign'),
        ]);

        $this->submittedEmail = $this->email;
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.beta-request-form', [
            'roleTypes' => BetaRequest::ROLE_TYPES,
            'governmentLevels' => BetaRequest::GOVERNMENT_LEVELS,
            'primaryInterests' => BetaRequest::PRIMARY_INTERESTS,
            'usStates' => BetaRequest::US_STATES,
        ]);
    }
}



