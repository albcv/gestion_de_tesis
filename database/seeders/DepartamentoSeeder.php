<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $departamentos = [
            ['Nombre_departamento' => 'Departamento de Informática'],
            ['Nombre_departamento' => 'Departamento de Agronomía'],
            ['Nombre_departamento' => 'Departamento de Ciencias Económicas'],
        ];

        $id = 1;
        foreach ($departamentos as $departamento) {
            DB::table('departamentos')->updateOrInsert(
                ['Nombre_departamento' => $departamento['Nombre_departamento']],
                [
                    'idDepartamento' => $id,
                    'Nombre_departamento' => $departamento['Nombre_departamento'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($departamentos) . ' departamentos creados exitosamente.');
    }
}