<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesPermisoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Obtener roles y permisos
        $roles = DB::table('roles')->get()->keyBy('rol');
        $permisos = DB::table('permisos')->get()->keyBy('permiso');
        
        // Definir qué permisos tiene cada rol
        $permisosPorRol = [
            'Administrador' => [
                'gestionarFacultad', 'gestionarCarrera', 'gestionarModalidad', 'gestionarGrupos',
                'gestionarDepartamento', 'gestionarTesis', 'gestionarCortes', 'gestionarNoConformidades',
                'gestionarUsuarios', 'gestionarRoles', 'gestionarPermisos', 'inicio', 'consultas',
                'estudiantes', 'profesores', 'buscarEstudiante', 'estudiantes_sin_tutor',
                'estudiantesAtrasadosFundamentación', 'estudiantesCursoDiurno', 'estudiantesCursoEncuentro',
                'estudiantesFacultad', 'buscarProfesor', 'profesoresDepartamento', 'profesoresDoctores',
                'profesoresMáster', 'profesoresNoTutores', 'mostrar_estudiante', 'mostrar_profesor',
                'crearUsuario', 'perfil', 'verUsuario', 'editarUsuario', 'crearFundamentación',
                'editarFundamentación', 'crearCorte', 'editarCorte', 'verCorte', 'verFundamentación',
                'agregarRecomendacionFundamentacion', 'editarRecomendacionFundamentacion',
                'agregarNoConformidadCorte', 'editarNoConformidadCorte', 'vincularProfesorCorte',
                'vincularProfesorFundamentación', 'asignarTutor', 'agregarCarrera', 'verCarrera',
                'editarCarrera', 'crearTesis', 'editarTesis', 'verTesis', 'gestionarFundamentaciones',
                'fechaEntrega',
            ],
            'Profesor' => [
                'revisarCorte', 'revisarFundamentación', 'revisarFundamentaciónEstudiante',
                'revisarCorteEstudiante', 'estudiantesTutorados', 'revisarEstudianteTutorado',
                'inicio', 'perfil',
            ],
            'Estudiante' => [
                'subirFundamentación', 'subirCorte', 'inicio', 'perfil',
            ],
        ];
        
        $id = 1;
        foreach ($permisosPorRol as $rolNombre => $permisosRol) {
            if (isset($roles[$rolNombre])) {
                foreach ($permisosRol as $permisoNombre) {
                    if (isset($permisos[$permisoNombre])) {
                        // Verificar si la relación ya existe
                        $existe = DB::table('roles_permisos')
                            ->where('id_rol', $roles[$rolNombre]->id)
                            ->where('id_permiso', $permisos[$permisoNombre]->id)
                            ->exists();
                        
                        if (!$existe) {
                            DB::table('roles_permisos')->insert([
                                'id' => $id,
                                'id_rol' => $roles[$rolNombre]->id,
                                'id_permiso' => $permisos[$permisoNombre]->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $id++;
                        }
                    }
                }
            }
        }

        $this->command->info('Permisos asignados a roles exitosamente.');
    }
}