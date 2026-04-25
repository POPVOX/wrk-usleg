<?php

namespace App\Services;

use App\Models\MemberDocument;
use App\Models\MemberDocumentEmbedding;
use App\Models\MemberStatement;
use App\Models\Bill;
use App\Models\Vote;
use App\Models\PressClip;
use App\Support\AI\AnthropicClient;
use App\Support\AI\OpenAiClient;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MemberKnowledgeService
{
    protected $anthropicApiKey;

    public function __construct()
    {
        $this->anthropicApiKey = config('services.anthropic.api_key');
    }

    /**
     * Index a document for semantic search.
     */
    public function indexDocument(MemberDocument $document): bool
    {
        try {
            // Extract text if it's a file and content is empty
            if ($document->file_path && !$document->content) {
                $document->content = $this->extractTextFromFile($document->file_path);
                $document->save();
            }

            if (empty($document->content)) {
                Log::warning("MemberKnowledgeService: Document {$document->id} has no content to index");
                return false;
            }

            // Split into chunks
            $chunks = $this->chunkText($document->content, 800);

            // Delete existing embeddings
            MemberDocumentEmbedding::where('member_document_id', $document->id)->delete();

            // Create embeddings for each chunk
            foreach ($chunks as $index => $chunk) {
                $embedding = $this->createEmbedding($chunk);

                MemberDocumentEmbedding::create([
                    'member_document_id' => $document->id,
                    'chunk_text' => $chunk,
                    'chunk_index' => $index,
                    'embedding' => $embedding,
                    'metadata' => [
                        'document_type' => $document->document_type,
                        'document_date' => $document->document_date?->format('Y-m-d'),
                        'document_title' => $document->title,
                    ],
                ]);
            }

            $document->update(['indexed' => true]);

            return true;
        } catch (\Exception $e) {
            Log::error("MemberKnowledgeService: Error indexing document {$document->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search for relevant document chunks.
     */
    public function searchMemberKnowledge(string $query, int $limit = 5): \Illuminate\Support\Collection
    {
        $queryEmbedding = $this->createEmbedding($query);

        if (empty($queryEmbedding)) {
            return collect([]);
        }

        $embeddings = MemberDocumentEmbedding::with('document')->get();

        $results = $embeddings->map(function ($embedding) use ($queryEmbedding) {
            if (empty($embedding->embedding)) {
                return null;
            }

            $similarity = $this->cosineSimilarity(
                $queryEmbedding,
                $embedding->embedding
            );

            return [
                'chunk' => $embedding->chunk_text,
                'similarity' => $similarity,
                'document' => $embedding->document,
                'metadata' => $embedding->metadata,
            ];
        })
            ->filter()
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();

        return $results;
    }

    /**
     * Answer a question using RAG.
     */
    public function answerQuestion(string $question): array
    {
        // 1. Search for relevant document context
        $relevantChunks = $this->searchMemberKnowledge($question, 5);

        // 2. Also include dynamic data based on question type
        $dynamicContext = $this->getDynamicContext($question);

        // 3. Build context from document chunks
        $documentContext = $relevantChunks->map(function ($result) {
            return "From \"{$result['metadata']['document_title']}\" ({$result['metadata']['document_type']}):\n{$result['chunk']}";
        })->join("\n\n---\n\n");

        // 4. Combine contexts
        $fullContext = $documentContext;
        if (!empty($dynamicContext)) {
            $fullContext .= "\n\n=== Live Data ===\n\n" . $dynamicContext;
        }

        // 5. Build sources list
        $sources = $relevantChunks->map(function ($result) {
            return [
                'title' => $result['metadata']['document_title'],
                'type' => $result['metadata']['document_type'],
                'date' => $result['metadata']['document_date'],
                'document_id' => $result['document']->id,
            ];
        })->unique('document_id')->values();

        // 6. Call Claude with context
        $memberName = config('office.member_name');
        $memberTitle = config('office.member_title', 'Representative');

        $systemPrompt = <<<PROMPT
You are an AI assistant with comprehensive knowledge about {$memberName}. You help congressional staff find information about their Member.

Guidelines:
- Only use information from the provided context
- Cite which document or data source your information comes from
- If you don't have the information, say so clearly
- Be specific and factual
- Note dates when discussing positions or statements
- Be helpful and professional

Member: {$memberName}
State: {$this->getMemberInfo('state')}
District: {$this->getMemberInfo('district')}
Party: {$this->getMemberInfo('party')}

Available Context:
{$fullContext}
PROMPT;

        if (empty($this->anthropicApiKey)) {
            return [
                'answer' => 'AI features are not configured. Please set up the Anthropic API key.',
                'sources' => $sources,
                'context_used' => $relevantChunks->count(),
            ];
        }

        try {
            $response = AnthropicClient::send([
                'max_tokens' => 2000,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ],
            ]);

            $answer = $response['content'][0]['text'] ?? 'Sorry, I encountered an error generating a response.';

            return [
                'answer' => $answer,
                'sources' => $sources,
                'context_used' => $relevantChunks->count(),
            ];
        } catch (\Exception $e) {
            Log::error("MemberKnowledgeService: Error answering question: " . $e->getMessage());

            return [
                'answer' => 'Sorry, I encountered an error. Please try again.',
                'sources' => $sources,
                'context_used' => $relevantChunks->count(),
            ];
        }
    }

    /**
     * Get dynamic context based on question type.
     */
    protected function getDynamicContext(string $query): string
    {
        $context = [];
        $queryLower = strtolower($query);

        // Include legislative data if relevant
        if (
            str_contains($queryLower, 'bill') || str_contains($queryLower, 'sponsor') ||
            str_contains($queryLower, 'legislation') || str_contains($queryLower, 'introduce')
        ) {
            $bills = Bill::orderBy('introduced_date', 'desc')->take(10)->get();
            if ($bills->isNotEmpty()) {
                $context[] = "LEGISLATIVE ACTIVITY:\n" . $bills->map(
                    fn($b) =>
                    "- {$b->bill_number}: {$b->title} ({$b->sponsor_role}, {$b->introduced_date->format('M j, Y')}) - Status: {$b->status}"
                )->join("\n");
            }
        }

        // Include voting record if relevant
        if (
            str_contains($queryLower, 'vote') || str_contains($queryLower, 'voted') ||
            str_contains($queryLower, 'position') || str_contains($queryLower, 'support')
        ) {
            $votes = Vote::orderBy('vote_date', 'desc')->take(15)->get();
            if ($votes->isNotEmpty()) {
                $context[] = "RECENT VOTES:\n" . $votes->map(
                    fn($v) =>
                    "- {$v->vote_date->format('M j, Y')}: {$v->question} - Voted: {$v->vote_cast}"
                )->join("\n");
            }
        }

        // Include statements if relevant
        if (
            str_contains($queryLower, 'said') || str_contains($queryLower, 'state') ||
            str_contains($queryLower, 'comment') || str_contains($queryLower, 'statement') ||
            str_contains($queryLower, 'press') || str_contains($queryLower, 'release')
        ) {
            $statements = MemberStatement::orderBy('published_date', 'desc')->take(10)->get();
            if ($statements->isNotEmpty()) {
                $context[] = "RECENT STATEMENTS:\n" . $statements->map(
                    fn($s) =>
                    "- {$s->published_date->format('M j, Y')}: [{$s->type_label}] {$s->title}" .
                    ($s->excerpt ? "\n  \"" . \Str::limit($s->excerpt, 200) . "\"" : "")
                )->join("\n");
            }
        }

        // Include media coverage if relevant
        if (
            str_contains($queryLower, 'media') || str_contains($queryLower, 'news') ||
            str_contains($queryLower, 'cover') || str_contains($queryLower, 'article')
        ) {
            $clips = PressClip::where('publish_date', '>=', Carbon::now()->subMonths(3))
                ->orderBy('publish_date', 'desc')
                ->take(5)
                ->get();
            if ($clips->isNotEmpty()) {
                $context[] = "RECENT MEDIA COVERAGE:\n" . $clips->map(function ($c) {
                    $outletName = $c->outlet?->name ?? 'Unknown';
                    return "- {$c->publish_date->format('M j, Y')} ({$outletName}): {$c->headline}";
                })->join("\n");
            }
        }

        return implode("\n\n", $context);
    }

    /**
     * Generate document summary.
     */
    public function generateDocumentSummary(MemberDocument $document): string
    {
        if (empty($document->content)) {
            return '';
        }

        $memberName = config('office.member_name');

        $prompt = "Please provide a concise 2-3 sentence summary of this document about {$memberName}.\n\nDocument Title: {$document->title}\nDocument Type: {$document->type_label}\n\nContent:\n" . \Str::limit($document->content, 4000);

        try {
            $response = AnthropicClient::send([
                'max_tokens' => 300,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

            $summary = $response['content'][0]['text'] ?? '';
            $document->update(['summary' => $summary]);

            return $summary;
        } catch (\Exception $e) {
            Log::error("MemberKnowledgeService: Error generating summary: " . $e->getMessage());
            return '';
        }
    }

    /**
     * Extract text from a file.
     */
    protected function extractTextFromFile(string $filePath): string
    {
        $fullPath = storage_path('app/' . $filePath);

        if (!file_exists($fullPath)) {
            return '';
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'txt':
            case 'md':
                return file_get_contents($fullPath);

            case 'pdf':
                try {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($fullPath);
                    return $pdf->getText();
                } catch (\Exception $e) {
                    Log::error("Error parsing PDF: " . $e->getMessage());
                    return '';
                }

            default:
                return '';
        }
    }

    /**
     * Split text into chunks for embedding.
     */
    protected function chunkText(string $text, int $maxWords = 800): array
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', $text);
        $chunks = [];
        $currentChunk = '';
        $currentWords = 0;

        foreach ($sentences as $sentence) {
            $sentenceWords = str_word_count($sentence);

            if ($currentWords + $sentenceWords > $maxWords && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $sentence;
                $currentWords = $sentenceWords;
            } else {
                $currentChunk .= ' ' . $sentence;
                $currentWords += $sentenceWords;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Create embedding using OpenAI.
     */
    protected function createEmbedding(string $text): array
    {
        if (empty(config('services.openai.api_key'))) {
            return [];
        }

        try {
            return OpenAiClient::embedding(\Str::limit($text, 8000));
        } catch (\Exception $e) {
            Log::error("MemberKnowledgeService: Error creating embedding: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        if (count($vectorA) !== count($vectorB) || empty($vectorA)) {
            return 0;
        }

        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dotProduct += $vectorA[$i] * $vectorB[$i];
            $magnitudeA += $vectorA[$i] * $vectorA[$i];
            $magnitudeB += $vectorB[$i] * $vectorB[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Get member info from config.
     */
    protected function getMemberInfo(string $key): string
    {
        return match ($key) {
            'state' => config('office.member_state', 'Unknown'),
            'district' => config('office.member_district', 'Unknown'),
            'party' => config('office.member_party', 'Unknown'),
            default => '',
        };
    }
}
