<?php

namespace App\Livewire;

use App\Models\UserFeedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class FeedbackWidget extends Component
{
    public bool $isOpen = false;
    public string $type = '';
    public string $message = '';
    public bool $includeScreenshot = false;
    public ?string $screenshotData = null;
    
    // Auto-captured context
    public string $pageUrl = '';
    public string $pageTitle = '';
    public string $browser = '';
    public string $device = '';
    public string $screenResolution = '';
    public array $consoleErrors = [];
    
    public bool $submitted = false;

    protected $listeners = [
        'openFeedbackModal' => 'open',
        'captureScreenshot' => 'receiveScreenshot',
        'captureContext' => 'receiveContext',
    ];

    protected $rules = [
        'type' => 'required|in:bug,feature,general',
        'message' => 'required|min:10|max:5000',
    ];

    protected $messages = [
        'type.required' => 'Please select the type of feedback.',
        'message.required' => 'Please describe your feedback.',
        'message.min' => 'Please provide at least 10 characters.',
    ];

    public function open(): void
    {
        $this->isOpen = true;
        $this->submitted = false;
        $this->dispatch('requestContext');
    }

    public function close(): void
    {
        $this->reset(['isOpen', 'type', 'message', 'includeScreenshot', 'screenshotData', 'consoleErrors', 'submitted']);
    }

    public function receiveScreenshot(string $data): void
    {
        $this->screenshotData = $data;
    }

    public function receiveContext(array $context): void
    {
        $this->pageUrl = $context['url'] ?? '';
        $this->pageTitle = $context['title'] ?? '';
        $this->browser = $context['browser'] ?? '';
        $this->device = $context['device'] ?? '';
        $this->screenResolution = $context['resolution'] ?? '';
        $this->consoleErrors = $context['errors'] ?? [];
    }

    public function requestScreenshot(): void
    {
        $this->dispatch('captureScreenshotRequest');
    }

    public function submit(): void
    {
        $this->validate();

        $user = Auth::user();
        
        // Save screenshot if provided
        $screenshotPath = null;
        if ($this->includeScreenshot && $this->screenshotData) {
            $screenshotPath = $this->saveScreenshot($this->screenshotData);
        }

        // Create feedback record
        UserFeedback::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Anonymous',
            'user_email' => $user?->email,
            'office_name' => config('office.member_name'),
            'type' => $this->type,
            'message' => $this->message,
            'screenshot_path' => $screenshotPath,
            'page_url' => $this->pageUrl,
            'page_title' => $this->pageTitle,
            'browser' => $this->browser,
            'device' => $this->device,
            'screen_resolution' => $this->screenResolution,
            'console_errors' => $this->type === 'bug' ? $this->consoleErrors : null,
            'status' => 'new',
        ]);

        $this->submitted = true;

        // Auto-close after 3 seconds
        $this->dispatch('feedbackSubmitted');
    }

    protected function saveScreenshot(string $base64Data): ?string
    {
        try {
            // Remove data URL prefix if present
            $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
            $imageData = base64_decode($base64Data);
            
            if (!$imageData) {
                return null;
            }

            $filename = 'feedback/' . date('Y/m/') . uniqid('screenshot_') . '.png';
            Storage::disk('local')->put($filename, $imageData);
            
            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.feedback-widget');
    }
}


