<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CatalogosBaseSeeder::class,
            AreaTematicaSeeder::class,
            TipoArticuloSeeder::class,
            EtiquetaArtSeeder::class,
            EdicionVarianzaSeeder::class,
            LineasInvestigacionSeeder::class,
            AutoresPersonasSeeder::class,
            ArticulosSeeder::class,
            MemoriasInvestigacionSeeder::class,
            CmsBancoDatosSeeder::class,
            PersonalInstitucionalSeeder::class,
            CmsServiciosSeeder::class,
            CmsConfiguracionSeeder::class,
            CmsTraduccionSeeder::class,
            AdminUserSeeder::class,
        ]);

        if (DB::getDriverName() === 'pgsql') {
            $this->resetPostgresSequences();
        }
    }

    /**
     * Sincroniza todas las secuencias auto-incrementales de PostgreSQL con el MAX(id) actual
     * para evitar colisiones UniqueConstraintViolationException al crear nuevos registros.
     */
    protected function resetPostgresSequences(): void
    {
        DB::statement("
            DO $$
            DECLARE
                r RECORD;
                seq_name TEXT;
                max_id BIGINT;
            BEGIN
                FOR r IN
                    SELECT table_name, column_name
                    FROM information_schema.columns
                    WHERE table_schema = 'public'
                      AND column_name = 'id'
                LOOP
                    seq_name := pg_get_serial_sequence('\"' || r.table_name || '\"', r.column_name);
                    IF seq_name IS NOT NULL THEN
                        EXECUTE format('SELECT COALESCE(MAX(%I), 0) FROM %I', r.column_name, r.table_name) INTO max_id;
                        IF max_id > 0 THEN
                            EXECUTE format('SELECT setval(%L, %s, true)', seq_name, max_id);
                        END IF;
                    END IF;
                END LOOP;
            END $$;
        ");
    }
}
