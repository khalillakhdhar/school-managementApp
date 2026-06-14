<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // e.g. "1ère Année"
            $table->string('code')->unique(); // e.g. "1AP"
            $table->unsignedTinyInteger('order')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('levels');
    }
};
