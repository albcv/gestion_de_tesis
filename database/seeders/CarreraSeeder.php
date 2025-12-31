<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarreraSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Mapeo de nombres de facultad a IDs (se obtendrán de la base de datos)
        $facultades = DB::table('facultades')->get()->keyBy('Nombre_facultad');
        
        $carreras = [
            [
                'facultad_nombre' => 'Facultad de Informática y Ciencias Exactas',
                'Nombre_carrera' => 'Ingeniería Informática',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Técnicas',
                'Nombre_carrera' => 'Ingeniería Civil',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Técnicas',
                'Nombre_carrera' => 'Ingeniería Mecánica',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Agropecuarias',
                'Nombre_carrera' => 'Ingeniería Agronómica',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Agropecuarias',
                'Nombre_carrera' => 'Ingeniería en Ciencias Forestales',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Económicas y Empresariales',
                'Nombre_carrera' => 'Licenciatura en Contabilidad y Finanzas',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Económicas y Empresariales',
                'Nombre_carrera' => 'Licenciatura en Economía',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Económicas y Empresariales',
                'Nombre_carrera' => 'Licenciatura en Turismo',
            ],
            [
                'facultad_nombre' => 'Facultad de Informática y Ciencias Exactas',
                'Nombre_carrera' => 'Licenciatura en Educación Informática',
            ],
            [
                'facultad_nombre' => 'Facultad de Informática y Ciencias Exactas',
                'Nombre_carrera' => 'Licenciatura en Matemática',
            ],
            [
                'facultad_nombre' => 'Facultad de Ciencias Técnicas',
                'Nombre_carrera' => 'Ingeniería Hidráulica',
            ],
            [
                'facultad_nombre' => 'Facultad de Informática y Ciencias Exactas',
                'Nombre_carrera' => 'Licenciatura en Física',
            ],
        ];

        $id = 1;
        foreach ($carreras as $carrera) {
            if (isset($facultades[$carrera['facultad_nombre']])) {
                DB::table('carreras')->updateOrInsert(
                    ['Nombre_carrera' => $carrera['Nombre_carrera']],
                    [
                        'id' => $id,
                        'id_facultad' => $facultades[$carrera['facultad_nombre']]->idFacultad,
                        'Nombre_carrera' => $carrera['Nombre_carrera'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $id++;
            } else {
                $this->command->warn("Facultad '{$carrera['facultad_nombre']}' no encontrada para la carrera '{$carrera['Nombre_carrera']}'");
            }
        }

        $this->command->info(count($carreras) . ' carreras creadas exitosamente.');
    }
}