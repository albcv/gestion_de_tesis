<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $permisos = [
            ['permiso' => 'gestionarFacultad'],
            ['permiso' => 'gestionarCarrera'],
            ['permiso' => 'gestionarModalidad'],
            ['permiso' => 'gestionarGrupos'],
            ['permiso' => 'gestionarDepartamento'],
            ['permiso' => 'gestionarTesis'],
            ['permiso' => 'gestionarCortes'],
            ['permiso' => 'gestionarNoConformidades'],
            ['permiso' => 'subirCorte'],
            ['permiso' => 'revisarCorte'],
            ['permiso' => 'revisarFundamentación'],
            ['permiso' => 'gestionarUsuarios'],
            ['permiso' => 'gestionarRoles'],
            ['permiso' => 'gestionarPermisos'],
            ['permiso' => 'inicio'],
            ['permiso' => 'consultas'],
            ['permiso' => 'estudiantes'],
            ['permiso' => 'profesores'],
            ['permiso' => 'buscarEstudiante'],
            ['permiso' => 'estudiantes_sin_tutor'],
            ['permiso' => 'estudiantesAtrasadosFundamentación'],
            ['permiso' => 'estudiantesCursoDiurno'],
            ['permiso' => 'estudiantesCursoEncuentro'],
            ['permiso' => 'estudiantesFacultad'],
            ['permiso' => 'buscarProfesor'],
            ['permiso' => 'profesoresDepartamento'],
            ['permiso' => 'profesoresDoctores'],
            ['permiso' => 'profesoresMáster'],
            ['permiso' => 'profesoresNoTutores'],
            ['permiso' => 'mostrar_estudiante'],
            ['permiso' => 'mostrar_profesor'],
            ['permiso' => 'crearUsuario'],
            ['permiso' => 'perfil'],
            ['permiso' => 'verUsuario'],
            ['permiso' => 'editarUsuario'],
            ['permiso' => 'crearFundamentación'],
            ['permiso' => 'editarFundamentación'],
            ['permiso' => 'crearCorte'],
            ['permiso' => 'editarCorte'],
            ['permiso' => 'verCorte'],
            ['permiso' => 'verFundamentación'],
            ['permiso' => 'agregarRecomendacionFundamentacion'],
            ['permiso' => 'editarRecomendacionFundamentacion'],
            ['permiso' => 'agregarNoConformidadCorte'],
            ['permiso' => 'editarNoConformidadCorte'],
            ['permiso' => 'vincularProfesorCorte'],
            ['permiso' => 'vincularProfesorFundamentación'],
            ['permiso' => 'asignarTutor'],
            ['permiso' => 'agregarCarrera'],
            ['permiso' => 'verCarrera'],
            ['permiso' => 'editarCarrera'],
            ['permiso' => 'crearTesis'],
            ['permiso' => 'editarTesis'],
            ['permiso' => 'verTesis'],
            ['permiso' => 'gestionarFundamentaciones'],
            ['permiso' => 'subirFundamentación'],
            ['permiso' => 'fechaEntrega'],
            ['permiso' => 'revisarFundamentaciónEstudiante'],
            ['permiso' => 'revisarCorteEstudiante'],
            ['permiso' => 'estudiantesTutorados'],
            ['permiso' => 'revisarEstudianteTutorado'],
        ];

        $id = 1;
        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(
                ['permiso' => $permiso['permiso']],
                [
                    'id' => $id,
                    'permiso' => $permiso['permiso'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $id++;
        }

        $this->command->info(count($permisos) . ' permisos creados exitosamente.');
    }
}