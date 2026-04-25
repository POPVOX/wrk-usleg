<?php

namespace App\Livewire\MemberHub;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\MemberDocument;
use App\Services\MemberKnowledgeService;
use Illuminate\Support\Facades\Auth;

/**
 * Document Library Component
 * 
 * Centralized repository for all documents about the Member.
 */
#[Layout('layouts.app')]
class DocumentLibrary extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public string $filterType = '';
    public bool $showUploadModal = false;
    public bool $showDetailModal = false;
    public ?MemberDocument $selectedDocument = null;

    // Upload form fields
    public string $title = '';
    public string $document_type = '';
    public ?string $document_date = null;
    public string $description = '';
    public $file = null;
    public string $content = '';
    public array $tags = [];
    public bool $is_public = true;
    public string $source = '';
    public bool $auto_index = true;
    public bool $generate_summary = true;

    protected MemberKnowledgeService $memberKnowledge;

    protected $rules = [
        'title' => 'required|string|max:255',
        'document_type' => 'required|string',
        'document_date' => 'nullable|date',
        'description' => 'nullable|string|max:1000',
        'file' => 'nullable|file|max:10240', // 10MB max
        'content' => 'nullable|string',
        'is_public' => 'boolean',
        'source' => 'nullable|string|max:255',
    ];

    public function boot(MemberKnowledgeService $memberKnowledge)
    {
        $this->memberKnowledge = $memberKnowledge;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    /**
     * Get documents with search and filters.
     */
    public function getDocumentsProperty()
    {
        $query = MemberDocument::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('summary', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('document_type', $this->filterType);
        }

        return $query->orderBy('document_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Get document types for filter.
     */
    public function getDocumentTypesProperty(): array
    {
        return MemberDocument::TYPES;
    }

    /**
     * Get stats for the library.
     */
    public function getLibraryStatsProperty(): array
    {
        return [
            'total' => MemberDocument::count(),
            'indexed' => MemberDocument::indexed()->count(),
            'needs_indexing' => MemberDocument::needsIndexing()->count(),
        ];
    }

    public function openUploadModal()
    {
        $this->resetUploadForm();
        $this->showUploadModal = true;
    }

    public function closeUploadModal()
    {
        $this->showUploadModal = false;
        $this->resetUploadForm();
    }

    protected function resetUploadForm()
    {
        $this->title = '';
        $this->document_type = '';
        $this->document_date = null;
        $this->description = '';
        $this->file = null;
        $this->content = '';
        $this->tags = [];
        $this->is_public = true;
        $this->source = '';
        $this->auto_index = true;
        $this->generate_summary = true;
    }

    public function uploadDocument()
    {
        $this->validate();

        // Require either file or content
        if (!$this->file && empty($this->content)) {
            $this->addError('content', 'Please upload a file or provide text content.');
            return;
        }

        $filePath = null;
        if ($this->file) {
            $filePath = $this->file->store('member-documents');
        }

        $document = MemberDocument::create([
            'title' => $this->title,
            'document_type' => $this->document_type,
            'description' => $this->description,
            'file_path' => $filePath,
            'content' => $this->content ?: null,
            'document_date' => $this->document_date,
            'is_public' => $this->is_public,
            'source' => $this->source ?: null,
            'tags' => $this->tags,
            'uploaded_by' => Auth::id(),
        ]);

        if ($this->generate_summary && !empty($document->content)) {
            $this->memberKnowledge->generateDocumentSummary($document);
        }

        if ($this->auto_index) {
            $this->memberKnowledge->indexDocument($document);
        }

        session()->flash('message', 'Document uploaded successfully!');
        $this->closeUploadModal();
    }

    public function viewDocument(int $documentId)
    {
        $this->selectedDocument = MemberDocument::findOrFail($documentId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedDocument = null;
    }

    public function reindexDocument(int $documentId)
    {
        $document = MemberDocument::findOrFail($documentId);
        $this->memberKnowledge->indexDocument($document);
        session()->flash('message', 'Document re-indexed successfully!');
    }

    public function generateSummary(int $documentId)
    {
        $document = MemberDocument::findOrFail($documentId);
        $this->memberKnowledge->generateDocumentSummary($document);
        session()->flash('message', 'Summary generated!');
    }

    public function deleteDocument(int $documentId)
    {
        $document = MemberDocument::findOrFail($documentId);

        // Delete file if exists
        if ($document->file_path) {
            \Storage::delete($document->file_path);
        }

        // Embeddings will cascade delete
        $document->delete();

        session()->flash('message', 'Document deleted.');
    }

    public function render()
    {
        return view('livewire.member-hub.document-library', [
            'documents' => $this->documents,
            'documentTypes' => $this->documentTypes,
            'libraryStats' => $this->libraryStats,
        ]);
    }
}
