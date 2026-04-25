<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use App\Models\IssueDecision;
use App\Models\IssueMilestone;
use App\Models\IssueQuestion;
use App\Models\IssueDocument;
use App\Models\IssueNote;
use App\Models\IssueChatMessage;
use App\Models\IssueEvent;
use App\Models\IssuePublication;
use App\Models\Meeting;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Topic;
use App\Models\User;
use App\Services\ChatService;
use App\Services\DocumentSafety;
use App\Jobs\SendChatMessage;
use App\Jobs\RunStyleCheck;
use App\Jobs\IndexDocumentContent;
use App\Jobs\FetchLinkContent;
use App\Jobs\SuggestDocumentTags;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class IssueShow extends Component
{
    use WithFileUploads;

    public Issue $issue;
    public string $activeTab = 'overview';

    // Editing
    public bool $editing = false;
    public string $name = '';
    public string $description = '';
    public string $goals = '';
    public ?string $start_date = null;
    public ?string $target_end_date = null;
    public string $status = 'active';
    public string $priority_level = 'Tracking';

    // Decision form
    public bool $showDecisionForm = false;
    public string $decisionTitle = '';
    public string $decisionDescription = '';
    public string $decisionRationale = '';
    public string $decisionContext = '';
    public ?string $decisionDate = null;
    public string $decisionDecidedBy = '';

    // Milestone form
    public bool $showMilestoneForm = false;
    public string $milestoneTitle = '';
    public string $milestoneDescription = '';
    public ?string $milestoneTargetDate = null;

    // Question form
    public bool $showQuestionForm = false;
    public string $questionText = '';
    public string $questionContext = '';

    // Answer form
    public ?int $answeringQuestionId = null;
    public string $answerText = '';

    // Staff management
    public bool $showAddStaffModal = false;
    public string $staffSearch = '';
    public ?int $selectedStaffId = null;
    public string $staffRole = 'contributor';

    // Documents
    public bool $showDocumentForm = false;
    public string $documentType = 'link';
    public string $documentTitle = '';
    public string $documentDescription = '';
    public string $documentUrl = '';
    public $documentFile = null;

    // Notes
    public bool $showNoteForm = false;
    public string $noteContent = '';
    public string $noteType = 'general';

    // Issue Chat
    public string $issueChatQuery = '';
    public array $issueChatHistory = [];
    public bool $isChatProcessing = false;

    // Document Viewer
    public bool $showDocumentViewer = false;
    public ?int $viewingDocumentId = null;
    public string $documentContent = '';
    public string $viewingDocumentTitle = '';

    // Style Check
    public bool $isStyleChecking = false;
    public array $styleCheckSuggestions = [];
    public bool $styleCheckComplete = false;

    // Upload / Link
    public $uploadFile = null;
    public string $uploadTitle = '';
    public string $linkTitle = '';
    public string $linkUrl = '';

    // Sync Preview
    public bool $showSyncPreviewModal = false;
    public array $syncPreview = ['add' => [], 'update' => [], 'missing' => []];

    // Tags editing
    public array $tagsEdit = [];
    public array $commonTags = [];

    // AI flags
    public bool $aiEnabled = true;
    public ?string $aiNotice = null;
    public ?string $styleNotice = null;

    protected $queryString = ['activeTab'];

    public function mount(Issue $issue)
    {
        $this->issue = $issue->load([
            'meetings.organizations',
            'organizations',
            'people.organization',
            'topics',
            'decisions.meeting',
            'milestones',
            'questions',
            'createdBy',
            'staff',
            'documents.uploadedBy',
            'notes.user',
            'children',
            'parent',
            'publications',
            'events',
        ]);
        $this->loadIssueData();
        $this->aiEnabled = (bool) config('ai.enabled');
        $this->loadChatHistory();

        // Open document viewer from deep link
        $docToOpen = (int) request()->query('doc', 0);
        if ($docToOpen > 0) {
            try {
                $this->viewDocument($docToOpen);
            } catch (\Throwable $e) {
                // ignore invalid ids
            }
        }
    }

    public function loadIssueData()
    {
        $this->name = $this->issue->name;
        $this->description = $this->issue->description ?? '';
        $this->goals = $this->issue->goals ?? '';
        $this->start_date = $this->issue->start_date?->format('Y-m-d');
        $this->target_end_date = $this->issue->target_end_date?->format('Y-m-d');
        $this->status = $this->issue->status;
        $this->priority_level = $this->issue->priority_level ?? 'Tracking';
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    // --- Issue editing ---
    public function startEditing()
    {
        $this->editing = true;
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->loadIssueData();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goals' => 'nullable|string',
            'start_date' => 'nullable|date',
            'target_end_date' => 'nullable|date',
            'status' => 'required|in:active,on_hold,completed,archived,planning',
            'priority_level' => 'required|in:Tracking,District Priority,Top Priority',
        ]);

        $this->issue->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'goals' => $this->goals ?: null,
            'start_date' => $this->start_date ?: null,
            'target_end_date' => $this->target_end_date ?: null,
            'status' => $this->status,
            'priority_level' => $this->priority_level,
        ]);

        $this->editing = false;
        $this->dispatch('notify', type: 'success', message: 'Issue updated successfully!');
    }

    // --- Decisions ---
    public function toggleDecisionForm()
    {
        $this->showDecisionForm = !$this->showDecisionForm;
        $this->resetDecisionForm();
    }

    public function resetDecisionForm()
    {
        $this->decisionTitle = '';
        $this->decisionDescription = '';
        $this->decisionRationale = '';
        $this->decisionContext = '';
        $this->decisionDate = null;
        $this->decisionDecidedBy = '';
    }

    public function addDecision()
    {
        $this->validate([
            'decisionTitle' => 'required|string|max:255',
            'decisionDescription' => 'required|string',
        ]);

        $this->issue->decisions()->create([
            'title' => $this->decisionTitle,
            'description' => $this->decisionDescription,
            'rationale' => $this->decisionRationale ?: null,
            'context' => $this->decisionContext ?: null,
            'decision_date' => $this->decisionDate ?: null,
            'decided_by' => $this->decisionDecidedBy ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->resetDecisionForm();
        $this->showDecisionForm = false;
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Decision added!');
    }

    public function deleteDecision(int $decisionId)
    {
        IssueDecision::where('id', $decisionId)->where('issue_id', $this->issue->id)->delete();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Decision deleted.');
    }

    // --- Milestones ---
    public function toggleMilestoneForm()
    {
        $this->showMilestoneForm = !$this->showMilestoneForm;
        $this->resetMilestoneForm();
    }

    public function resetMilestoneForm()
    {
        $this->milestoneTitle = '';
        $this->milestoneDescription = '';
        $this->milestoneTargetDate = null;
    }

    public function addMilestone()
    {
        $this->validate([
            'milestoneTitle' => 'required|string|max:255',
        ]);

        $maxOrder = $this->issue->milestones()->max('sort_order') ?? 0;

        $this->issue->milestones()->create([
            'title' => $this->milestoneTitle,
            'description' => $this->milestoneDescription ?: null,
            'due_date' => $this->milestoneTargetDate ?: null,
            'status' => 'pending',
            'sort_order' => $maxOrder + 1,
        ]);

        $this->resetMilestoneForm();
        $this->showMilestoneForm = false;
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Milestone added!');
    }

    public function completeMilestone(int $milestoneId)
    {
        IssueMilestone::where('id', $milestoneId)->where('issue_id', $this->issue->id)
            ->update(['status' => 'completed', 'completed_date' => now()]);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Milestone completed!');
    }

    public function deleteMilestone(int $milestoneId)
    {
        IssueMilestone::where('id', $milestoneId)->where('issue_id', $this->issue->id)->delete();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Milestone deleted.');
    }

    // --- Questions ---
    public function toggleQuestionForm()
    {
        $this->showQuestionForm = !$this->showQuestionForm;
        $this->resetQuestionForm();
    }

    public function resetQuestionForm()
    {
        $this->questionText = '';
        $this->questionContext = '';
    }

    public function addQuestion()
    {
        $this->validate([
            'questionText' => 'required|string',
        ]);

        $this->issue->questions()->create([
            'question' => $this->questionText,
            'context' => $this->questionContext ?: null,
            'status' => 'open',
            'raised_by' => auth()->id(),
        ]);

        $this->resetQuestionForm();
        $this->showQuestionForm = false;
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Question added!');
    }

    public function startAnswering(int $questionId)
    {
        $this->answeringQuestionId = $questionId;
        $this->answerText = '';
    }

    public function cancelAnswering()
    {
        $this->answeringQuestionId = null;
        $this->answerText = '';
    }

    public function submitAnswer()
    {
        $this->validate([
            'answerText' => 'required|string',
        ]);

        IssueQuestion::where('id', $this->answeringQuestionId)
            ->where('issue_id', $this->issue->id)
            ->update([
                'answer' => $this->answerText,
                'status' => 'answered',
                'answered_date' => now(),
            ]);

        $this->answeringQuestionId = null;
        $this->answerText = '';
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Question answered!');
    }

    public function deleteQuestion(int $questionId)
    {
        IssueQuestion::where('id', $questionId)->where('issue_id', $this->issue->id)->delete();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Question deleted.');
    }

    // --- Organization Linking ---
    public bool $showAddOrgModal = false;
    public string $orgSearch = '';
    public ?int $selectedOrgId = null;
    public string $orgRole = '';
    public string $orgNotes = '';

    public function toggleAddOrgModal()
    {
        $this->showAddOrgModal = !$this->showAddOrgModal;
        $this->resetOrgForm();
    }

    public function resetOrgForm()
    {
        $this->orgSearch = '';
        $this->selectedOrgId = null;
        $this->orgRole = '';
        $this->orgNotes = '';
    }

    public function selectOrg(int $orgId)
    {
        $this->selectedOrgId = $orgId;
        $org = Organization::find($orgId);
        $this->orgSearch = $org ? $org->name : '';
    }

    public function linkOrganization()
    {
        if (!$this->selectedOrgId) {
            return;
        }

        // Check if already linked
        if ($this->issue->organizations()->where('organization_id', $this->selectedOrgId)->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Organization already linked.');
            return;
        }

        $this->issue->organizations()->attach($this->selectedOrgId, [
            'role' => $this->orgRole ?: null,
            'notes' => $this->orgNotes ?: null,
        ]);

        $this->resetOrgForm();
        $this->showAddOrgModal = false;
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Organization linked!');
    }

    public function unlinkOrganization(int $orgId)
    {
        $this->issue->organizations()->detach($orgId);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Organization removed.');
    }

    // --- Person Linking ---
    public bool $showAddPersonModal = false;
    public string $personSearch = '';
    public ?int $selectedPersonId = null;
    public string $personRole = '';
    public string $personNotes = '';

    public function toggleAddPersonModal()
    {
        $this->showAddPersonModal = !$this->showAddPersonModal;
        $this->resetPersonForm();
    }

    public function resetPersonForm()
    {
        $this->personSearch = '';
        $this->selectedPersonId = null;
        $this->personRole = '';
        $this->personNotes = '';
    }

    public function selectPerson(int $personId)
    {
        $this->selectedPersonId = $personId;
        $person = Person::find($personId);
        $this->personSearch = $person ? $person->name : '';
    }

    public function linkPerson()
    {
        if (!$this->selectedPersonId) {
            return;
        }

        if ($this->issue->people()->where('person_id', $this->selectedPersonId)->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Person already linked.');
            return;
        }

        $this->issue->people()->attach($this->selectedPersonId, [
            'role' => $this->personRole ?: null,
            'notes' => $this->personNotes ?: null,
        ]);

        $this->resetPersonForm();
        $this->showAddPersonModal = false;
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Person linked!');
    }

    public function unlinkPerson(int $personId)
    {
        $this->issue->people()->detach($personId);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Person removed.');
    }

    // --- Topic Linking ---
    public bool $showAddTopicModal = false;
    public string $topicSearch = '';
    public ?int $selectedTopicId = null;

    public function toggleAddTopicModal()
    {
        $this->showAddTopicModal = !$this->showAddTopicModal;
        $this->topicSearch = '';
        $this->selectedTopicId = null;
    }

    public function selectTopic(int $topicId)
    {
        $this->selectedTopicId = $topicId;
        $topic = Topic::find($topicId);
        $this->topicSearch = $topic ? $topic->name : '';
    }

    public function linkTopic()
    {
        if (!$this->selectedTopicId) {
            return;
        }

        if ($this->issue->topics()->where('topic_id', $this->selectedTopicId)->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Topic already linked.');
            return;
        }

        $this->issue->topics()->attach($this->selectedTopicId);

        $this->toggleAddTopicModal();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Topic linked!');
    }

    public function unlinkTopic(int $topicId)
    {
        $this->issue->topics()->detach($topicId);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Topic removed.');
    }

    // --- Meeting Linking ---
    public bool $showAddMeetingModal = false;
    public string $meetingSearch = '';
    public ?int $selectedMeetingId = null;
    public string $meetingRelevance = '';

    public function toggleAddMeetingModal()
    {
        $this->showAddMeetingModal = !$this->showAddMeetingModal;
        $this->meetingSearch = '';
        $this->selectedMeetingId = null;
        $this->meetingRelevance = '';
    }

    public function selectMeeting(int $meetingId)
    {
        $this->selectedMeetingId = $meetingId;
        $meeting = Meeting::find($meetingId);
        $this->meetingSearch = $meeting ? ($meeting->title ?: $meeting->meeting_date->format('M j, Y')) : '';
    }

    public function linkMeeting()
    {
        if (!$this->selectedMeetingId) {
            return;
        }

        if ($this->issue->meetings()->where('meeting_id', $this->selectedMeetingId)->exists()) {
            $this->dispatch('notify', type: 'error', message: 'Meeting already linked.');
            return;
        }

        $this->issue->meetings()->attach($this->selectedMeetingId, [
            'relevance_note' => $this->meetingRelevance ?: null,
        ]);

        $this->toggleAddMeetingModal();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Meeting linked!');
    }

    public function unlinkMeeting(int $meetingId)
    {
        $this->issue->meetings()->detach($meetingId);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Meeting removed.');
    }

    // --- Staff Management ---
    public function toggleAddStaffModal()
    {
        $this->showAddStaffModal = !$this->showAddStaffModal;
        $this->staffSearch = '';
        $this->selectedStaffId = null;
        $this->staffRole = 'contributor';
    }

    public function selectStaff(int $userId)
    {
        $this->selectedStaffId = $userId;
        $user = User::find($userId);
        $this->staffSearch = $user?->name ?? '';
    }

    public function addStaff()
    {
        if (!$this->selectedStaffId)
            return;

        if ($this->issue->staff()->where('user_id', $this->selectedStaffId)->exists()) {
            $this->dispatch('notify', type: 'error', message: 'This person is already on the team.');
            return;
        }

        $this->issue->staff()->attach($this->selectedStaffId, [
            'role' => $this->staffRole,
            'added_at' => now(),
        ]);

        $this->issue->refresh();
        $this->toggleAddStaffModal();
        $this->dispatch('notify', type: 'success', message: 'Team member added!');
    }

    public function updateStaffRole(int $userId, string $role)
    {
        $this->issue->staff()->updateExistingPivot($userId, ['role' => $role]);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Role updated.');
    }

    public function removeStaff(int $userId)
    {
        $this->issue->staff()->detach($userId);
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Team member removed.');
    }

    // --- Documents ---
    public function toggleDocumentForm()
    {
        $this->showDocumentForm = !$this->showDocumentForm;
        $this->resetDocumentForm();
    }

    public function resetDocumentForm()
    {
        $this->documentType = 'link';
        $this->documentTitle = '';
        $this->documentDescription = '';
        $this->documentUrl = '';
        $this->documentFile = null;
    }

    public function addDocument()
    {
        $rules = [
            'documentTitle' => 'required|string|max:255',
            'documentDescription' => 'nullable|string',
            'documentType' => 'required|in:file,link',
        ];

        if ($this->documentType === 'link') {
            $rules['documentUrl'] = 'required|url';
        } else {
            $rules['documentFile'] = 'required|file|max:10240'; // 10MB max
        }

        $this->validate($rules);

        $data = [
            'issue_id' => $this->issue->id,
            'title' => $this->documentTitle,
            'description' => $this->documentDescription ?: null,
            'type' => $this->documentType,
            'uploaded_by' => auth()->id(),
        ];

        if ($this->documentType === 'link') {
            $data['url'] = $this->documentUrl;
        } else {
            $path = $this->documentFile->store('issue-documents', 'public');
            $data['file_path'] = $path;
            $data['mime_type'] = $this->documentFile->getMimeType();
            $data['file_size'] = $this->documentFile->getSize();
        }

        IssueDocument::create($data);

        $this->issue->refresh();
        $this->toggleDocumentForm();
        $this->dispatch('notify', type: 'success', message: 'Document added!');
    }

    public function deleteDocument(int $documentId)
    {
        $document = IssueDocument::findOrFail($documentId);

        if ($document->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Document deleted.');
    }

    // --- Notes ---
    public function toggleNoteForm()
    {
        $this->showNoteForm = !$this->showNoteForm;
        $this->noteContent = '';
        $this->noteType = 'general';
    }

    public function addNote()
    {
        $this->validate([
            'noteContent' => 'required|string',
            'noteType' => 'required|in:update,decision,blocker,general',
        ]);

        IssueNote::create([
            'issue_id' => $this->issue->id,
            'user_id' => auth()->id(),
            'content' => $this->noteContent,
            'note_type' => $this->noteType,
        ]);

        $this->issue->refresh();
        $this->toggleNoteForm();
        $this->dispatch('notify', type: 'success', message: 'Note added!');
    }

    public function togglePinNote(int $noteId)
    {
        $note = IssueNote::findOrFail($noteId);
        $note->update(['is_pinned' => !$note->is_pinned]);
        $this->issue->refresh();
    }

    public function deleteNote(int $noteId)
    {
        IssueNote::findOrFail($noteId)->delete();
        $this->issue->refresh();
        $this->dispatch('notify', type: 'success', message: 'Note deleted.');
    }

    // --- Issue Chat ---
    public function sendIssueChat()
    {
        if (empty(trim($this->issueChatQuery))) {
            return;
        }

        if (!config('ai.enabled')) {
            $this->issueChatHistory[] = [
                'role' => 'assistant',
                'content' => 'AI features are disabled by the administrator.',
                'timestamp' => now()->format('g:i A'),
            ];
            return;
        }

        $chatLimit = config('ai.limits.chat', ['max' => 30, 'decay_seconds' => 60]);
        $chatKey = 'ai-issue-chat:' . Auth::id() . ':' . $this->issue->id;
        if (RateLimiter::tooManyAttempts($chatKey, $chatLimit['max'])) {
            $this->issueChatHistory[] = [
                'role' => 'assistant',
                'content' => 'You are sending messages too quickly. Please wait a moment.',
                'timestamp' => now()->format('g:i A'),
            ];
            return;
        }
        RateLimiter::hit($chatKey, $chatLimit['decay_seconds']);

        $query = $this->issueChatQuery;
        $this->issueChatQuery = '';
        $this->isChatProcessing = true;

        $this->issueChatHistory[] = [
            'role' => 'user',
            'content' => $query,
            'timestamp' => now()->format('g:i A'),
        ];

        $this->dispatch('chatUpdated');

        try {
            $chatService = app(ChatService::class);
            $response = $chatService->askAboutIssue($this->issue, $query);

            $this->issueChatHistory[] = [
                'role' => 'assistant',
                'content' => $response,
                'timestamp' => now()->format('g:i A'),
            ];
        } catch (\Exception $e) {
            $this->issueChatHistory[] = [
                'role' => 'assistant',
                'content' => 'Sorry, I encountered an error processing your question.',
                'timestamp' => now()->format('g:i A'),
            ];
        }

        $this->isChatProcessing = false;
        $this->dispatch('chatUpdated');
    }

    public function clearIssueChat()
    {
        $this->issueChatHistory = [];
    }

    // --- AI Chat Methods ---
    public function loadChatHistory(): void
    {
        $messages = $this->issue->chatMessages()
            ->where('user_id', Auth::id())
            ->orderBy('created_at')
            ->get();

        $this->issueChatHistory = $messages->map(fn($m) => [
            'role' => $m->role,
            'content' => $m->content,
            'timestamp' => $m->created_at->format('g:i A'),
        ])->toArray();
    }

    public function refreshChatHistory(): void
    {
        $this->loadChatHistory();
        $this->dispatch('chatUpdated');
    }

    // --- Document Viewer Methods ---
    public function viewDocument(int $documentId): void
    {
        $document = IssueDocument::find($documentId);
        if (!$document || $document->issue_id !== $this->issue->id) {
            return;
        }

        $fullPath = base_path($document->file_path);
        if (!file_exists($fullPath)) {
            return;
        }

        $this->viewingDocumentId = $documentId;
        $this->viewingDocumentTitle = $document->title;
        $this->documentContent = file_get_contents($fullPath);
        $this->showDocumentViewer = true;
        $this->styleCheckSuggestions = [];
        $this->styleCheckComplete = false;
    }

    public function closeDocumentViewer(): void
    {
        $this->showDocumentViewer = false;
        $this->viewingDocumentId = null;
        $this->documentContent = '';
        $this->viewingDocumentTitle = '';
        $this->styleCheckSuggestions = [];
        $this->styleCheckComplete = false;
    }

    // --- Stats ---
    public function getStats(): array
    {
        return [
            'milestones_total' => $this->issue->milestones->count(),
            'milestones_completed' => $this->issue->milestones->where('status', 'completed')->count(),
            'documents' => $this->issue->documents->count(),
            'decisions' => $this->issue->decisions->count(),
            'questions_open' => $this->issue->questions->where('status', 'open')->count(),
        ];
    }

    public function render()
    {
        $openQuestions = $this->issue->questions()->where('status', 'open')->get();
        $answeredQuestions = $this->issue->questions()->where('status', 'answered')->get();
        $pendingMilestones = $this->issue->milestones()->whereIn('status', ['pending', 'in_progress'])->get();
        $completedMilestones = $this->issue->milestones()->where('status', 'completed')->get();

        // Search results for modals
        $orgResults = $this->orgSearch && strlen($this->orgSearch) >= 2
            ? Organization::where('name', 'like', '%' . $this->orgSearch . '%')->limit(10)->get()
            : collect();

        $personResults = $this->personSearch && strlen($this->personSearch) >= 2
            ? Person::with('organization')->where('name', 'like', '%' . $this->personSearch . '%')->limit(10)->get()
            : collect();

        $topicResults = $this->topicSearch && strlen($this->topicSearch) >= 2
            ? Topic::where('name', 'like', '%' . $this->topicSearch . '%')->limit(10)->get()
            : collect();

        $meetingResults = $this->meetingSearch && strlen($this->meetingSearch) >= 2
            ? Meeting::with('organizations')
                ->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->meetingSearch . '%')
                        ->orWhere('raw_notes', 'like', '%' . $this->meetingSearch . '%');
                })
                ->orderBy('meeting_date', 'desc')
                ->limit(10)
                ->get()
            : collect();

        // Staff search - all users for now
        $staffResults = $this->staffSearch && strlen($this->staffSearch) >= 2
            ? User::where('name', 'like', '%' . $this->staffSearch . '%')->limit(10)->get()
            : collect();

        return view('livewire.issues.issue-show', [
            'statuses' => Issue::STATUSES,
            'priorityLevels' => Issue::PRIORITY_LEVELS,
            'openQuestions' => $openQuestions,
            'answeredQuestions' => $answeredQuestions,
            'pendingMilestones' => $pendingMilestones,
            'completedMilestones' => $completedMilestones,
            'orgResults' => $orgResults,
            'personResults' => $personResults,
            'topicResults' => $topicResults,
            'meetingResults' => $meetingResults,
            'staffResults' => $staffResults,
            'noteTypes' => IssueNote::NOTE_TYPES,
            'staffRoles' => ['lead' => 'Lead', 'contributor' => 'Contributor', 'observer' => 'Observer'],
        ])->title($this->issue->name . ' - Issue');
    }
}



