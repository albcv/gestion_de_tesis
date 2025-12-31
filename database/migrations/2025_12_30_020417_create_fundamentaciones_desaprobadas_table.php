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
        Schema::create('fundamentaciones_desaprobadas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_fundamentacion')->unique('id_fundamentacion');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fundamentaciones_desaprobadas');
    }
};
