<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 2 — Migration A : add a NULLABLE school_id to every tenant-owned table.
 *
 * Kept nullable on purpose: the production panels (/admin, /parent, /staff) are
 * not yet tenant-aware (that is Phase 4), so records created there cannot be
 * stamped automatically yet. The non-nullable flip is a later migration (Phase 5),
 * after tenancy is live on the real panels and a final backfill is done.
 *
 * `students` already received school_id in Phase 0 (spike) and is excluded here.
 */
return new class extends Migration
{
    /** Tenant-owned tables (excluding students, already done in Phase 0). */
    private array $tables = [
        'parents', 'employees', 'classrooms', 'levels', 'subjects',
        'payments', 'payrolls', 'expenses', 'expense_categories',
        'incidents', 'attendances', 'student_attendances', 'holidays',
        'blog_posts', 'services', 'timetable_entries', 'grades',
        'audit_logs', 'school_settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('school_id')
                    ->nullable()
                    ->constrained('schools')
                    ->cascadeOnDelete();
                $blueprint->index('school_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('school_id');
            });
        }
    }
};
