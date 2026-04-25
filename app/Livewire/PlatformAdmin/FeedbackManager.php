<?php

namespace App\Livewire\PlatformAdmin;

use App\Models\UserFeedback;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.platform-admin')]
#[Title('Feedback')]
class FeedbackManager extends Component
{
    use WithPagination;

    public string $filterType = '';
    public string $filterStatus = '';
    public string $search = '';
    
    public ?int $selectedFeedbackId = null;
    public string $adminNotes = '';
    public string $newStatus = '';
    public string $priority = 'medium';

    protected $queryString = [
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function selectFeedback(int $id): void
    {
        $this->selectedFeedbackId = $id;
        $feedback = UserFeedback::find($id);
        if ($feedback) {
            $this->adminNotes = $feedback->admin_notes ?? '';
            $this->newStatus = $feedback->status;
        }
    }

    public function closeDetail(): void
    {
        $this->selectedFeedbackId = null;
        $this->adminNotes = '';
        $this->newStatus = '';
    }

    public function updateFeedback(): void
    {
        $feedback = UserFeedback::find($this->selectedFeedbackId);
        if (!$feedback) return;

        $feedback->update([
            'admin_notes' => $this->adminNotes,
            'status' => $this->newStatus,
            'resolved_at' => in_array($this->newStatus, ['resolved', 'closed']) ? now() : null,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Feedback updated successfully');
    }

    public function deleteFeedback(int $id): void
    {
        $feedback = UserFeedback::find($id);
        if ($feedback) {
            if ($feedback->screenshot_path) {
                Storage::disk('local')->delete($feedback->screenshot_path);
            }
            $feedback->delete();
            $this->dispatch('notify', type: 'success', message: 'Feedback deleted');
            
            if ($this->selectedFeedbackId === $id) {
                $this->closeDetail();
            }
        }
    }

    public function render()
    {
        $query = UserFeedback::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('message', 'like', "%{$this->search}%")
                  ->orWhere('user_name', 'like', "%{$this->search}%")
                  ->orWhere('user_email', 'like', "%{$this->search}%")
                  ->orWhere('office_name', 'like', "%{$this->search}%");
            });
        }

        $stats = [
            'total' => UserFeedback::count(),
            'new' => UserFeedback::where('status', 'new')->count(),
            'in_review' => UserFeedback::where('status', 'reviewing')->count(),
            'bugs' => UserFeedback::where('type', 'bug')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'features' => UserFeedback::where('type', 'feature')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('livewire.platform-admin.feedback-manager', [
            'feedbacks' => $query->paginate(20),
            'selectedFeedback' => $this->selectedFeedbackId ? UserFeedback::find($this->selectedFeedbackId) : null,
            'types' => UserFeedback::TYPES,
            'statuses' => UserFeedback::STATUSES,
            'stats' => $stats,
        ]);
    }
}

