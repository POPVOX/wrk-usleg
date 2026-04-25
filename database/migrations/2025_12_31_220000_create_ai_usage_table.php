<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('feature'); // 'meeting_summary', 'knowledge_qa', 'briefing', 'research', 'member_qa'
            $table->string('model')->nullable(); // 'claude-sonnet-4-20250514', etc.
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->text('prompt_preview')->nullable(); // First 200 chars for debugging
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['feature', 'created_at']);
        });

        // Add bonus AI credits column to users table (office-level in future)
        Schema::table('users', function (Blueprint $table) {
            $table->integer('ai_credits_bonus')->default(0)->after('is_super_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ai_credits_bonus');
        });
    }
};


