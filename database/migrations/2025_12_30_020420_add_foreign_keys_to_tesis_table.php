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
        Schema::table('tesis', function (Blueprint $table) {
            $table->foreign(['id_estudiante'], 'trabajo_diploma_estudiante_idestudiante_foreign')->references(['id'])->on('estudiantes')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tesis', function (Blueprint $table) {
            $table->dropForeign('trabajo_diploma_estudiante_idestudiante_foreign');
        });
    }
};
