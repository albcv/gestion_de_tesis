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
        Schema::create('profesor_fundamentacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_profesor');
            $table->integer('id_fundamentacion')->index('profesor_fundamentacion_id_fundamentacion_foreign_key');
            $table->timestamps();

            $table->unique(['id_profesor', 'id_fundamentacion'], 'id_profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesor_fundamentacion');
    }
};
