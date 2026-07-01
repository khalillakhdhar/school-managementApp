<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 4/9 — multi-tenant isolation for unique constraints.
 *
 * These columns were globally unique, which would stop a second school from
 * reusing a value that legitimately repeats per school (level code "1AP",
 * subject code "MATH", a national holiday date, a blog slug). Convert each to a
 * composite unique scoped by school_id so every school has its own namespace.
 *
 * Composite uniques referencing tenant-owned ids (grades, attendances, …) are
 * already effectively per-tenant and are left untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['school_id', 'code']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->unique(['school_id', 'code']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['school_id', 'slug']);
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropUnique(['date']);
            $table->unique(['school_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'code']);
            $table->unique(['code']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'code']);
            $table->unique(['code']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'slug']);
            $table->unique(['slug']);
        });

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'date']);
            $table->unique(['date']);
        });
    }
};
