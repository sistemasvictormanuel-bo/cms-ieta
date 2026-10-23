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
        Schema::table('articulos', function (Blueprint $table) {
            $table->string('estado_revision', 30)->default('aprobado')->after('art_estado');
            $table->text('revision_motivo')->nullable()->after('estado_revision');
            $table->foreignId('revisado_por_user_id')->nullable()->after('revision_motivo')->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable()->after('revisado_por_user_id');
            $table->foreignId('created_by_user_id')->nullable()->after('revisado_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropForeign(['revisado_por_user_id']);
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn([
                'estado_revision',
                'revision_motivo',
                'revisado_por_user_id',
                'revisado_at',
                'created_by_user_id',
            ]);
        });
    }
};
