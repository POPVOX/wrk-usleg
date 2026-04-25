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
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('office_name')->nullable();
            
            // Feedback content
            $table->enum('type', ['bug', 'feature', 'general'])->default('general');
            $table->text('message');
            $table->string('screenshot_path')->nullable();
            
            // Context data
            $table->string('page_url')->nullable();
            $table->string('page_title')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('screen_resolution')->nullable();
            $table->json('console_errors')->nullable();
            
            // Status tracking
            $table->enum('status', ['new', 'reviewing', 'in_progress', 'resolved', 'closed'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};


