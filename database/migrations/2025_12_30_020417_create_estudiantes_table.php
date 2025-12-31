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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('CI_estudiante', 11)->unique();
            $table->string('sexo', 9);
            $table->string('Nombre_estudiante', 40);
            $table->string('Apellido1', 40);
            $table->string('Apellido2', 40);
            $table->date('Fecha_ingreso');
            $table->unsignedInteger('year_academico');
            $table->timestamps();
            $table->integer('id_grupo')->index('id_grupo_fk');
            $table->unsignedBigInteger('id_modalidad')->index('id_modalidad_fk');
            $table->unsignedInteger('número');
            $table->unsignedBigInteger('id_usuario')->index('id_usuario_fk');
            $table->unsignedBigInteger('id_carrera')->index('id_carrera_foreign_key');

            $table->unique(['year_academico', 'id_grupo', 'id_modalidad', 'número', 'id_carrera'], 'year_academico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
