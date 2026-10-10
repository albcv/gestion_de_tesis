<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ver si existe la columna
        if (!Schema::hasColumn('users', 'id_rol')) {
            // La columna ya no existe. Solo limpiamos índices huérfanos.
            $this->dropOrphanIndexes();
            return;
        }

        // 2. Intentar eliminar la FK (si existe)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['id_rol']);
            });
        } catch (\Exception $e) {
            // No existía la FK, continuar
        }

        // 3. Intentar eliminar el índice (si existe)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['id_rol']);
            });
        } catch (\Exception $e) {
            // No existía el índice, continuar
        }

        // 4. Eliminar la columna
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('id_rol');
        });
    }

    /**
     * Elimina índices y FKs huérfanos que apunten a id_rol.
     */
    private function dropOrphanIndexes(): void
    {
        $database = DB::getDatabaseName();

        // Buscar FKs huérfanas
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'id_rol'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database]);

        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE users DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
            } catch (\Exception $e) {
                // Ignorar
            }
        }

        // Buscar índices huérfanos
        $indexes = DB::select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = 'users'
              AND COLUMN_NAME = 'id_rol'
              AND INDEX_NAME != 'PRIMARY'
        ", [$database]);

        foreach ($indexes as $idx) {
            try {
                DB::statement("ALTER TABLE users DROP INDEX `{$idx->INDEX_NAME}`");
            } catch (\Exception $e) {
                // Ignorar
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'id_rol')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('id_rol')->nullable()->after('id');
            });
        }
    }
};