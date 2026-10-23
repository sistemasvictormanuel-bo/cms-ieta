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
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->string('art_titulo');
            $table->text('art_resumen')->nullable();
            $table->text('art_abstracto')->nullable(); // campo texto enriquecido
            $table->date('art_fecha_recepcion');
            $table->date('art_fecha_aceptado')->nullable();
            $table->string('art_orcid')->unique()->nullable(); // Campo provisto por ojs debe se unico pero opcional
            $table->string('art_doi')->unique()->nullable();
            $table->integer('art_num_paginas')->nullable();
            $table->boolean('art_estado')->default(1);
            $table->text('art_referencias')->nullable(); // texto enriquecido
            $table->integer('art_vistas')->nullable(); // campo para la logica de articulos similares y mas leidos del mismo autor
            // campo art_archivo pero gestionado por spatie media library
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
