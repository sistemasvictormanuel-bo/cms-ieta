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
        Schema::create('memorias_investigaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->constrained('cms_autores')->cascadeOnDelete();
            $table->string('mdi_titulo');
            $table->text('mdi_descripcion')->nullable();
            $table->string('mdi_etiqueta'); // se arma solo en la vista
            $table->date('mdi_fecha');
            $table->string('mdi_enlace_url')->nullable(); // para guardar enlaces externos de la investigacion
            // campo mdi_archivo por laravel media spatie
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memorias_investigaciones');
    }
};
