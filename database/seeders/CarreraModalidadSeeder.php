<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarreraModalidadSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Obtener carreras y modalidades
        $carreras = DB::table('carreras')->get()->keyBy('Nombre_carrera');
        $modalidades = DB::table('modalidades')->get()->keyBy('Nombre_modalidad');
        
        $relaciones = [
            [
                'carrera_nombre' => 'Ingeniería Civil',
                'modalidad_nombre' => 'Curso regular diurno',
                'cantidad_years' => 4,
            ],
            [
                'carrera_nombre' => 'Ingeniería Informática',
                'modalidad_nombre' => 'Curso regular diurno',
                'cantidad_years' => 4,
            ],
            [
                'carrera_nombre' => 'Ingeniería Informática',
                'modalidad_nombre' => 'Curso por encuentro',
                'cantidad_years' => 5,
            ],
            [
                'carrera_nombre' => 'Ingeniería Informática',
                'modalidad_nombre' => 'Curso por encuentro',
                'cantidad_years' => 6,
            ],
        ];

        $id = 1;
        foreach ($relaciones as $relacion) {
            if (isset($carreras[$relacion['carrera_nombre']]) && isset($modalidades[$relacion['modalidad_nombre']])) {
                // Verificar si la relación ya existe
                $existe = DB::table('carrera_modalidad')
                    ->where('Carrera_idCarrera', $carreras[$relacion['carrera_nombre']]->id)
                    ->where('Modalidad_idModalidad', $modalidades[$relacion['modalidad_nombre']]->idModalidad)
                    ->where('cantidad_years', $relacion['cantidad_years'])
                    ->exists();
                
                if (!$existe) {
                    DB::table('carrera_modalidad')->insert([
                        'id' => $id,
                        'Carrera_idCarrera' => $carreras[$relacion['carrera_nombre']]->id,
                        'Modalidad_idModalidad' => $modalidades[$relacion['modalidad_nombre']]->idModalidad,
                        'cantidad_years' => $relacion['cantidad_years'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $id++;
                }
            } else {
                $this->command->warn("Carrera o modalidad no encontrada: {$relacion['carrera_nombre']} - {$relacion['modalidad_nombre']}");
            }
        }

        $this->command->info(count($relaciones) . ' relaciones carrera-modalidad creadas exitosamente.');
    }
}