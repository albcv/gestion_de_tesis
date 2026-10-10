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
        Schema::table('no_conformidades', function (Blueprint $table) {
            $table->string('documento_revision', 500)
                  ->nullable()
                  ->after('Deficiencias_detectadas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('no_conformidades', function (Blueprint $table) {
            $table->dropColumn('documento_revision');
        });
    }
};