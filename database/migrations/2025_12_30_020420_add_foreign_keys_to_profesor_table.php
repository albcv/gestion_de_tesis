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
        Schema::table('profesor', function (Blueprint $table) {
            $table->foreign(['id_departamento'], 'id_departamento_fk')->references(['idDepartamento'])->on('departamentos')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_usuario'], 'id_usuario')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesor', function (Blueprint $table) {
            $table->dropForeign('id_departamento_fk');
            $table->dropForeign('id_usuario');
        });
    }
};
