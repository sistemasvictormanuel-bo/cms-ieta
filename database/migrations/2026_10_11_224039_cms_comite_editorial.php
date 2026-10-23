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
        Schema::create('cms_comite_editoriales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('cargo_id')->constrained('cargo_institucionales')->cascadeOnDelete();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->enum('ce_tipo', ['Nacional', 'Internacional', 'Otro']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_comite_editoriales');
    }
};
