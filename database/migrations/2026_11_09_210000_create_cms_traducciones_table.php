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
        Schema::create('cms_traducciones', function (Blueprint $table) {
            $table->id();
            $table->string('modulo', 50)->default('general')->index(); // navegacion, hero, general, revista, investigacion, banco_datos, contacto, etc.
            $table->string('clave', 100)->index(); // nav_inicio, btn_buscar, etc.
            $table->text('texto_es'); // Traducción en Español
            $table->text('texto_en'); // Traducción en Inglés
            $table->string('descripcion', 255)->nullable(); // Contexto de uso para los editores
            $table->timestamps();

            $table->unique(['modulo', 'clave'], 'cms_traducciones_modulo_clave_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_traducciones');
    }
};
