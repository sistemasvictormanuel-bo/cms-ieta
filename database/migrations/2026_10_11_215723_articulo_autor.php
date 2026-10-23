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
        Schema::create('articulo_autor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')
                ->constrained('articulos')
                ->cascadeOnDelete();
            $table->foreignId('autor_id')
                ->constrained('cms_autores')
                ->cascadeOnDelete();
            $table->foreignId('tipo_participaciones_id')
                ->constrained('tipo_participaciones')
                ->restrictOnDelete();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulo_autor');
    }
};
