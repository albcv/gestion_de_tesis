<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posibles_usuarios', function (Blueprint $table) {
            $table->id();

            // ----- Datos de la cuenta (users) -----
            $table->string('name', 100);                              // nombre de usuario
            $table->string('email', 255)->unique('posibles_usuarios_email_unique');
            $table->string('password');

            // ----- Rol solicitado -----
            $table->string('rol_solicitado', 20);                     // 'estudiante' | 'profesor'

            // ----- Datos personales comunes -----
            $table->string('ci', 11)->nullable();                     // CI del estudiante o profesor
            $table->string('nombre_persona', 40)->nullable();
            $table->string('apellido1', 40)->nullable();
            $table->string('apellido2', 40)->nullable();

            // ----- Datos exclusivos del ESTUDIANTE -----
            $table->string('sexo', 9)->nullable();                  
            $table->unsignedInteger('year_academico')->nullable();
            $table->unsignedBigInteger('id_grupo')->nullable();
            $table->unsignedBigInteger('id_modalidad')->nullable();
            $table->unsignedBigInteger('id_carrera')->nullable();

            // ----- Datos exclusivos del PROFESOR -----
            $table->string('categoria_docente', 30)->nullable();
            $table->string('categoria_cientifica', 30)->nullable();
            $table->unsignedBigInteger('id_departamento')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posibles_usuarios');
    }
};