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
        Schema::table('tutor_estudiante', function (Blueprint $table) {
            $table->foreign(['id_estudiante'], 'id_estudiante_fk')->references(['id'])->on('estudiantes')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_profesor'], 'id_profesor_foreign_key')->references(['id'])->on('profesor')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutor_estudiante', function (Blueprint $table) {
            $table->dropForeign('id_estudiante_fk');
            $table->dropForeign('id_profesor_foreign_key');
        });
    }
};
