<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_usuarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('id_rol');
            $table->unsignedBigInteger('id_usuario');
            $table->timestamps();

            $table->unique(['id_rol', 'id_usuario'], 'rol_usuario_unique');

            $table->foreign('id_rol')
                  ->references('id')->on('roles')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('id_usuario')
                  ->references('id')->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_usuarios');
    }
};