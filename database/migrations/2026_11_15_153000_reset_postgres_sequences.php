<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No se requiere revertir el valor actual de secuencias numéricas
    }
};
