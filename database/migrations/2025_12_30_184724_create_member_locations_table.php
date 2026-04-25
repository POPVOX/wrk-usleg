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
        if (!Schema::hasTable('member_locations')) {
            Schema::create('member_locations', function (Blueprint $table) {
                $table->id();
                $table->string('location_name'); // "Washington, DC" or "District Office 1"
                $table->string('timezone')->default('America/New_York');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('current_activity')->nullable(); // "In committee"
                $table->timestamp('activity_until')->nullable();
                $table->boolean('is_current')->default(false); // Only one should be true
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                // Index for quick lookup of current location
                $table->index('is_current');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_locations');
    }
};
