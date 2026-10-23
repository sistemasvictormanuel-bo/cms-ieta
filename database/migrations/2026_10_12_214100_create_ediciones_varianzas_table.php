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
        Schema::create('ediciones_varianzas', function (Blueprint $table) {
            $table->id();
            $table->string('ev_titulo');
            $table->string('ev_numero', 50);
            $table->string('ev_volumen', 50)->nullable();
            $table->integer('ev_anio');
            $table->string('ev_mes', 100)->nullable();
            $table->string('ev_slug')->unique();
            $table->string('ev_issn_ver_impresa')->nullable();
            $table->string('ev_issn_ver_electronica')->nullable();
            $table->integer('ev_num_art_originales')->nullable();
            $table->integer('ev_notas_cientificas')->nullable();
            $table->integer('ev_num_paginas')->nullable();
            $table->string('ev_doi')->nullable();
            $table->json('ev_presentacion')->nullable(); // Editor.js
            $table->text('ev_editorial')->nullable();
            $table->date('ev_fecha_publicacion')->nullable();
            $table->boolean('ev_estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ediciones_varianzas');
    }
};
