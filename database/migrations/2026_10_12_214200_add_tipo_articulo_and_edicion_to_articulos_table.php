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
        Schema::table('articulos', function (Blueprint $table) {
            $table->foreignId('tipo_articulo_id')
                ->nullable()
                ->after('id')
                ->constrained('tipo_articulos')
                ->nullOnDelete();

            $table->foreignId('edicion_varianza_id')
                ->nullable()
                ->after('tipo_articulo_id')
                ->constrained('ediciones_varianzas')
                ->nullOnDelete();

            $table->integer('art_pag_inicio')->nullable()->after('art_num_paginas');
            $table->integer('art_pag_fin')->nullable()->after('art_pag_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropForeign(['tipo_articulo_id']);
            $table->dropForeign(['edicion_varianza_id']);
            $table->dropColumn(['tipo_articulo_id', 'edicion_varianza_id', 'art_pag_inicio', 'art_pag_fin']);
        });
    }
};
