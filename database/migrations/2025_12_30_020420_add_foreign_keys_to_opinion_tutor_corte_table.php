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
        Schema::table('opinion_tutor_corte', function (Blueprint $table) {
            $table->foreign(['id_corte'])->references(['idCortes_de_tesis'])->on('cortes_de_tesis')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_profesor'])->references(['id'])->on('profesor')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opinion_tutor_corte', function (Blueprint $table) {
            $table->dropForeign('opinion_tutor_corte_id_corte_foreign');
            $table->dropForeign('opinion_tutor_corte_id_profesor_foreign');
        });
    }
};
