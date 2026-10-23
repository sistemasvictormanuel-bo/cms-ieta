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
        Schema::create('cms_galerias', function (Blueprint $table) {
            $table->id();
            $table->string('gal_titulo', 255);
            $table->text('gal_descripcion')->nullable();
            $table->string('gal_categoria', 100)->default('General');
            $table->date('gal_fecha')->nullable();
            $table->string('gal_lugar', 255)->nullable();
            $table->boolean('gal_destacado')->default(false);
            $table->boolean('gal_activo')->default(true);
            $table->integer('gal_orden')->default(0);
            $table->timestamps();

            // Índices para optimizar búsquedas y ordenamiento
            $table->index(['gal_categoria', 'gal_activo']);
            $table->index(['gal_destacado', 'gal_activo']);
            $table->index('gal_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_galerias');
    }
};
