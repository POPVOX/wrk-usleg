<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beta_requests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email');
            $table->enum('role_type', ['elected_official', 'staff_member', 'other']);
            $table->string('official_name')->nullable(); // For staff members
            $table->enum('government_level', ['us_congress', 'state_legislature', 'city_municipal', 'county', 'other']);
            $table->string('government_level_other')->nullable();
            $table->string('state', 50);
            $table->string('district')->nullable();
            $table->string('primary_interest');
            $table->text('additional_info')->nullable();
            $table->string('status')->default('pending'); // pending, contacted, onboarded, declined
            $table->text('notes')->nullable(); // Internal notes
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('status');
            $table->index('government_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beta_requests');
    }
};



