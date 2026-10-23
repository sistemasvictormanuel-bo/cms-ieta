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
        Schema::create('cms_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('ct_titulo');
            $table->foreignId('tipo_curso_id')->constrained('tipo_cursos')->restrictOnDelete();
            $table->foreignId('modalidad_curso_id')->constrained('curso_modalidades')->restrictOnDelete();

            $table->foreignId('docente_id')->nullable()->constrained('personas')->nullOnDelete();

            $table->date('ct_fecha_inicio');
            $table->date('ct_fecha_fin');
            $table->time('ct_hora_inicio')->nullable();
            $table->time('ct_hora_fin')->nullable();
            $table->string('ct_duracion')->nullable();
            $table->text('ct_descripcion')->nullable();
            $table->json('ct_contenido');
            $table->string('ct_email_info')->nullable();
            $table->string('ct_telefono_ref', 20)->nullable();
            $table->string('ct_slug')->unique();
            $table->boolean('ct_activo')->default(true);
            $table->decimal('ct_costo', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            // campo portada_curso gestionada por laravel spatie media library
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
