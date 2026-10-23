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
        Schema::create('cms_comunicados', function (Blueprint $table) {
            $table->id();
            $table->string('com_titulo');
            $table->text('com_descripcion')->nullable();
            $table->string('com_enlace_url')->nullable();
            $table->string('com_texto_boton')->nullable()->default('Más información');
            $table->string('com_tipo')->default('general'); // general, curso, articulo, convocatoria, noticia
            $table->date('com_fecha_inicio');
            $table->date('com_fecha_fin')->nullable();
            $table->boolean('com_activo')->default(true);
            $table->unsignedInteger('com_vistas')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_comunicados');
    }
};
