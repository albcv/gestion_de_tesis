<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultadSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $facultades = [
            [
                'Nombre_facultad' => 'Facultad de Informática y Ciencias Exactas',
                'Siglas' => 'FICE',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias Técnicas',
                'Siglas' => 'FCT',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias Económicas y Empresariales',
                'Siglas' => 'FCEE',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias Agropecuarias',
                'Siglas' => 'FCA',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias Pedagógicas',
                'Siglas' => 'FCP',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias Sociales y Humanísticas',
                'Siglas' => 'FCSH',
            ],
            [
                'Nombre_facultad' => 'Facultad de Ciencias de la Cultura Física y el Deporte',
                'Siglas' => 'FCCFD',
            ],
        ];

        $id = 1;
        foreach ($facultades as $facultad) {
            DB::table('facultades')->updateOrInsert(
                ['Nombre_facultad' => $facultad['Nombre_facultad']],
                [
                    'idFacultad' => $id,
                    'Nombre_facultad' => $facultad['Nombre_facultad'],
                    'Siglas' => $facultad['Siglas'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($facultades) . ' facultades creadas exitosamente.');
    }
}