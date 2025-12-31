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
        Schema::table('cortes_de_tesis', function (Blueprint $table) {
            $table->foreign(['id_tesis'], 'cortes_de_tesis_trabajo_diploma_idtrabajo_diploma_foreign')->references(['id'])->on('tesis')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cortes_de_tesis', function (Blueprint $table) {
            $table->dropForeign('cortes_de_tesis_trabajo_diploma_idtrabajo_diploma_foreign');
        });
    }
};
