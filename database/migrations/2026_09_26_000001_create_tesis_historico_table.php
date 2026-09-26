<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tesis_historico', function (Blueprint $table) {
            $table->id();
            $table->year('año');                            
            $table->string('nombre_tesis', 500);
            $table->string('nombre_estudiante', 500);
            $table->string('documento_fundamentacion', 500);  
            $table->string('documento_corte', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tesis_historico');
    }
};