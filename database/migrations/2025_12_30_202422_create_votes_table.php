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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->string('vote_number');
            $table->string('bill_number')->nullable();
            $table->text('question');
            $table->string('vote_cast'); // YEA, NAY, PRESENT, NOT VOTING
            $table->date('vote_date');
            $table->string('result')->nullable();
            $table->integer('vote_count_yea')->default(0);
            $table->integer('vote_count_nay')->default(0);
            $table->string('congress')->default('119');
            $table->string('chamber')->default('House'); // House or Senate
            $table->string('congress_api_url')->nullable();
            $table->timestamps();

            $table->index('vote_date');
            $table->index('vote_cast');
            $table->index(['vote_number', 'congress']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
