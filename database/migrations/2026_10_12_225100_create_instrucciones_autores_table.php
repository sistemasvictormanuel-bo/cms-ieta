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
        Schema::create('instrucciones_autores', function (Blueprint $table) {
            $table->id();
            $table->string('ia_titulo_seccion', 150);
            $table->json('ia_contenido')->nullable(); // Texto enriquecido con viñetas, negritas, etc. (EditorJS)
            $table->integer('ia_orden')->default(1);
            $table->boolean('ia_activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrucciones_autores');
    }
};
