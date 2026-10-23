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
        Schema::create('cms_lineas_investigaciones', function (Blueprint $table) {
            $table->id();
            $table->string('li_titulo');
            $table->string('li_descripcion');
            $table->json('li_contenido'); // texto enriquecido RIch editor
            $table->string('li_slug'); // autogenerado
            // campo de media  spatie  li_imagen
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_lineas_investigaciones');
    }
};
