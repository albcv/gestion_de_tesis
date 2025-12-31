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
        Schema::create('recomendaciones_fundamentacion', function (Blueprint $table) {
            $table->integer('id_recomendaciones_fundamentacion', true);
            $table->integer('id_fundamentacion')->unique('id_fundamentacion');
            $table->text('recomendacion');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recomendaciones_fundamentacion');
    }
};
