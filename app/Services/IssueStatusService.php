<?php

namespace App\Services;

use App\Models\Issue;
use App\Support\AI\AnthropicClient;
use Illuminate\Support\Facades\Cache;

class IssueStatusService
{
    public function generateStatus(Issue $issue): string
    {
        if (!config('ai.enabled')) {
            $summary = 'AI status generation is currently disabled.';
            $issue->update([
                'ai_status_summary' => $summary,
                'ai_status_generated_at' => now(),
            ]);
            return $summary;
        }

        $context = $this->buildContext($issue);
        $cacheKey = 'ai:issue_status:' . $issue->id;

        try {
            $response = AnthropicClient::send([
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $this->buildPrompt($issue, $context),
                    ]
                ],
                'max_tokens' => 300,
            ]);

            $summary = $response['content'][0]['text'] ?? null;

            if (!$summary) {
                $summary = 'Unable to generate status summary.';
            } else {
                Cache::put($cacheKey, $summary, now()->addHours(6));
            }
        } catch (\Exception $e) {
            $cached = Cache::get($cacheKey);
            $summary = $cached ?? 'Status generation temporarily unavailable.';
            \Log::error('IssueStatusService error: ' . $e->getMessage());
        }

        // Cache the result
        $issue->update([
            'ai_status_summary' => $summary,
            'ai_status_generated_at' => now(),
        ]);

        return $summary;
    }

    protected function buildContext(Issue $issue): array
    {
        // Load all relevant data
        $issue->load([
            'meetings' => fn($q) => $q->latest('meeting_date')->limit(5),
            'meetings.organizations',
            'decisions' => fn($q) => $q->latest('decision_date')->limit(3),
            'milestones',
            'questions' => fn($q) => $q->where('status', 'open'),
            'organizations',
            'people',
        ]);

        return [
            'recent_meetings' => $issue->meetings->map(fn($m) => [
                'date' => $m->meeting_date?->format('M j, Y'),
                'orgs' => $m->organizations->pluck('name')->join(', '),
                'summary' => $m->ai_summary ?? substr($m->raw_notes ?? '', 0, 300),
                'key_ask' => $m->key_ask,
            ]),
            'recent_decisions' => $issue->decisions->map(fn($d) => [
                'title' => $d->title,
                'date' => $d->decision_date?->format('M j, Y'),
                'rationale' => $d->rationale,
            ]),
            'milestones' => [
                'completed' => $issue->milestones->where('status', 'completed')->count(),
                'pending' => $issue->milestones->where('status', '!=', 'completed')->values()->map(fn($m) => [
                    'title' => $m->title,
                    'due_date' => $m->due_date?->format('M j, Y'),
                    'is_overdue' => $m->is_overdue,
                ]),
            ],
            'open_questions' => $issue->questions->where('status', 'open')->pluck('question'),
            'organizations_count' => $issue->organizations->count(),
            'people_count' => $issue->people->count(),
        ];
    }

    protected function buildPrompt(Issue $issue, array $context): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are providing a brief status update for a policy issue being tracked by a congressional office. Be concise and actionable.

ISSUE: {$issue->name}
PRIORITY: {$issue->priority_level}
DESCRIPTION: {$issue->description}
GOALS: {$issue->goals}

RECENT CONTEXT:
{$contextJson}

Write a 2-3 sentence status summary that captures:
1. Current momentum (active, stalled, wrapping up?)
2. Most important recent development
3. What needs attention next (if anything)

Be specific and concrete. Don't be generic. If there are overdue milestones or open questions, mention them.

Respond with ONLY the summary, no preamble.
PROMPT;
    }

    public function refreshIfNeeded(Issue $issue): string
    {
        if ($issue->needsStatusRefresh()) {
            return $this->generateStatus($issue);
        }

        return $issue->ai_status_summary ?? $this->generateStatus($issue);
    }
}



