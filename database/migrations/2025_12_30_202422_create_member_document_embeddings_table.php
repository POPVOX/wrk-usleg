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
        Schema::create('member_document_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_document_id')->constrained()->onDelete('cascade');
            $table->text('chunk_text');
            $table->integer('chunk_index');
            $table->json('embedding')->nullable(); // Vector embedding for semantic search
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('member_document_id');
            $table->index('chunk_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_document_embeddings');
    }
};
