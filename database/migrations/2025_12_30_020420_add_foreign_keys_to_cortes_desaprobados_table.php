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
        Schema::table('cortes_desaprobados', function (Blueprint $table) {
            $table->foreign(['id_corte'], 'cortes_desaprobados_id_corte_foreign_key')->references(['idCortes_de_tesis'])->on('cortes_de_tesis')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cortes_desaprobados', function (Blueprint $table) {
            $table->dropForeign('cortes_desaprobados_id_corte_foreign_key');
        });
    }
};
