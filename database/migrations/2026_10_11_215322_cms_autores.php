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
        Schema::create('cms_autores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('tipo_autor_id')->constrained('tipo_autores')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('grado_academico_id')->constrained('grado_academicos')->cascadeOnUpdate()->restrictOnDelete();
            $table->json('at_biografia')->nullable(); // rich editor
            $table->string('at_slug')->unique(); // autogenerado
            $table->string('at_intereses_cientificos')->nullable();
            $table->boolean('at_estado')->default(true); // visible en el sitio
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_autores');
    }
};
