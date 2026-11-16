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
        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->text('mdi_titulo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->string('mdi_titulo', 255)->change();
        });
    }
};
