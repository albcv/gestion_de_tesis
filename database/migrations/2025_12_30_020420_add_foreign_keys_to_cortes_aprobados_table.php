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
        Schema::table('cortes_aprobados', function (Blueprint $table) {
            $table->foreign(['id_corte'], 'corte_tesis_fk')->references(['idCortes_de_tesis'])->on('cortes_de_tesis')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cortes_aprobados', function (Blueprint $table) {
            $table->dropForeign('corte_tesis_fk');
        });
    }
};
