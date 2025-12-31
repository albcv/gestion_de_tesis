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
        Schema::create('carrera_modalidad', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('Carrera_idCarrera');
            $table->unsignedBigInteger('Modalidad_idModalidad')->index('carrera_modalidad_modalidad_idmodalidad_foreign');
            $table->integer('cantidad_years');
            $table->timestamps();

            $table->unique(['Carrera_idCarrera', 'Modalidad_idModalidad', 'cantidad_years'], 'carrera_idcarrera');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrera_modalidad');
    }
};
