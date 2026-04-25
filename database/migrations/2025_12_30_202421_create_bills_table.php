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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->string('bill_type'); // HR, S, HRES, SRES, etc.
            $table->text('title');
            $table->string('sponsor_role'); // 'sponsor' or 'cosponsor'
            $table->date('introduced_date');
            $table->string('status');
            $table->text('summary')->nullable();
            $table->string('committee')->nullable();
            $table->json('cosponsors')->nullable();
            $table->string('congress')->default('119');
            $table->string('congress_api_url')->nullable();
            $table->timestamps();

            $table->index('bill_type');
            $table->index('sponsor_role');
            $table->index('introduced_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
