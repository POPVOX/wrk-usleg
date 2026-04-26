<?php

namespace App\Livewire\PlatformAdmin;

use App\Models\BetaRequest;
use App\Models\User;
use Illuminate\Support\Str;
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
    public ?string $generatedInviteUrl = null;
    public ?int $generatedInviteRequestId = null;

    protected $queryString = [
        'filterStatus' => ['except' => '', 'as' => 'status'],
        'filterLevel' => ['except' => '', 'as' => 'level'],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function approve(int $requestId): void
    {
        $request = BetaRequest::findOrFail($requestId);

        if ($request->status === 'onboarded') {
            $this->dispatch('notify', type: 'info', message: 'This requester already has access.');
            return;
        }

        if ($existingUser = User::where('email', $request->email)->first()) {
            $request->update([
                'status' => 'onboarded',
                'approved_at' => $request->approved_at ?? now(),
                'approved_by' => $request->approved_by ?? auth()->id(),
                'onboarded_at' => $request->onboarded_at ?? now(),
                'onboarded_user_id' => $existingUser->id,
                'invite_token' => null,
                'invite_expires_at' => null,
            ]);

            $this->generatedInviteUrl = null;
            $this->generatedInviteRequestId = null;
            $this->dispatch('notify', type: 'success', message: 'This email already had an account. The request was marked onboarded.');
            return;
        }

        $request->update([
            'status' => 'approved',
            'invite_token' => Str::random(48),
            'invite_expires_at' => now()->addDays(14),
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'declined_at' => null,
            'declined_by' => null,
        ]);

        $request->refresh();

        $this->generatedInviteRequestId = $request->id;
        $this->generatedInviteUrl = $this->buildInviteUrl($request);
        $this->dispatch('notify', type: 'success', message: 'Invite link generated. Share it with the requester.');
    }

    public function regenerateInvite(int $requestId): void
    {
        $request = BetaRequest::findOrFail($requestId);

        if ($request->status === 'onboarded') {
            $this->dispatch('notify', type: 'info', message: 'This requester already completed onboarding.');
            return;
        }

        $request->update([
            'status' => 'approved',
            'invite_token' => Str::random(48),
            'invite_expires_at' => now()->addDays(14),
            'approved_at' => $request->approved_at ?? now(),
            'approved_by' => $request->approved_by ?? auth()->id(),
            'declined_at' => null,
            'declined_by' => null,
        ]);

        $request->refresh();

        $this->generatedInviteRequestId = $request->id;
        $this->generatedInviteUrl = $this->buildInviteUrl($request);
        $this->dispatch('notify', type: 'success', message: 'A fresh invite link is ready.');
    }

    public function showInvite(int $requestId): void
    {
        $request = BetaRequest::findOrFail($requestId);

        if (!$request->inviteIsActive()) {
            $this->dispatch('notify', type: 'error', message: 'This request does not have an active invite link.');
            return;
        }

        $this->generatedInviteRequestId = $request->id;
        $this->generatedInviteUrl = $this->buildInviteUrl($request);
    }

    public function decline(int $requestId): void
    {
        $request = BetaRequest::findOrFail($requestId);

        $request->update([
            'status' => 'declined',
            'declined_at' => now(),
            'declined_by' => auth()->id(),
            'invite_token' => null,
            'invite_expires_at' => null,
        ]);

        if ($this->generatedInviteRequestId === $request->id) {
            $this->generatedInviteRequestId = null;
            $this->generatedInviteUrl = null;
        }

        $this->dispatch('notify', type: 'success', message: 'Request declined.');
    }

    protected function buildInviteUrl(BetaRequest $request): string
    {
        return route('register', ['invite' => $request->invite_token], absolute: true);
    }

    public function render()
    {
        $query = BetaRequest::query()
            ->with(['approvedByUser', 'onboardedUser'])
            ->orderByDesc('created_at');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterLevel) {
            $query->whereIn('government_level', match ($this->filterLevel) {
                'federal' => ['us_congress'],
                'state' => ['state_legislature'],
                'local' => ['city_municipal', 'county'],
                default => [],
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('official_name', 'like', "%{$this->search}%");
            });
        }

        $stats = [
            'pending' => BetaRequest::where('status', 'pending')->count(),
            'approved' => BetaRequest::where('status', 'approved')->count(),
            'onboarded' => BetaRequest::where('status', 'onboarded')->count(),
            'declined' => BetaRequest::where('status', 'declined')->count(),
            'total' => BetaRequest::count(),
        ];

        return view('livewire.platform-admin.beta-requests', [
            'requests' => $query->paginate(20),
            'stats' => $stats,
        ]);
    }
}
