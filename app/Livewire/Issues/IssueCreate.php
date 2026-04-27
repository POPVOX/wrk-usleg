<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Create Issue')]
class IssueCreate extends Component
{
    public string $name = '';
    public string $description = '';
    public string $goals = '';
    public ?string $start_date = null;
    public ?string $target_end_date = null;
    public string $status = 'active';

    // New fields
    public string $scope = 'District';
    public string $lead = '';
    public string $url = '';
    public string $tagsInput = ''; // Comma-separated tags

    // Hierarchy fields
    public ?int $parent_issue_id = null;
    public string $issue_type = 'initiative';

    // Congressional fields
    public string $committee_relevance = '';
    public string $legislative_vehicle = '';
    public string $priority_level = 'Tracking';

    // AI Extraction
    public bool $showAiExtract = false;
    public string $freeText = '';
    public bool $isExtracting = false;
    public bool $hasExtracted = false;

    // Duplicate tracking
    public bool $isDuplicate = false;
    public string $sourceIssueName = '';

    public function mount(?Issue $issue = null): void
    {
        if ($issue && $issue->exists) {
            // Pre-fill form with data from source issue
            $this->isDuplicate = true;
            $this->sourceIssueName = $issue->name;
            $this->name = $issue->name . ' (Copy)';
            $this->description = $issue->description ?? '';
            $this->goals = $issue->goals ?? '';
            $this->status = 'planning'; // Start as planning
            $this->scope = $issue->scope ?? 'District';
            $this->lead = $issue->lead ?? '';
            $this->url = $issue->url ?? '';
            $this->parent_issue_id = $issue->parent_issue_id;
            $this->issue_type = $issue->issue_type ?? 'initiative';
            $this->committee_relevance = $issue->committee_relevance ?? '';
            $this->legislative_vehicle = $issue->legislative_vehicle ?? '';
            $this->priority_level = $issue->priority_level ?? 'Tracking';

            // Convert tags array back to comma-separated string
            if ($issue->tags && is_array($issue->tags)) {
                $this->tagsInput = implode(', ', $issue->tags);
            }
        }
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'goals' => 'nullable|string',
        'start_date' => 'nullable|date',
        'target_end_date' => 'nullable|date|after_or_equal:start_date',
        'status' => 'required|in:planning,active,on_hold,completed,archived',
        'scope' => 'nullable|string|max:50',
        'lead' => 'nullable|string|max:100',
        'url' => 'nullable|url|max:500',
        'tagsInput' => 'nullable|string',
        'parent_issue_id' => 'nullable|exists:issues,id',
        'issue_type' => 'required|string',
        'committee_relevance' => 'nullable|string|max:255',
        'legislative_vehicle' => 'nullable|string|max:255',
        'priority_level' => 'required|in:Tracking,District Priority,Top Priority',
    ];

    public function toggleAiExtract()
    {
        $this->showAiExtract = !$this->showAiExtract;
    }

    public function extractFromText()
    {
        if (empty(trim($this->freeText))) {
            $this->dispatch('notify', type: 'error', message: 'Please enter some text to extract from.');
            return;
        }

        $this->isExtracting = true;

        try {
            $response = Http::withHeaders([
                'x-api-key' => config('services.anthropic.api_key'),
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                        'model' => config('services.anthropic.model'),
                        'max_tokens' => 1500,
                        'system' => $this->getExtractionSystemPrompt(),
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => "Extract issue details from this text:\n\n{$this->freeText}",
                            ]
                        ]
                    ]);

            $content = $response->json('content.0.text');

            if ($content) {
                $this->parseExtractedData($content);
                $this->hasExtracted = true;
                $this->showAiExtract = false;
                $this->dispatch('notify', type: 'success', message: 'Issue details extracted! Review and adjust as needed.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Could not extract issue details. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Issue AI extraction error: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error during extraction. Please try again.');
        }

        $this->isExtracting = false;
    }

    protected function getExtractionSystemPrompt(): string
    {
        return <<<PROMPT
You are an assistant that extracts structured policy issue information from free-form text for a congressional office.

Extract the following fields from the provided text:
1. **title**: A concise issue name/title (max 100 chars)
2. **description**: A clear description of what the issue is about
3. **goals**: Specific goals or objectives (as bullet points or numbered list)
4. **start_date**: Start date if mentioned (format: YYYY-MM-DD)
5. **end_date**: Target end date or deadline if mentioned (format: YYYY-MM-DD)
6. **status**: One of: planning, active, on_hold, completed, archived (default to "planning" if unclear)
7. **scope**: One of: District, National (based on whether it's district-focused or national)
8. **lead**: The lead staff member's first name if mentioned
9. **committee_relevance**: Which congressional committee this relates to (if any)
10. **legislative_vehicle**: Any bill numbers, resolutions, or legislative vehicles mentioned
11. **priority_level**: One of: Tracking, District Priority, Top Priority
12. **tags**: An array of relevant themes/tags

Return your response in this exact JSON format:
```json
{
    "title": "...",
    "description": "...",
    "goals": "...",
    "start_date": "YYYY-MM-DD or null",
    "end_date": "YYYY-MM-DD or null",
    "status": "planning",
    "scope": "District",
    "lead": "name or null",
    "committee_relevance": "...",
    "legislative_vehicle": "...",
    "priority_level": "Tracking",
    "tags": ["tag1", "tag2"]
}
```
PROMPT;
    }

    protected function parseExtractedData(string $content): void
    {
        // Try to extract JSON from the response
        if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
            $jsonStr = $matches[1];
        } elseif (preg_match('/\{.*\}/s', $content, $matches)) {
            $jsonStr = $matches[0];
        } else {
            return;
        }

        try {
            $data = json_decode($jsonStr, true);

            if (!$data) {
                return;
            }

            // Populate form fields
            if (!empty($data['title'])) {
                $this->name = $data['title'];
            }
            if (!empty($data['description'])) {
                $this->description = $data['description'];
            }
            if (!empty($data['goals'])) {
                $this->goals = $data['goals'];
            }
            if (!empty($data['start_date']) && $data['start_date'] !== 'null') {
                $this->start_date = $data['start_date'];
            }
            if (!empty($data['end_date']) && $data['end_date'] !== 'null') {
                $this->target_end_date = $data['end_date'];
            }
            if (!empty($data['status'])) {
                $this->status = $data['status'];
            }
            if (!empty($data['scope'])) {
                $this->scope = $data['scope'];
            }
            if (!empty($data['lead'])) {
                $this->lead = $data['lead'];
            }
            if (!empty($data['committee_relevance'])) {
                $this->committee_relevance = $data['committee_relevance'];
            }
            if (!empty($data['legislative_vehicle'])) {
                $this->legislative_vehicle = $data['legislative_vehicle'];
            }
            if (!empty($data['priority_level'])) {
                $this->priority_level = $data['priority_level'];
            }
            if (!empty($data['tags']) && is_array($data['tags'])) {
                $this->tagsInput = implode(', ', $data['tags']);
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing extracted issue data: ' . $e->getMessage());
        }
    }

    public function save()
    {
        $this->validate();

        // Parse tags from comma-separated string
        $tags = [];
        if (!empty($this->tagsInput)) {
            $tags = array_map('trim', explode(',', $this->tagsInput));
            $tags = array_filter($tags); // Remove empty values
        }

        $issue = Issue::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'goals' => $this->goals ?: null,
            'start_date' => $this->start_date ?: null,
            'target_end_date' => $this->target_end_date ?: null,
            'status' => $this->status,
            'scope' => $this->scope ?: null,
            'lead' => $this->lead ?: null,
            'url' => $this->url ?: null,
            'tags' => !empty($tags) ? $tags : null,
            'created_by' => auth()->id(),
            'parent_issue_id' => $this->parent_issue_id,
            'issue_type' => $this->issue_type,
            'committee_relevance' => $this->committee_relevance ?: null,
            'legislative_vehicle' => $this->legislative_vehicle ?: null,
            'priority_level' => $this->priority_level,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Issue created successfully!');

        return $this->redirect(route('issues.show', $issue), navigate: true);
    }

    public function render()
    {
        return view('livewire.issues.issue-create', [
            'statuses' => Issue::STATUSES,
            'priorityLevels' => Issue::PRIORITY_LEVELS,
            'parentIssues' => Issue::roots()->orderBy('name')->get(),
            'issueTypes' => [
                'initiative' => 'Initiative',
                'legislation' => 'Legislation',
                'constituent' => 'Constituent Service',
                'publication' => 'Publication',
                'event' => 'Event',
                'research' => 'Research',
                'outreach' => 'Outreach',
            ],
            'scopeOptions' => ['District', 'National', 'Committee', 'Bipartisan', 'Caucus'],
            'leadOptions' => \App\Models\User::orderBy('name')->pluck('name')->toArray(),
        ]);
    }
}


