<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Solo si aún existe la columna id_rol en users
        if (!Schema::hasColumn('users', 'id_rol')) {
            return;
        }

        $usuarios = DB::table('users')
            ->whereNotNull('id_rol')
            ->select('id', 'id_rol')
            ->get();

        foreach ($usuarios as $u) {
            // Evitar duplicados por si la pivote ya tenía datos
            $existe = DB::table('roles_usuarios')
                ->where('id_usuario', $u->id)
                ->where('id_rol', $u->id_rol)
                ->exists();

            if (!$existe) {
                DB::table('roles_usuarios')->insert([
                    'id_usuario' => $u->id,
                    'id_rol'     => $u->id_rol,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Al revertir, eliminar todos los registros migrados
        DB::table('roles_usuarios')->truncate();
    }
};