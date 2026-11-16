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
        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->integer('mdi_anio')->nullable()->after('mdi_etiqueta');
        });

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('UPDATE memorias_investigaciones SET mdi_anio = EXTRACT(YEAR FROM mdi_fecha)::integer WHERE mdi_fecha IS NOT NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE memorias_investigaciones SET mdi_anio = CAST(strftime('%Y', mdi_fecha) AS INTEGER) WHERE mdi_fecha IS NOT NULL");
        } else {
            DB::statement('UPDATE memorias_investigaciones SET mdi_anio = YEAR(mdi_fecha) WHERE mdi_fecha IS NOT NULL');
        }

        DB::table('memorias_investigaciones')->whereNull('mdi_anio')->update(['mdi_anio' => (int) date('Y')]);

        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->dropColumn('mdi_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->date('mdi_fecha')->nullable()->after('mdi_etiqueta');
        });

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('UPDATE memorias_investigaciones SET mdi_fecha = make_date(mdi_anio, 1, 1) WHERE mdi_anio IS NOT NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement("UPDATE memorias_investigaciones SET mdi_fecha = mdi_anio || '-01-01' WHERE mdi_anio IS NOT NULL");
        } else {
            DB::statement("UPDATE memorias_investigaciones SET mdi_fecha = STR_TO_DATE(CONCAT(mdi_anio, '-01-01'), '%Y-%m-%d') WHERE mdi_anio IS NOT NULL");
        }

        Schema::table('memorias_investigaciones', function (Blueprint $table) {
            $table->dropColumn('mdi_anio');
        });
    }
};
