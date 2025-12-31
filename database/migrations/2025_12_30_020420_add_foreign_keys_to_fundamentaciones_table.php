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
        Schema::table('fundamentaciones', function (Blueprint $table) {
            $table->foreign(['id_tesis'], 'id_tesis_fk')->references(['id'])->on('tesis')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fundamentaciones', function (Blueprint $table) {
            $table->dropForeign('id_tesis_fk');
        });
    }
};
