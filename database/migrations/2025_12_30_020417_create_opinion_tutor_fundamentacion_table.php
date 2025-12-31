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
        Schema::create('opinion_tutor_fundamentacion', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_fundamentacion')->index('opinion_tutor_fundamentacion_id_fundamentacion_foreign');
            $table->unsignedBigInteger('id_profesor')->index('opinion_tutor_fundamentacion_id_profesor_foreign');
            $table->text('opinion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opinion_tutor_fundamentacion');
    }
};
