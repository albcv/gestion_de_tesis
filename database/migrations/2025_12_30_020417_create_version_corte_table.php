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
        Schema::create('version_corte', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_corte');
            $table->string('ruta_documento');
            $table->string('Enlace_Github');
            $table->integer('version_numero')->default(1);
            $table->string('nombre_archivo');
            $table->integer('tamanio')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_subida')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['id_corte', 'version_numero'], 'id_corte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_corte');
    }
};
