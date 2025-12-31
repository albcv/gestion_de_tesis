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
        Schema::create('cortes_de_tesis', function (Blueprint $table) {
            $table->bigIncrements('idCortes_de_tesis');
            $table->unsignedBigInteger('id_tesis');
            $table->unsignedInteger('Numero_corte');
            $table->timestamps();

            $table->unique(['id_tesis', 'Numero_corte'], 'id_tesis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cortes_de_tesis');
    }
};
