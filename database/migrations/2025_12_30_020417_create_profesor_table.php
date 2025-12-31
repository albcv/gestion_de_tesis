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
        Schema::create('profesor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_departamento')->index('profesor_oponente_departamento_iddepartamento_foreign');
            $table->string('CI_profesor', 11)->unique('profesor_oponente_ci_profesor_unique');
            $table->string('Nombre_profesor', 40);
            $table->string('Apellido1', 40);
            $table->string('Apellido2', 40);
            $table->string('Categoria_docente', 30);
            $table->string('Categoria_cientifica', 30);
            $table->timestamps();
            $table->unsignedBigInteger('id_usuario')->index('id_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesor');
    }
};
