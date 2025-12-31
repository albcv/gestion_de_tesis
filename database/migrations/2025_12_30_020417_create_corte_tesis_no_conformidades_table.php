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
        Schema::create('corte_tesis_no_conformidades', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('corte_tesis_id');
            $table->unsignedBigInteger('no_conformidad_id')->index('corte_tesis_no_conformidades_no_conformidad_id_foreign');
            $table->timestamps();

            $table->unique(['corte_tesis_id', 'no_conformidad_id'], 'corte_tesis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corte_tesis_no_conformidades');
    }
};
