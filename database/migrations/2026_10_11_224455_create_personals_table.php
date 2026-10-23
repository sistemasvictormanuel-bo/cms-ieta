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
        Schema::create('personal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->text('pers_descripcion')->nullable();
            $table->foreignId('cargo_id')->constrained('cargo_institucionales')->cascadeOnDelete();
            $table->date('pers_fecha_ingreso')->nullable();
            $table->date('pers_fecha_retiro')->nullable();
            $table->boolean('pers_estado')->default(1);
            $table->softDeletes();
            // campo foto UQ VIVE EN PERSONAS NO ES NECESARIO ACA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal');
    }
};
