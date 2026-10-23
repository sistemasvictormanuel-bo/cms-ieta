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
        Schema::create('cms_contactos', function (Blueprint $table) {
            $table->id();
            $table->json('cc_telefono');
            $table->string('cc_dia_inicio');
            $table->string('cc_dia_fin');
            $table->time('cc_hora_apertura');
            $table->time('cc_hora_cierre');
            $table->text('cc_direccion')->nullable();
            $table->json('cc_email');
            $table->json('cc_contenido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_contactos');
    }
};
