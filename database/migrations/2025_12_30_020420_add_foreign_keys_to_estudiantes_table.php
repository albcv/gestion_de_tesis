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
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->foreign(['id_carrera'], 'id_carrera_foreign_key')->references(['id'])->on('carreras')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_grupo'], 'id_grupo_fk')->references(['id'])->on('grupos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_modalidad'], 'id_modalidad_fk')->references(['idModalidad'])->on('modalidades')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'id_usuario_fk')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropForeign('id_carrera_foreign_key');
            $table->dropForeign('id_grupo_fk');
            $table->dropForeign('id_modalidad_fk');
            $table->dropForeign('id_usuario_fk');
        });
    }
};
