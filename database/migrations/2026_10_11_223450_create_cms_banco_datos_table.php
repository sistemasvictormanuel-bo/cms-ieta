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
        Schema::create('cms_banco_datos', function (Blueprint $table) {
            $table->id();
            $table->enum('bdd_tipo_dato', ['Censos', 'Encuestas']);
            $table->year('bdd_anio');
            $table->string('bdd_codigo');
            $table->string('bdd_titulo');
            $table->text('bdd_descripcion')->nullable();
            $table->string('bdd_locacion')->nullable(); // aca para el front debe guardarse o bien un departamento o Bolivia default no corrsponde
            $table->text('bdd_url')->nullable();
            // aarchivo bdd  manejado por laravel spatie
            // imagen_portada tambien manejado por laravel Spatie
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_banco_datos');
    }
};
