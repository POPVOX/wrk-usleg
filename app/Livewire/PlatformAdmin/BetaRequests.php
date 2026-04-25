<?php

namespace App\Livewire\PlatformAdmin;

use App\Models\BetaRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
#[Title('Beta Requests')]
class BetaRequests extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $filterLevel = '';
    public string $search = '';

    protected $queryString = [
        'filterStatus' => ['except' => '', 'as' => 'status'],
        'filterLevel' => ['except' => '', 'as' => 'level'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BetaRequest::query()->orderByDesc('created_at');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterLevel) {
            $query->where('level', $this->filterLevel);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('elected_official_name', 'like', "%{$this->search}%");
            });
        }

        $stats = [
            'pending' => BetaRequest::where('status', 'pending')->count(),
            'approved' => BetaRequest::where('status', 'approved')->count(),
            'declined' => BetaRequest::where('status', 'declined')->count(),
            'total' => BetaRequest::count(),
        ];

        return view('livewire.platform-admin.beta-requests', [
            'requests' => $query->paginate(20),
            'stats' => $stats,
        ]);
    }
}

