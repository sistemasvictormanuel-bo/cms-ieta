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
        Schema::create('articulos_ediciones_varianza', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edicion_varianza_id')->constrained('ediciones_varianzas')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos')->cascadeOnDelete();
            $table->integer('aev_orden')->default(1);
            $table->integer('aev_pag_inicio')->nullable();
            $table->integer('aev_pag_fin')->nullable();
            $table->timestamps();

            $table->unique(['edicion_varianza_id', 'articulo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos_ediciones_varianza');
    }
};
