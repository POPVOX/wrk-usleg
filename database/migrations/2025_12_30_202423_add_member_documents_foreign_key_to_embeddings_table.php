<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('member_documents') || !Schema::hasTable('member_document_embeddings')) {
            return;
        }

        if (!Schema::hasColumn('member_document_embeddings', 'member_document_id')) {
            return;
        }

        if ($this->foreignKeyExists('member_document_embeddings', 'member_document_embeddings_member_document_id_foreign')) {
            return;
        }

        Schema::table('member_document_embeddings', function (Blueprint $table) {
            $table->foreign('member_document_id', 'member_document_embeddings_member_document_id_foreign')
                ->references('id')
                ->on('member_documents')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('member_document_embeddings')) {
            return;
        }

        if (!$this->foreignKeyExists('member_document_embeddings', 'member_document_embeddings_member_document_id_foreign')) {
            return;
        }

        Schema::table('member_document_embeddings', function (Blueprint $table) {
            $table->dropForeign('member_document_embeddings_member_document_id_foreign');
        });
    }

    protected function foreignKeyExists(string $table, string $constraint): bool
    {
        return match (DB::getDriverName()) {
            'mysql' => (bool) DB::table('information_schema.table_constraints')
                ->whereRaw('constraint_schema = database()')
                ->where('table_name', $table)
                ->where('constraint_name', $constraint)
                ->where('constraint_type', 'FOREIGN KEY')
                ->exists(),
            'pgsql' => (bool) DB::table('pg_constraint as c')
                ->join('pg_class as t', 'c.conrelid', '=', 't.oid')
                ->join('pg_namespace as n', 't.relnamespace', '=', 'n.oid')
                ->where('t.relname', $table)
                ->where('c.conname', $constraint)
                ->where('c.contype', 'f')
                ->exists(),
            default => false,
        };
    }
};
