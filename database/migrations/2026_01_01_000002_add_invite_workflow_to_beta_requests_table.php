<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beta_requests', function (Blueprint $table) {
            $table->string('invite_token')->nullable()->unique()->after('notes');
            $table->timestamp('invite_expires_at')->nullable()->after('invite_token');
            $table->timestamp('approved_at')->nullable()->after('invite_expires_at');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('declined_at')->nullable()->after('approved_by');
            $table->foreignId('declined_by')->nullable()->after('declined_at')->constrained('users')->nullOnDelete();
            $table->timestamp('onboarded_at')->nullable()->after('declined_by');
            $table->foreignId('onboarded_user_id')->nullable()->after('onboarded_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beta_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('onboarded_user_id');
            $table->dropColumn('onboarded_at');
            $table->dropConstrainedForeignId('declined_by');
            $table->dropColumn('declined_at');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn('approved_at');
            $table->dropColumn('invite_expires_at');
            $table->dropUnique(['invite_token']);
            $table->dropColumn('invite_token');
        });
    }
};
