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
        Schema::table('opinion_tutor_fundamentacion', function (Blueprint $table) {
            $table->string('documento_revision', 500)
                  ->nullable()
                  ->after('opinion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opinion_tutor_fundamentacion', function (Blueprint $table) {
            $table->dropColumn('documento_revision');
        });
    }
};