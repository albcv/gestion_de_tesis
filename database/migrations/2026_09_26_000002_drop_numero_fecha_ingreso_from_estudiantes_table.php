<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // 1. Eliminar el índice único antiguo
            $table->dropUnique('year_academico');

            // 2. Eliminar las columnas
            $table->dropColumn(['número', 'Fecha_ingreso']);
        });

        // 3. Recrear el índice único sin "número"
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->unique(
                ['year_academico', 'id_grupo', 'id_modalidad', 'id_carrera'],
                'year_academico'
            );
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            // 1. Eliminar el índice único nuevo
            $table->dropUnique('year_academico');

            // 2. Recrear las columnas
            $table->unsignedInteger('número')->after('id_modalidad');
            $table->date('Fecha_ingreso')->after('Apellido2');
        });

        // 3. Recrear el índice único original
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->unique(
                ['year_academico', 'id_grupo', 'id_modalidad', 'número', 'id_carrera'],
                'year_academico'
            );
        });
    }
};