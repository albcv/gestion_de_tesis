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
        Schema::table('carrera_modalidad', function (Blueprint $table) {
            $table->foreign(['Carrera_idCarrera'], 'carrera_modalidad_carrera_foreign_key')->references(['id'])->on('carreras')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['Modalidad_idModalidad'])->references(['idModalidad'])->on('modalidades')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrera_modalidad', function (Blueprint $table) {
            $table->dropForeign('carrera_modalidad_carrera_foreign_key');
            $table->dropForeign('carrera_modalidad_modalidad_idmodalidad_foreign');
        });
    }
};
