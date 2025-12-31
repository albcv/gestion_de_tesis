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

class EstadisticasController extends Controller
{
    public function obtenerEstadisticas()
    {
        try {
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