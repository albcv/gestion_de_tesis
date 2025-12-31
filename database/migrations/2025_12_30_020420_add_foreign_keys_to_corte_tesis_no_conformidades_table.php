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
        Schema::table('corte_tesis_no_conformidades', function (Blueprint $table) {
            $table->foreign(['corte_tesis_id'], 'corte_tesis_no_conformidades_foreign_key_cortes')->references(['idCortes_de_tesis'])->on('cortes_de_tesis')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['no_conformidad_id'])->references(['idNoConformidades'])->on('no_conformidades')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_tesis_no_conformidades', function (Blueprint $table) {
            $table->dropForeign('corte_tesis_no_conformidades_foreign_key_cortes');
            $table->dropForeign('corte_tesis_no_conformidades_no_conformidad_id_foreign');
        });
    }
};
