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
        Schema::table('corte_tesis_profesor', function (Blueprint $table) {
            $table->foreign(['corte_tesis_id'])->references(['idCortes_de_tesis'])->on('cortes_de_tesis')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['profesor_id'])->references(['id'])->on('profesor')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_tesis_profesor', function (Blueprint $table) {
            $table->dropForeign('corte_tesis_profesor_corte_tesis_id_foreign');
            $table->dropForeign('corte_tesis_profesor_profesor_id_foreign');
        });
    }
};
