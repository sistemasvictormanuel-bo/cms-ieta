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
        Schema::create('cms_noticias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete(); // tiene que ser un autor o un persona registrada puede ser el admin super admin etc pero registrada
            $table->string('not_titulo');
            $table->text('not_descripcion')->nullable();
            $table->date('not_fecha')->nullable();
            $table->boolean('not_destacado')->default(false);
            $table->boolean('not_publicado')->default(false);
            $table->json('not_contenido')->nullable();
            $table->text('not_url')->nullable();
            // campo not_portada spatie media library
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_noticias');
    }
};
