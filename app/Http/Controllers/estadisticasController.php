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
                return response()->json([
                    'error'   => 'No autenticado',
                    'message' => 'Debes iniciar sesión para ver las estadísticas.',
                ], 401);
            }

            $usuario = Auth::user();

            // ============================================================
            // Verificar permiso usando la relación many-to-many
            // ============================================================
            // IDs de los roles del usuario autenticado
            $idsRoles = $usuario->roles()->pluck('roles.id')->toArray();

            if (empty($idsRoles)) {
                return response()->json([
                    'error'   => 'Sin rol asignado',
                    'message' => 'El usuario no tiene roles asignados.',
                ], 403);
            }

            $tienePermiso = DB::table('roles_permisos as rp')
                ->join('permisos as p', 'rp.id_permiso', '=', 'p.id')
                ->whereIn('rp.id_rol', $idsRoles)
                ->where('p.permiso', 'estadisticas')
                ->exists();

            if (!$tienePermiso) {
                return response()->json([
                    'error'   => 'Sin permiso',
                    'message' => 'No tienes permisos para ver las estadísticas.',
                ], 403);
            }

            // ============================================================
            // Estadísticas de fundamentaciones
            // ============================================================
            $totalFundamentaciones = fundamentaciones::count();
            $fundAprobadas         = fundamentaciones_aprobadas::count();
            $fundDesaprobadas      = fundamentaciones_desaprobadas::count();
            $fundPendientes        = max(0, $totalFundamentaciones - ($fundAprobadas + $fundDesaprobadas));

            // ============================================================
            // Estadísticas de cortes
            // ============================================================
            $totalCortes         = Cortes_de_tesis::count();
            $cortesAprobados     = cortes_aprobados::count();
            $cortesDesaprobados  = cortes_desaprobados::count();
            $cortesPendientes    = max(0, $totalCortes - ($cortesAprobados + $cortesDesaprobados));

            // ============================================================
            // Estadísticas de estudiantes
            // ============================================================
            $totalEstudiantes    = Estudiante::count();
            $estudiantesConTutor = tutor_estudiante::distinct('id_estudiante')->count('id_estudiante');
            $estudiantesSinTutor = max(0, $totalEstudiantes - $estudiantesConTutor);

            // ============================================================
            // Estadísticas de estudiantes de año culminante
            // ============================================================
            $estudiantesAnioCulminante = Estudiante::select('estudiantes.*', 'carrera_modalidad.cantidad_years')
                ->join('carrera_modalidad', function ($join) {
                    $join->on('estudiantes.id_carrera', '=', 'carrera_modalidad.Carrera_idCarrera')
                         ->on('estudiantes.id_modalidad', '=', 'carrera_modalidad.Modalidad_idModalidad');
                })
                ->whereColumn('estudiantes.year_academico', '=', 'carrera_modalidad.cantidad_years')
                ->get();

            $totalEstudiantesCulminante = $estudiantesAnioCulminante->count();
            $estudiantesCulminanteIds   = $estudiantesAnioCulminante->pluck('id')->toArray();

            $estudiantesCulminanteConTutor = 0;
            $estudiantesCulminanteSinTutor = $totalEstudiantesCulminante;

            if (count($estudiantesCulminanteIds) > 0) {
                $estudiantesCulminanteConTutor = tutor_estudiante::whereIn('id_estudiante', $estudiantesCulminanteIds)
                    ->distinct('id_estudiante')
                    ->count('id_estudiante');

                $estudiantesCulminanteSinTutor = max(0, $totalEstudiantesCulminante - $estudiantesCulminanteConTutor);
            }

            // ============================================================
            // Respuesta JSON
            // ============================================================
            return response()->json([
                'fundamentaciones' => [
                    'total'        => $totalFundamentaciones,
                    'aprobadas'    => $fundAprobadas,
                    'desaprobadas' => $fundDesaprobadas,
                    'pendientes'   => $fundPendientes,
                ],
                'cortes' => [
                    'total'        => $totalCortes,
                    'aprobados'    => $cortesAprobados,
                    'desaprobados' => $cortesDesaprobados,
                    'pendientes'   => $cortesPendientes,
                ],
                'estudiantes' => [
                    'total'     => $totalEstudiantes,
                    'sin_tutor' => $estudiantesSinTutor,
                ],
                'estudiantes_culminante' => [
                    'total'     => $totalEstudiantesCulminante,
                    'sin_tutor' => $estudiantesCulminanteSinTutor,
                    'con_tutor' => $estudiantesCulminanteConTutor,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en estadísticas: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error'   => 'Error al obtener estadísticas',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}