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
        Schema::create('cms_servicios', function (Blueprint $table) {
            $table->id();
            $table->string('sv_titulo');
            $table->text('sv_descripcion')->nullable();
            $table->json('sv_contenido');
            // CAMPO IMAGEN sv_imagen por media spatie
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_servicios');
    }
};
