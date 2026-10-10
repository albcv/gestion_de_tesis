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
        Schema::table('corte_tesis_no_conformidades', function (Blueprint $table) {
            $table->string('documento_revision', 500)
                  ->nullable()
                  ->after('no_conformidad_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_tesis_no_conformidades', function (Blueprint $table) {
            $table->dropColumn('documento_revision');
        });
    }
};