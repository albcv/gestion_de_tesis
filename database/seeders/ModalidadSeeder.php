<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModalidadSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $modalidades = [
            ['Nombre_modalidad' => 'Curso regular diurno'],
            ['Nombre_modalidad' => 'Curso por encuentro'],
            ['Nombre_modalidad' => 'Curso a distancia'],
        ];

        $id = 1;
        foreach ($modalidades as $modalidad) {
            DB::table('modalidades')->updateOrInsert(
                ['Nombre_modalidad' => $modalidad['Nombre_modalidad']],
                [
                    'idModalidad' => $id,
                    'Nombre_modalidad' => $modalidad['Nombre_modalidad'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($modalidades) . ' modalidades creadas exitosamente.');
    }
}