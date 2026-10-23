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
        Schema::create('banco_datos_tematicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banco_id')->constrained('cms_banco_datos')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('area_tematicas')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banco_datos_tematicas');
    }
};
