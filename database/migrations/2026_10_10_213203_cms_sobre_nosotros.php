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
        Schema::create('cms_sobre_nosotros', function (Blueprint $table) {
            $table->id();
            $table->json('sn_contenido');
            $table->text('sn_historia');
            $table->text('sn_mision');
            $table->text('sn_vision');
            $table->json('sn_objetivos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_sobre_nosotros');
    }
};
