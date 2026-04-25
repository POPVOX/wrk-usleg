<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('document_type'); // biography, position_paper, speech, etc.
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->longText('content')->nullable();
            $table->json('metadata')->nullable();
            $table->date('document_date')->nullable();
            $table->boolean('is_public')->default(true);
            $table->string('source')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('indexed')->default(false);
            $table->text('summary')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('document_type');
            $table->index('document_date');
            $table->index('indexed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_documents');
    }
};
