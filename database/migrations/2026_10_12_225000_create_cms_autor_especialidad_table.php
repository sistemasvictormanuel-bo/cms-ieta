<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla pivote cms_autor_especialidad (N:M)
        Schema::create('cms_autor_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('autor_id')->constrained('cms_autores')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('especialidad_id')->constrained('especialidades')->cascadeOnUpdate()->cascadeOnDelete();
            $table->unique(['autor_id', 'especialidad_id'], 'autor_especialidad_unique');
            $table->timestamps();
        });

        // 2. Migrar relaciones existentes si la columna especialidad_id aún existe en cms_autores
        if (Schema::hasColumn('cms_autores', 'especialidad_id')) {
            $registros = DB::table('cms_autores')
                ->whereNotNull('especialidad_id')
                ->select('id', 'especialidad_id')
                ->get();

            foreach ($registros as $row) {
                DB::table('cms_autor_especialidad')->insertOrIgnore([
                    'autor_id' => $row->id,
                    'especialidad_id' => $row->especialidad_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. Eliminar foreign key y columna especialidad_id de cms_autores
            Schema::table('cms_autores', function (Blueprint $table) {
                $table->dropForeign(['especialidad_id']);
                $table->dropColumn('especialidad_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('cms_autores', 'especialidad_id')) {
            Schema::table('cms_autores', function (Blueprint $table) {
                $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->cascadeOnUpdate()->restrictOnDelete();
            });

            // Restaurar primer especialidad de la tabla pivote si existe
            if (Schema::hasTable('cms_autor_especialidad')) {
                $pivotes = DB::table('cms_autor_especialidad')->select('autor_id', 'especialidad_id')->get();
                foreach ($pivotes as $pivote) {
                    DB::table('cms_autores')->where('id', $pivote->autor_id)->whereNull('especialidad_id')->update([
                        'especialidad_id' => $pivote->especialidad_id,
                    ]);
                }
            }
        }

        Schema::dropIfExists('cms_autor_especialidad');
    }
};
