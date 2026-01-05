<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\fundamentaciones;
use App\Models\fundamentaciones_aprobadas;
use App\Models\fundamentaciones_desaprobadas;
use App\Models\Cortes_de_tesis;
use App\Models\cortes_aprobados;
use App\Models\cortes_desaprobados;
use App\Models\Estudiante;
use App\Models\tutor_estudiante;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class estadisticasController extends Controller
{
    public function obtenerEstadisticas()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            // Obtener el rol del usuario autenticado
            $usuario = Auth::user();
            $id_rol = $usuario->id_rol;

            // Verificar si el rol tiene permiso para ver estadísticas
            $tienePermiso = DB::table('roles_permisos as rp')
                ->join('permisos as p', 'rp.id_permiso', '=', 'p.id')
                ->where('rp.id_rol', $id_rol)
                ->where('p.permiso', 'estadisticas')
                ->exists();

            // Si no tiene permiso, redirigir al login
            if (!$tienePermiso) {
                return redirect()->route('login');
            }

            // Estadísticas de fundamentaciones
            $totalFundamentaciones = fundamentaciones::count();
            $fundAprobadas = fundamentaciones_aprobadas::count();
            $fundDesaprobadas = fundamentaciones_desaprobadas::count();
            $fundPendientes = $totalFundamentaciones - ($fundAprobadas + $fundDesaprobadas);

            // Estadísticas de cortes
            $totalCortes = Cortes_de_tesis::count();
            $cortesAprobados = cortes_aprobados::count();
            $cortesDesaprobados = cortes_desaprobados::count();
            $cortesPendientes = $totalCortes - ($cortesAprobados + $cortesDesaprobados);

            // Estadísticas de estudiantes
            $totalEstudiantes = Estudiante::count();
            $estudiantesConTutor = tutor_estudiante::distinct('id_estudiante')->count('id_estudiante');
            $estudiantesSinTutor = $totalEstudiantes - $estudiantesConTutor;

            // Estadísticas de estudiantes de año culminante
            $estudiantesAnioCulminante = Estudiante::select('estudiantes.*', 'carrera_modalidad.cantidad_years')
                ->join('carrera_modalidad', function($join) {
                    $join->on('estudiantes.id_carrera', '=', 'carrera_modalidad.Carrera_idCarrera')
                         ->on('estudiantes.id_modalidad', '=', 'carrera_modalidad.Modalidad_idModalidad');
                })
                ->whereColumn('estudiantes.year_academico', '=', 'carrera_modalidad.cantidad_years')
                ->get();

            $totalEstudiantesCulminante = $estudiantesAnioCulminante->count();
            
            // Estudiantes de año culminante con y sin tutor
            $estudiantesCulminanteIds = $estudiantesAnioCulminante->pluck('id')->toArray();
            
            $estudiantesCulminanteConTutor = 0;
            $estudiantesCulminanteSinTutor = $totalEstudiantesCulminante;
            
            if (count($estudiantesCulminanteIds) > 0) {
                $estudiantesCulminanteConTutor = tutor_estudiante::whereIn('id_estudiante', $estudiantesCulminanteIds)
                    ->distinct('id_estudiante')
                    ->count('id_estudiante');
                
                $estudiantesCulminanteSinTutor = $totalEstudiantesCulminante - $estudiantesCulminanteConTutor;
            }

            return response()->json([
                'fundamentaciones' => [
                    'total' => $totalFundamentaciones,
                    'aprobadas' => $fundAprobadas,
                    'desaprobadas' => $fundDesaprobadas,
                    'pendientes' => $fundPendientes
                ],
                'cortes' => [
                    'total' => $totalCortes,
                    'aprobados' => $cortesAprobados,
                    'desaprobados' => $cortesDesaprobados,
                    'pendientes' => $cortesPendientes
                ],
                'estudiantes' => [
                    'total' => $totalEstudiantes,
                    'sin_tutor' => $estudiantesSinTutor
                ],
                'estudiantes_culminante' => [
                    'total' => $totalEstudiantesCulminante,
                    'sin_tutor' => $estudiantesCulminanteSinTutor,
                    'con_tutor' => $estudiantesCulminanteConTutor
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener estadísticas',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}