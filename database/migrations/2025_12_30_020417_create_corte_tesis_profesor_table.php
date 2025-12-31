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
        Schema::create('corte_tesis_profesor', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('corte_tesis_id');
            $table->unsignedBigInteger('profesor_id')->index('corte_tesis_profesor_profesor_id_foreign_key');
            $table->timestamps();

            $table->unique(['corte_tesis_id', 'profesor_id'], 'corte_tesis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corte_tesis_profesor');
    }
};
