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
        Schema::table('cms_lineas_investigaciones', function (Blueprint $table) {
            $table->text('li_descripcion')->nullable()->change();
            $table->text('li_contenido')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cms_lineas_investigaciones', function (Blueprint $table) {
            $table->string('li_descripcion', 255)->change();
        });
    }
};
