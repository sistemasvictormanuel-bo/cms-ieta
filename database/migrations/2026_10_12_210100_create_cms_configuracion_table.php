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
        Schema::create('cms_configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('cfg_nombre_institucion')->default('Instituto de Estadística Teórica y Aplicada');
            $table->string('cfg_siglas')->default('IETA');
            $table->string('cfg_slogan')->nullable()->default('Investigación, rigor científico y análisis estadístico');
            $table->text('cfg_descripcion_corta')->nullable();
            $table->string('cfg_email_principal')->nullable();
            $table->string('cfg_telefono_principal')->nullable();
            $table->string('cfg_direccion_principal')->nullable();
            $table->json('cfg_redes_sociales')->nullable();
            $table->string('cfg_copyright')->nullable()->default('© Todos los derechos reservados. Instituto de Estadística Teórica y Aplicada (IETA)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_configuracion');
    }
};
