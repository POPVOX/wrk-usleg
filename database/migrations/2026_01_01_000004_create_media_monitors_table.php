<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media_monitors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('query');
            $table->enum('monitor_type', ['member', 'topic', 'issue', 'custom'])->default('custom');
            $table->enum('source_type', ['google_news_rss'])->default('google_news_rss');
            $table->enum('cadence', ['hourly', 'three_times_daily', 'daily'])->default('hourly');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_approve')->default(false);
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_clip_at')->nullable();
            $table->unsignedInteger('clips_found')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'cadence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_monitors');
    }
};
