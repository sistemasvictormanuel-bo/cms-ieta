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
        Schema::create('cms_revista_varianzas', function (Blueprint $table) {
            $table->id();
            $table->string('rv_titulo')->default('Revista Varianza');
            $table->string('rv_subtitulo')->nullable();
            $table->text('rv_mision')->nullable();
            $table->text('rv_vision')->nullable();
            $table->json('rv_contenido')->nullable(); // Editor.js
            $table->json('rv_alcance')->nullable();   // Editor.js
            $table->json('rv_politicas')->nullable(); // Editor.js
            $table->string('rv_issn_impreso')->nullable();
            $table->string('rv_issn_electronico')->nullable();
            $table->string('rv_periodicidad')->nullable(); // Ej: Semestral
            $table->string('rv_correo_contacto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_revista_varianzas');
    }
};
