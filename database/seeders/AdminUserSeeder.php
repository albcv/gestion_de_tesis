<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Obtener el rol de Administrador
        $rolAdmin = DB::table('roles')->where('rol', 'Administrador')->first();
        
        if ($rolAdmin) {
            $adminUser = [
                'name' => 'Administrador',
                'email' => 'admin@tesis.com',
                'id_rol' => $rolAdmin->id,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Verificar si el usuario ya existe
            $exists = DB::table('users')
                ->where('email', 'admin@tesis.com')
                ->exists();
            
            if (!$exists) {
                DB::table('users')->insert($adminUser);
                $this->command->info('Usuario administrador creado:');
                $this->command->info('   Email: admin@tesis.com');
                $this->command->info('   Password: 12345678');
            } else {
                $this->command->info('El usuario administrador ya existe en la base de datos.');
            }
        } else {
            $this->command->error('Rol de Administrador no encontrado.');
        }
    }
}