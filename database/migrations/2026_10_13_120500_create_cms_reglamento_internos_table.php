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
        Schema::create('cms_reglamento_internos', function (Blueprint $table) {
            $table->id();
            $table->string('reg_titulo', 255)->default('Reglamento Interno Institucional');
            $table->text('reg_descripcion')->nullable();
            $table->json('reg_contenido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_reglamento_internos');
    }
};
