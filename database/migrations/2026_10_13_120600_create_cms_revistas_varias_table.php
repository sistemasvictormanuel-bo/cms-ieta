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
        Schema::create('cms_revistas_varias', function (Blueprint $table) {
            $table->id();
            $table->string('rev_titulo', 255);
            $table->text('rev_descripcion')->nullable();
            $table->string('rev_url_enlace', 500);
            $table->string('rev_entidad', 255)->nullable();
            $table->string('rev_issn', 100)->nullable();
            $table->boolean('rev_activo')->default(true);
            $table->integer('rev_orden')->default(0);
            $table->timestamps();

            $table->index('rev_activo');
            $table->index('rev_orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_revistas_varias');
    }
};
