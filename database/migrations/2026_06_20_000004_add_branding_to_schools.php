<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PHASE 1 — per-tenant branding + contact identity on the School (tenant) model.
 * Append-only (the schools table already exists from Phase 0).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('subdomain');
            $table->string('primary_color', 9)->nullable()->after('logo_path'); // #RRGGBB(AA)
            $table->string('email')->nullable()->after('primary_color');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('city')->nullable()->after('phone');
            $table->string('country')->nullable()->default('Tunisie')->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'primary_color', 'email', 'phone', 'city', 'country']);
        });
    }
};
