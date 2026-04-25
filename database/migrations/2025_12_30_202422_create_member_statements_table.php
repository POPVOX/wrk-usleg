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
        if (!Schema::hasTable('member_statements')) {
            Schema::create('member_statements', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // 'press_release', 'statement', 'op_ed', 'floor_speech', 'interview'
                $table->string('title');
                $table->text('content')->nullable();
                $table->text('excerpt')->nullable();
                $table->date('published_date');
                $table->string('outlet')->nullable(); // Media outlet if applicable
                $table->string('url')->nullable();
                $table->json('related_issues')->nullable(); // Issue IDs this relates to
                $table->json('topics')->nullable(); // Topic tags
                $table->boolean('is_public')->default(true);
                $table->timestamps();

                $table->index('type');
                $table->index('published_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_statements');
    }
};
