<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
    
        $this->call([
            // Roles y permisos primero
            RolSeeder::class,
            PermisoSeeder::class,
            
            // Datos del sistema
            FacultadSeeder::class,
            DepartamentoSeeder::class,
            CarreraSeeder::class,
            ModalidadSeeder::class,
            CarreraModalidadSeeder::class,
            GrupoSeeder::class,
            
            // Asignación de permisos a roles
            RolesPermisoSeeder::class,
            
            // Usuarios
            AdminUserSeeder::class,
        ]);
    }
}