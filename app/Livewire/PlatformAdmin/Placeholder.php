<?php

namespace App\Livewire\PlatformAdmin;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.platform-admin')]
class Placeholder extends Component
{
    public string $title = 'Coming Soon';
    public string $description = 'This feature is under development.';

    public function mount(string $title = 'Coming Soon', string $description = 'This feature is under development.'): void
    {
        $this->title = $title;
        $this->description = $description;
    }

    public function render()
    {
        return view('livewire.platform-admin.placeholder');
    }
}

