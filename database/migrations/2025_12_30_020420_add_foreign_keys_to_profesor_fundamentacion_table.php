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
        Schema::table('profesor_fundamentacion', function (Blueprint $table) {
            $table->foreign(['id_fundamentacion'], 'profesor_fundamentacion_id_fundamentacion_foreign_key')->references(['id_fundamentacion'])->on('fundamentaciones')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['id_profesor'], 'profesor_fundamentacion_id_profesor_foreign_key')->references(['id'])->on('profesor')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profesor_fundamentacion', function (Blueprint $table) {
            $table->dropForeign('profesor_fundamentacion_id_fundamentacion_foreign_key');
            $table->dropForeign('profesor_fundamentacion_id_profesor_foreign_key');
        });
    }
};
