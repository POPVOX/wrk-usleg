<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("
                CREATE VIRTUAL TABLE IF NOT EXISTS kb_index USING fts5(
                    doc_id UNINDEXED,
                    issue_id UNINDEXED,
                    title,
                    body,
                    tokenize = 'unicode61'
                )
            ");

            return;
        }

        if (Schema::hasTable('kb_index')) {
            return;
        }

        Schema::create('kb_index', function (Blueprint $table) {
            $table->unsignedBigInteger('doc_id')->primary();
            $table->unsignedBigInteger('issue_id')->nullable();
            $table->text('title')->nullable();
            $table->longText('body')->nullable();
        });

        DB::statement('CREATE INDEX kb_index_issue_id_index ON kb_index (issue_id)');

        if ($driver === 'pgsql') {
            DB::statement("
                CREATE INDEX kb_index_search_idx
                ON kb_index
                USING GIN (to_tsvector('english', coalesce(title, '') || ' ' || coalesce(body, '')))
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS kb_index');
            return;
        }

        Schema::dropIfExists('kb_index');
    }
};
