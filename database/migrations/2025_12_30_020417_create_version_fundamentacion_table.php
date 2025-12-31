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
        Schema::create('version_fundamentacion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_fundamentacion');
            $table->string('ruta_documento', 500);
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->integer('version_numero')->default(1);
            $table->string('nombre_archivo');
            $table->integer('tamanio')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha_subida')->useCurrent();

            $table->unique(['id_fundamentacion', 'version_numero'], 'id_fundamentacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_fundamentacion');
    }
};
