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
        $tableNames = config('permission.table_names');

        if (Schema::hasTable($tableNames['permissions'])) {
            Schema::table($tableNames['permissions'], function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'modulo')) {
                    $table->string('modulo')->default('General')->index()->after('guard_name');
                }
                if (! Schema::hasColumn($table->getTable(), 'description')) {
                    $table->string('description')->nullable()->after('modulo');
                }
            });
        }

        if (Schema::hasTable($tableNames['roles'])) {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                if (! Schema::hasColumn($table->getTable(), 'description')) {
                    $table->string('description')->nullable()->after('guard_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (Schema::hasTable($tableNames['permissions'])) {
            Schema::table($tableNames['permissions'], function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn($table->getTable(), 'modulo')) {
                    $table->dropColumn('modulo');
                }
            });
        }

        if (Schema::hasTable($tableNames['roles'])) {
            Schema::table($tableNames['roles'], function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    }
};
