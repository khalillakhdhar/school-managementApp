<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 7 — introduce the `platform_admin` role (SaaS super-admin, above tenants).
 *
 * `role` was an ENUM (admin/parent/teacher/employee) with a hard CHECK/enum
 * constraint. Widen it to a plain string so new roles can be added without a
 * schema change on every driver. Native change() rebuilds the constraint on
 * both MySQL and SQLite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('admin')->change();
        });
    }
};
