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
        Schema::create('personas', function (Blueprint $table) {
            $table->id(); // lo puse directo en pg alterado por sentencia, por olvido
            $table->string('p_nombre');
            $table->string('p_apellido_paterno');
            $table->string('p_apellido_materno')->nullable();
            $table->string('p_celular')->nullable();
            $table->string('p_correo')->nullable();
            $table->string('p_sitio_web')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            // campo de foto gestionado por spatie media library
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
