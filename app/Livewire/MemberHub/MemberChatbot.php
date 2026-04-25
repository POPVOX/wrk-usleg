<?php

namespace App\Livewire\MemberHub;

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\MemberKnowledgeService;
use App\Models\MemberDocument;

/**
 * Member Chatbot Component
 * 
 * AI-powered chatbot that answers questions about the Member using RAG
 * over the document library and live data.
 */
#[Layout('layouts.app')]
class MemberChatbot extends Component
{
    public array $messages = [];
    public string $newMessage = '';
    public bool $isProcessing = false;

    protected MemberKnowledgeService $memberKnowledge;

    public function boot(MemberKnowledgeService $memberKnowledge)
    {
        $this->memberKnowledge = $memberKnowledge;
    }

    public function mount()
    {
        $memberName = config('office.member_name', 'the Member');

        $this->messages[] = [
            'role' => 'assistant',
            'content' => "Hi! I can answer any question about {$memberName}. I have access to their biography, positions, statements, voting record, and more. What would you like to know?",
            'timestamp' => now()->toIso8601String(),
            'sources' => [],
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->newMessage))) {
            return;
        }

        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->newMessage,
            'timestamp' => now()->toIso8601String(),
        ];

        $question = $this->newMessage;
        $this->newMessage = '';
        $this->isProcessing = true;

        // Get answer from knowledge service
        $result = $this->memberKnowledge->answerQuestion($question);

        // Add assistant response
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $result['answer'],
            'sources' => $result['sources']->toArray(),
            'contextUsed' => $result['context_used'],
            'timestamp' => now()->toIso8601String(),
        ];

        $this->isProcessing = false;
    }

    public function askQuickQuestion(string $question)
    {
        $this->newMessage = $question;
        $this->sendMessage();
    }

    public function clearConversation()
    {
        $memberName = config('office.member_name', 'the Member');

        $this->messages = [
            [
                'role' => 'assistant',
                'content' => "Conversation cleared! I'm ready to answer questions about {$memberName}.",
                'timestamp' => now()->toIso8601String(),
                'sources' => [],
            ]
        ];
    }

    /**
     * Get quick question suggestions.
     */
    public function getQuickQuestionsProperty(): array
    {
        return [
            "Where did they go to school?",
            "What's their career before Congress?",
            "Military service?",
            "Position on climate change?",
            "Recent legislation?",
            "How did they vote on the latest bill?",
            "Family background?",
            "District connection?",
        ];
    }

    /**
     * Get indexed document count.
     */
    public function getDocumentCountProperty(): int
    {
        return MemberDocument::indexed()->count();
    }

    public function render()
    {
        return view('livewire.member-hub.member-chatbot', [
            'quickQuestions' => $this->quickQuestions,
            'documentCount' => $this->documentCount,
        ]);
    }
}
