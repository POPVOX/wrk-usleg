<?php

namespace App\Livewire\Admin;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('How AI Works')]
class HowAiWorks extends Component
{
    public bool $aiEnabled = true;
    public bool $showEnableModal = false;
    public bool $showDisableModal = false;
    public bool $confirmationChecked = false;
    
    public ?string $viewingPrompt = null;
    public string $customPrompt = '';

    public array $prompts = [
        'meeting_summaries' => [
            'name' => 'Meeting Summaries',
            'description' => 'Generate concise summaries of meeting notes',
            'system_prompt' => "You are an assistant helping a legislative office summarize meeting notes.

Guidelines:
- Be concise and focus on key takeaways
- Highlight action items and commitments
- Note any follow-up needed
- Do not include information not present in the source notes
- Do not speculate or add external context
- Use professional, neutral language",
            'explanations' => [
                '"Do not include information not present in the source"' => 'This instruction reduces "hallucination"—when AI generates plausible but made-up details.',
                '"Highlight action items and commitments"' => 'Legislative staff told us the most useful summaries focus on what happens next, not just what was said.',
                '"Professional, neutral language"' => 'Ensures output is appropriate for official use.',
            ],
        ],
        'briefing_preparation' => [
            'name' => 'Briefing Preparation',
            'description' => 'Create background briefings for upcoming meetings',
            'system_prompt' => "You are an assistant helping prepare briefings for a legislative office.

Guidelines:
- Summarize relevant background from provided documents
- Highlight key talking points
- Note any known positions or past interactions
- Flag potential sensitive topics
- Keep briefings scannable and actionable
- Do not include information not in the provided context",
            'explanations' => [
                '"Summarize relevant background from provided documents"' => 'Ensures the AI only uses information from your office\'s knowledge base.',
                '"Flag potential sensitive topics"' => 'Helps staff prepare for difficult conversations.',
                '"Keep briefings scannable"' => 'Optimized for quick review before meetings.',
            ],
        ],
        'knowledge_qa' => [
            'name' => 'Knowledge Q&A',
            'description' => 'Answer questions about your office\'s documents',
            'system_prompt' => "You are an assistant helping answer questions about a legislative office's documents and history.

Guidelines:
- Only answer based on the provided context
- If the answer isn't in the context, say so clearly
- Cite specific documents when possible
- Be precise about dates and facts
- Never make up information
- If uncertain, express that uncertainty",
            'explanations' => [
                '"Only answer based on the provided context"' => 'Prevents the AI from inventing answers or pulling from general knowledge.',
                '"If the answer isn\'t in the context, say so"' => 'Builds trust by being honest about limitations.',
                '"Cite specific documents"' => 'Allows staff to verify and dive deeper.',
            ],
        ],
        'issue_research' => [
            'name' => 'Issue Research',
            'description' => 'Summarize policy issues and background',
            'system_prompt' => "You are an assistant helping research policy issues for a legislative office.

Guidelines:
- Provide balanced, factual summaries
- Present multiple perspectives when relevant
- Note key stakeholders and their positions
- Highlight legislative history if available
- Flag areas where information is limited
- Do not advocate for positions",
            'explanations' => [
                '"Provide balanced, factual summaries"' => 'Ensures research is useful regardless of political perspective.',
                '"Present multiple perspectives"' => 'Helps staff understand the full landscape of an issue.',
                '"Do not advocate for positions"' => 'Keeps the AI as a neutral research tool.',
            ],
        ],
    ];

    public function mount(): void
    {
        $this->aiEnabled = config('office.features.ai_enabled', true);
    }

    public function toggleAiModal(): void
    {
        if ($this->aiEnabled) {
            $this->showDisableModal = true;
        } else {
            $this->showEnableModal = true;
        }
    }

    public function enableAi(): void
    {
        if ($this->confirmationChecked) {
            $this->aiEnabled = true;
            $this->showEnableModal = false;
            $this->confirmationChecked = false;
            // In production, this would update a database setting
        }
    }

    public function disableAi(): void
    {
        $this->aiEnabled = false;
        $this->showDisableModal = false;
        // In production, this would update a database setting
    }

    public function viewPrompt(string $key): void
    {
        $this->viewingPrompt = $key;
        $this->customPrompt = ''; // In production, load from database
    }

    public function closePromptView(): void
    {
        $this->viewingPrompt = null;
        $this->customPrompt = '';
    }

    public function saveCustomPrompt(): void
    {
        // In production, save to database
        session()->flash('message', 'Custom instructions saved.');
    }

    public function render()
    {
        return view('livewire.admin.how-ai-works', [
            'anthropicModel' => (string) config('services.anthropic.model'),
            'openAiChatModel' => (string) config('services.openai.chat_model'),
            'openAiEmbeddingModel' => (string) config('services.openai.embedding_model'),
        ]);
    }
}


