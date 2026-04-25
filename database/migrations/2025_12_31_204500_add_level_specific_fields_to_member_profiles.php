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
        if (!Schema::hasTable('member_profiles')) {
            return;
        }

        Schema::table('member_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('member_profiles', 'skip_positioning')) {
                $table->boolean('skip_positioning')->default(false)->after('use_in_prompts');
            }

            if (!Schema::hasColumn('member_profiles', 'session_type')) {
                $table->string('session_type')->nullable()->after('skip_positioning');
            }
            if (!Schema::hasColumn('member_profiles', 'other_occupation')) {
                $table->string('other_occupation')->nullable()->after('session_type');
            }
            if (!Schema::hasColumn('member_profiles', 'state_federal_issues')) {
                $table->json('state_federal_issues')->nullable()->after('other_occupation');
            }

            if (!Schema::hasColumn('member_profiles', 'local_role_type')) {
                $table->string('local_role_type')->nullable()->after('state_federal_issues');
            }
            if (!Schema::hasColumn('member_profiles', 'governance_structure')) {
                $table->string('governance_structure')->nullable()->after('local_role_type');
            }
            if (!Schema::hasColumn('member_profiles', 'admin_relationship')) {
                $table->string('admin_relationship')->nullable()->after('governance_structure');
            }
            if (!Schema::hasColumn('member_profiles', 'boards_commissions')) {
                $table->json('boards_commissions')->nullable()->after('admin_relationship');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('member_profiles')) {
            return;
        }

        Schema::table('member_profiles', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('member_profiles', 'skip_positioning') ? 'skip_positioning' : null,
                Schema::hasColumn('member_profiles', 'session_type') ? 'session_type' : null,
                Schema::hasColumn('member_profiles', 'other_occupation') ? 'other_occupation' : null,
                Schema::hasColumn('member_profiles', 'state_federal_issues') ? 'state_federal_issues' : null,
                Schema::hasColumn('member_profiles', 'local_role_type') ? 'local_role_type' : null,
                Schema::hasColumn('member_profiles', 'governance_structure') ? 'governance_structure' : null,
                Schema::hasColumn('member_profiles', 'admin_relationship') ? 'admin_relationship' : null,
                Schema::hasColumn('member_profiles', 'boards_commissions') ? 'boards_commissions' : null,
            ]));

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

