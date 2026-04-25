<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Models\IssueChatMessage;
use App\Support\AI\AnthropicClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $issueId;
    public int $userId;
    public string $prompt;
    public string $system;

    public $timeout = 90;

    public function __construct(int $issueId, int $userId, string $prompt, string $system)
    {
        $this->issueId = $issueId;
        $this->userId = $userId;
        $this->prompt = $prompt;
        $this->system = $system;
    }

    public function handle(): void
    {
        $issue = Issue::find($this->issueId);
        if (!$issue) {
            Log::warning('SendChatMessage: Issue not found', ['issue_id' => $this->issueId]);
            return;
        }

        $response = AnthropicClient::send([
            'system' => $this->system,
            'messages' => [
                ['role' => 'user', 'content' => $this->prompt],
            ],
            'max_tokens' => 2000,
        ]);

        $text = $response['content'][0]['text'] ?? 'No response generated.';

        IssueChatMessage::create([
            'issue_id' => $this->issueId,
            'user_id' => $this->userId,
            'role' => 'assistant',
            'content' => $text,
        ]);

        Log::info('SendChatMessage: assistant message saved', [
            'issue_id' => $this->issueId,
            'user_id' => $this->userId,
        ]);
    }
}
