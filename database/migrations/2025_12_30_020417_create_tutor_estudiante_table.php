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
        Schema::create('tutor_estudiante', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_profesor');
            $table->unsignedBigInteger('id_estudiante')->index('id_estudiante_fk');
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['id_profesor', 'id_estudiante'], 'id_profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_estudiante');
    }
};
