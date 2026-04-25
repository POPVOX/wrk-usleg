<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\Issue;
use App\Models\IssueDecision;
use App\Models\Organization;
use App\Models\Person;
use App\Models\Topic;
use App\Support\AI\AnthropicClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ChatService
{
    public function query(string $query, array $conversationHistory = []): string
    {
        if (!config('ai.enabled')) {
            return 'AI features are disabled by the administrator.';
        }

        // Step 1: Retrieve relevant context from database
        $context = $this->retrieveContext($query);

        // Step 2: Build prompt with context
        $prompt = $this->buildPrompt($query, $context, $conversationHistory);
        $cacheKey = 'ai:chat:' . md5($prompt);

        try {
            $response = AnthropicClient::send([
                'system' => $this->getSystemPrompt(),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ]
                ],
                'max_tokens' => 1000,
            ]);

            $text = $response['content'][0]['text'] ?? null;
            if ($text) {
                Cache::put($cacheKey, $text, now()->addMinutes(30));
                return $text;
            }

            $cached = Cache::get($cacheKey);
            return $cached
                ? $cached . "\n\n(Served from cache while AI response was empty.)"
                : 'Sorry, I encountered an error processing your request.';
        } catch (\Exception $e) {
            \Log::error('ChatService error: ' . $e->getMessage());
            $cached = Cache::get($cacheKey);
            return $cached
                ? $cached . "\n\n(Served from cache because AI is unavailable.)"
                : 'Sorry, I encountered an error processing your request. Please try again.';
        }
    }

    protected function retrieveContext(string $query): array
    {
        $context = [];
        $like = $this->likeOperator();

        // Extract potential search terms
        $searchTerms = $this->extractSearchTerms($query);

        // Search meetings
        $meetings = Meeting::query()
            ->where(function ($q) use ($searchTerms, $query, $like) {
                $q->where('ai_summary', $like, "%{$query}%")
                    ->orWhere('raw_notes', $like, "%{$query}%")
                    ->orWhere('transcript', $like, "%{$query}%")
                    ->orWhere('key_ask', $like, "%{$query}%");

                foreach ($searchTerms as $term) {
                    $q->orWhere('ai_summary', $like, "%{$term}%")
                        ->orWhere('raw_notes', $like, "%{$term}%");
                }
            })
            ->with(['organizations', 'people', 'issues'])
            ->latest('meeting_date')
            ->limit(10)
            ->get();

        if ($meetings->isNotEmpty()) {
            $context['meetings'] = $meetings->map(fn($m) => [
                'date' => $m->meeting_date?->format('M j, Y'),
                'organizations' => $m->organizations->pluck('name')->join(', '),
                'people' => $m->people->pluck('name')->join(', '),
                'issues' => $m->issues->pluck('name')->join(', '),
                'summary' => Str::limit($m->ai_summary ?? $m->raw_notes, 500),
                'key_ask' => $m->key_ask,
            ])->toArray();
        }

        // Search issues (renamed from projects)
        $issues = Issue::query()
            ->where(function ($q) use ($searchTerms, $query, $like) {
                $q->where('name', $like, "%{$query}%")
                    ->orWhere('description', $like, "%{$query}%")
                    ->orWhere('goals', $like, "%{$query}%");

                foreach ($searchTerms as $term) {
                    $q->orWhere('name', $like, "%{$term}%")
                        ->orWhere('description', $like, "%{$term}%");
                }
            })
            ->with(['milestones', 'openQuestions'])
            ->limit(5)
            ->get();

        if ($issues->isNotEmpty()) {
            $context['issues'] = $issues->map(fn($i) => [
                'name' => $i->name,
                'status' => $i->status,
                'priority' => $i->priority_level,
                'description' => Str::limit($i->description, 300),
                'goals' => $i->goals,
                'ai_status' => $i->ai_status_summary,
                'pending_milestones' => $i->milestones->where('status', '!=', 'completed')->pluck('title'),
                'open_questions' => $i->openQuestions->pluck('question'),
            ])->toArray();
        }

        // Search decisions
        $decisions = IssueDecision::query()
            ->where(function ($q) use ($searchTerms, $query, $like) {
                $q->where('title', $like, "%{$query}%")
                    ->orWhere('description', $like, "%{$query}%")
                    ->orWhere('rationale', $like, "%{$query}%")
                    ->orWhere('context', $like, "%{$query}%");

                foreach ($searchTerms as $term) {
                    $q->orWhere('title', $like, "%{$term}%")
                        ->orWhere('rationale', $like, "%{$term}%");
                }
            })
            ->with('issue')
            ->latest('decision_date')
            ->limit(10)
            ->get();

        if ($decisions->isNotEmpty()) {
            $context['decisions'] = $decisions->map(fn($d) => [
                'title' => $d->title,
                'issue' => $d->issue?->name,
                'date' => $d->decision_date?->format('M j, Y'),
                'description' => $d->description,
                'rationale' => $d->rationale,
            ])->toArray();
        }

        // Search organizations
        $organizations = Organization::query()
            ->where(function ($q) use ($searchTerms, $query, $like) {
                $q->where('name', $like, "%{$query}%")
                    ->orWhere('notes', $like, "%{$query}%");

                foreach ($searchTerms as $term) {
                    $q->orWhere('name', $like, "%{$term}%");
                }
            })
            ->withCount('meetings')
            ->with('people')
            ->limit(5)
            ->get();

        if ($organizations->isNotEmpty()) {
            $context['organizations'] = $organizations->map(fn($o) => [
                'name' => $o->name,
                'type' => $o->type,
                'meetings_count' => $o->meetings_count,
                'people' => $o->people->map(fn($p) => $p->name . ($p->title ? " ({$p->title})" : ''))->join(', '),
                'notes' => Str::limit($o->notes, 200),
            ])->toArray();
        }

        // Search people
        $people = Person::query()
            ->where(function ($q) use ($searchTerms, $query, $like) {
                $q->where('name', $like, "%{$query}%")
                    ->orWhere('title', $like, "%{$query}%")
                    ->orWhere('notes', $like, "%{$query}%");

                foreach ($searchTerms as $term) {
                    $q->orWhere('name', $like, "%{$term}%");
                }
            })
            ->with('organization')
            ->withCount('meetings')
            ->limit(5)
            ->get();

        if ($people->isNotEmpty()) {
            $context['people'] = $people->map(fn($p) => [
                'name' => $p->name,
                'title' => $p->title,
                'organization' => $p->organization?->name,
                'meetings_count' => $p->meetings_count,
                'notes' => Str::limit($p->notes, 200),
            ])->toArray();
        }

        // Search topics
        $topics = Topic::query()
            ->where('name', $like, "%{$query}%")
            ->withCount('meetings')
            ->limit(5)
            ->get();

        if ($topics->isNotEmpty()) {
            $context['topics'] = $topics->map(fn($t) => [
                'name' => $t->name,
                'meetings_count' => $t->meetings_count,
            ])->toArray();
        }

        // Optional FTS fallback from knowledge base (SQLite FTS5)
        try {
            if (DB::getDriverName() === 'sqlite') {
                $kbMatches = DB::select(
                    'SELECT doc_id, title, snippet(kb_index, 3, "[[", "]]", "...", 8) AS snippet
                     FROM kb_index
                     WHERE kb_index MATCH ?
                     LIMIT 5',
                    [$query]
                );

                if (!empty($kbMatches)) {
                    $context['kb_matches'] = collect($kbMatches)->map(fn($row) => [
                        'doc_id' => $row->doc_id,
                        'title' => $row->title,
                        'snippet' => $row->snippet,
                    ])->toArray();
                }
            } elseif (DB::getDriverName() === 'pgsql') {
                $kbMatches = DB::select(
                    "SELECT doc_id,
                            title,
                            ts_headline(
                                'english',
                                coalesce(body, ''),
                                websearch_to_tsquery('english', ?),
                                'StartSel=[[,StopSel=]],MaxFragments=2,MaxWords=8,MinWords=3'
                            ) AS snippet
                     FROM kb_index
                     WHERE to_tsvector('english', coalesce(title, '') || ' ' || coalesce(body, ''))
                        @@ websearch_to_tsquery('english', ?)
                     LIMIT 5",
                    [$query, $query]
                );

                if (!empty($kbMatches)) {
                    $context['kb_matches'] = collect($kbMatches)->map(fn($row) => [
                        'doc_id' => $row->doc_id,
                        'title' => $row->title,
                        'snippet' => $row->snippet,
                    ])->toArray();
                }
            }
        } catch (\Throwable $e) {
            \Log::debug('ChatService FTS lookup skipped', ['error' => $e->getMessage()]);
        }

        // If query asks about "recent" or time-based, add recent meetings regardless of search match
        if (Str::contains(strtolower($query), ['recent', 'last week', 'last month', 'this week', 'today', 'yesterday', 'latest'])) {
            $recentMeetings = Meeting::with(['organizations', 'issues'])
                ->latest('meeting_date')
                ->limit(10)
                ->get();

            $context['recent_meetings'] = $recentMeetings->map(fn($m) => [
                'date' => $m->meeting_date?->format('M j, Y'),
                'organizations' => $m->organizations->pluck('name')->join(', '),
                'issues' => $m->issues->pluck('name')->join(', '),
                'summary' => Str::limit($m->ai_summary ?? $m->raw_notes, 300),
            ])->toArray();
        }

        // If query asks about "pending" or "todo" or "action", add pending actions and milestones
        if (Str::contains(strtolower($query), ['pending', 'todo', 'action', 'follow up', 'overdue', 'due'])) {
            $pendingActions = \App\Models\Action::where('status', 'pending')
                ->with('meeting.organizations')
                ->orderBy('due_date')
                ->limit(10)
                ->get();

            $context['pending_actions'] = $pendingActions->map(fn($a) => [
                'description' => $a->description,
                'due_date' => $a->due_date?->format('M j, Y'),
                'priority' => $a->priority,
                'from_meeting' => $a->meeting?->organizations->pluck('name')->join(', '),
            ])->toArray();

            // Also get overdue milestones
            $overdueMilestones = \App\Models\IssueMilestone::where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->with('issue')
                ->get();

            $context['overdue_milestones'] = $overdueMilestones->map(fn($m) => [
                'title' => $m->title,
                'issue' => $m->issue?->name,
                'due_date' => $m->due_date?->format('M j, Y'),
            ])->toArray();
        }

        // Add summary stats
        $context['system_stats'] = [
            'total_meetings' => Meeting::count(),
            'total_issues' => Issue::where('status', 'active')->count(),
            'total_organizations' => Organization::count(),
            'total_people' => Person::count(),
            'open_questions' => \App\Models\IssueQuestion::where('status', 'open')->count(),
            'pending_actions' => \App\Models\Action::where('status', 'pending')->count(),
        ];

        return $context;
    }

    protected function extractSearchTerms(string $query): array
    {
        // Remove common words and extract meaningful terms
        $stopWords = ['what', 'who', 'where', 'when', 'how', 'why', 'is', 'are', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'about', 'have', 'has', 'had', 'do', 'does', 'did', 'our', 'we', 'us', 'me', 'my', 'i', 'you', 'your', 'they', 'them', 'their', 'it', 'its', 'this', 'that', 'these', 'those', 'from', 'been', 'being', 'was', 'were', 'will', 'would', 'could', 'should', 'can', 'may', 'might', 'must'];

        $words = preg_split('/\s+/', strtolower($query));
        $terms = array_filter(
            $words,
            fn($word) =>
            strlen($word) > 2 && !in_array($word, $stopWords)
        );

        return array_values($terms);
    }

    protected function getSystemPrompt(): string
    {
        return <<<PROMPT
You are an AI assistant for a Congressional Office management tool. You help users understand their meetings, issues, relationships, and decisions.

Your knowledge base includes:
- Meetings (with summaries, organizations involved, key asks)
- Issues (policy issues being tracked, with status, milestones, decisions, open questions)
- Organizations and People (contacts, relationships)
- Decisions (what was decided, why, in what context)
- Topics being tracked

When answering:
1. Be specific and reference actual data when available
2. If you find relevant meetings, mention dates and who was involved
3. If you find relevant decisions, explain the rationale
4. If information isn't found, say so clearly
5. Be concise but complete
6. If the user asks about something you don't have data on, suggest what they might search for

You're helping a congressional office team stay on top of their work. Be helpful and practical.
PROMPT;
    }

    protected function buildPrompt(string $query, array $context, array $conversationHistory): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT);

        // Include recent conversation for continuity
        $recentHistory = '';
        if (!empty($conversationHistory)) {
            $recent = array_slice($conversationHistory, -4); // Last 2 exchanges
            foreach ($recent as $msg) {
                $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
                $recentHistory .= "{$role}: {$msg['content']}\n\n";
            }
        }

        $prompt = "RETRIEVED CONTEXT:\n{$contextJson}\n\n";

        if ($recentHistory) {
            $prompt .= "RECENT CONVERSATION:\n{$recentHistory}\n";
        }

        $prompt .= "USER QUESTION: {$query}\n\n";
        $prompt .= "Please answer based on the retrieved context. If the context doesn't contain relevant information, say so.";

        return $prompt;
    }

    /**
     * Answer questions about a specific issue with full context
     */
    public function askAboutIssue(Issue $issue, string $query): string
    {
        if (!config('ai.enabled')) {
            return 'AI features are disabled by the administrator.';
        }

        // Load issue with all relevant relationships
        $issue->load([
            'meetings.organizations',
            'meetings.people',
            'organizations',
            'people.organization',
            'staff',
            'documents',
            'notes.user',
            'decisions',
            'milestones',
            'questions',
            'topics',
        ]);

        // Build comprehensive issue context
        $context = [
            'issue' => [
                'name' => $issue->name,
                'status' => $issue->status,
                'priority' => $issue->priority_level,
                'description' => $issue->description,
                'goals' => $issue->goals,
                'committee_relevance' => $issue->committee_relevance,
                'legislative_vehicle' => $issue->legislative_vehicle,
                'start_date' => $issue->start_date?->format('M j, Y'),
                'target_end_date' => $issue->target_end_date?->format('M j, Y'),
                'ai_status_summary' => $issue->ai_status_summary,
            ],
            'team' => $issue->staff->map(fn($s) => [
                'name' => $s->name,
                'role' => $s->pivot->role,
            ])->toArray(),
            'meetings' => $issue->meetings->map(fn($m) => [
                'date' => $m->meeting_date?->format('M j, Y'),
                'organizations' => $m->organizations->pluck('name')->join(', '),
                'people' => $m->people->pluck('name')->join(', '),
                'summary' => Str::limit($m->ai_summary ?? $m->raw_notes, 400),
                'key_ask' => $m->key_ask,
            ])->toArray(),
            'organizations' => $issue->organizations->map(fn($o) => [
                'name' => $o->name,
                'role' => $o->pivot->role,
            ])->toArray(),
            'external_collaborators' => $issue->people->map(fn($p) => [
                'name' => $p->name,
                'title' => $p->title,
                'organization' => $p->organization?->name,
            ])->toArray(),
            'decisions' => $issue->decisions->map(fn($d) => [
                'title' => $d->title,
                'date' => $d->decision_date?->format('M j, Y'),
                'description' => $d->description,
                'rationale' => $d->rationale,
            ])->toArray(),
            'milestones' => $issue->milestones->map(fn($m) => [
                'title' => $m->title,
                'status' => $m->status,
                'due_date' => $m->due_date?->format('M j, Y'),
            ])->toArray(),
            'open_questions' => $issue->questions->where('status', 'open')->map(fn($q) => [
                'question' => $q->question,
                'context' => $q->context,
            ])->values()->toArray(),
            'notes' => $issue->notes->take(20)->map(fn($n) => [
                'type' => $n->note_type,
                'content' => Str::limit($n->content, 300),
                'author' => $n->user?->name,
                'date' => $n->created_at->format('M j, Y'),
                'pinned' => $n->is_pinned,
            ])->toArray(),
            'documents' => $issue->documents->map(fn($d) => [
                'title' => $d->title,
                'type' => $d->type,
                'description' => $d->description,
            ])->toArray(),
            'topics' => $issue->topics->pluck('name')->toArray(),
        ];

        $contextJson = json_encode($context, JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You are an AI assistant helping with issue tracking for a congressional office.

You are answering questions specifically about the issue "{$issue->name}".

You have full context about this issue including:
- Meetings held and their summaries
- Team members and collaborators
- Decisions made and their rationale
- Milestones and their status
- Open questions
- Issue notes and updates
- Related documents
- Connected organizations and topics

When answering:
1. Be specific and reference actual data from the issue
2. Mention dates, people, and organizations when relevant
3. If the information isn't in the context, say so
4. Be concise but complete
5. Help the user understand the current state of the issue
PROMPT;

        $cacheKey = 'ai:issue_chat:' . $issue->id . ':' . md5($query);

        try {
            $response = AnthropicClient::send([
                'system' => $systemPrompt,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "ISSUE CONTEXT:\n{$contextJson}\n\nQUESTION: {$query}",
                    ]
                ],
                'max_tokens' => 1500,
            ]);

            $text = $response['content'][0]['text'] ?? null;
            if ($text) {
                Cache::put($cacheKey, $text, now()->addMinutes(30));
                return $text;
            }

            $cached = Cache::get($cacheKey);
            return $cached
                ? $cached . "\n\n(Served from cache while AI response was empty.)"
                : 'Sorry, I encountered an error processing your request.';
        } catch (\Exception $e) {
            \Log::error('ChatService askAboutIssue error: ' . $e->getMessage());
            $cached = Cache::get($cacheKey);
            return $cached
                ? $cached . "\n\n(Served from cache because AI is unavailable.)"
                : 'Sorry, I encountered an error processing your request. Please try again.';
        }
    }

    /**
     * Case-insensitive operator for PostgreSQL; fallback elsewhere.
     */
    protected function likeOperator(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
    }
}
