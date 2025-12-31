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
        Schema::table('recomendaciones_fundamentacion', function (Blueprint $table) {
            $table->foreign(['id_fundamentacion'], 'id_fundamentacion_fk')->references(['id_fundamentacion'])->on('fundamentaciones')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recomendaciones_fundamentacion', function (Blueprint $table) {
            $table->dropForeign('id_fundamentacion_fk');
        });
    }
};
