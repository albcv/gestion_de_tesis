<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $roles = [
            ['rol' => 'Administrador'],
            ['rol' => 'Profesor'],
            ['rol' => 'Estudiante'],
        ];

        $id = 1;
        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['rol' => $rol['rol']],
                [
                    'id' => $id,
                    'rol' => $rol['rol'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($roles) . ' roles creados exitosamente.');
    }
}