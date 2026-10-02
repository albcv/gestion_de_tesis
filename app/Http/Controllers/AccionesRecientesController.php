<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccionesRecientesController extends Controller
{
    /**
     * Muestra las acciones recientes: subidas/actualizaciones de tesis,
     * fundamentaciones y cortes de los últimos N días.
     */
    public function index(Request $request)
    {
        try {
            // Verificar que sea administrador
            $user = Auth::user();
            $rol = DB::table('roles')->where('id', $user->id_rol)->first();

            if (!$rol || strtolower($rol->rol) !== 'administrador') {
                return redirect()->route('inicio')
                    ->with('error', 'No tienes permisos para acceder a esta sección.');
            }

            // Días configurables (por defecto 3)
            $dias = (int) $request->input('dias', 3);
            if ($dias < 1) $dias = 1;
            if ($dias > 30) $dias = 30;

            $desde = Carbon::now()->subDays($dias);

            /* ============================================================
               TESIS (Trabajo de Diploma)
               ============================================================ */
            $tesis = DB::table('tesis as t')
                ->join('estudiantes as e', 'e.id', '=', 't.id_estudiante')
                ->where(function ($q) use ($desde) {
                    $q->where('t.created_at', '>=', $desde)
                      ->orWhere('t.updated_at', '>=', $desde);
                })
                ->select(
                    't.id',
                    DB::raw("NULL as version_numero"),
                    DB::raw("NULL as nombre_archivo"),
                    DB::raw("NULL as descripcion"),
                    't.created_at',
                    't.updated_at',
                    DB::raw("'tesis' as tipo"),
                    DB::raw("NULL as numero_corte"),
                    'e.Nombre_estudiante',
                    'e.Apellido1',
                    'e.Apellido2',
                    'e.CI_estudiante',
                    't.Nombre_trabajo',
                    't.id as id_referencia'
                )
                ->get();

            /* ============================================================
               VERSIONES DE FUNDAMENTACIÓN
               ============================================================ */
            $fundamentaciones = DB::table('version_fundamentacion as vf')
                ->join('fundamentaciones as f', 'f.id_fundamentacion', '=', 'vf.id_fundamentacion')
                ->join('tesis as t', 't.id', '=', 'f.id_tesis')
                ->join('estudiantes as e', 'e.id', '=', 't.id_estudiante')
                ->where(function ($q) use ($desde) {
                    $q->where('vf.created_at', '>=', $desde)
                      ->orWhere('vf.updated_at', '>=', $desde);
                })
                ->select(
                    'vf.id',
                    'vf.version_numero',
                    'vf.nombre_archivo',
                    'vf.descripcion',
                    'vf.created_at',
                    'vf.updated_at',
                    DB::raw("'fundamentacion' as tipo"),
                    DB::raw("NULL as numero_corte"),
                    'e.Nombre_estudiante',
                    'e.Apellido1',
                    'e.Apellido2',
                    'e.CI_estudiante',
                    't.Nombre_trabajo',
                    'f.id_fundamentacion as id_referencia'
                )
                ->get();

            /* ============================================================
               VERSIONES DE CORTE
               ============================================================ */
            $cortes = DB::table('version_corte as vc')
                ->join('cortes_de_tesis as c', 'c.idCortes_de_tesis', '=', 'vc.id_corte')
                ->join('tesis as t', 't.id', '=', 'c.id_tesis')
                ->join('estudiantes as e', 'e.id', '=', 't.id_estudiante')
                ->where(function ($q) use ($desde) {
                    $q->where('vc.created_at', '>=', $desde)
                      ->orWhere('vc.updated_at', '>=', $desde);
                })
                ->select(
                    'vc.id',
                    'vc.version_numero',
                    'vc.nombre_archivo',
                    'vc.descripcion',
                    'vc.created_at',
                    'vc.updated_at',
                    DB::raw("'corte' as tipo"),
                    'c.Numero_corte as numero_corte',
                    'e.Nombre_estudiante',
                    'e.Apellido1',
                    'e.Apellido2',
                    'e.CI_estudiante',
                    't.Nombre_trabajo',
                    'c.idCortes_de_tesis as id_referencia'
                )
                ->get();

            /* ============================================================
               UNIFICAR Y ORDENAR
               ============================================================ */
            $acciones = $tesis
                ->concat($fundamentaciones)
                ->concat($cortes)
                ->map(function ($item) {
                    // Fecha de la acción: la más reciente entre created_at y updated_at
                    $fechaCreated = $item->created_at ? strtotime($item->created_at) : 0;
                    $fechaUpdated = $item->updated_at ? strtotime($item->updated_at) : 0;

                    $item->fecha_accion = max($fechaCreated, $fechaUpdated);

                    // Nombre completo del estudiante
                    $item->nombre_completo = trim(
                        ($item->Nombre_estudiante ?? '') . ' ' .
                        ($item->Apellido1 ?? '') . ' ' .
                        ($item->Apellido2 ?? '')
                    );

                    // ------- Determinar si fue creación o actualización -------
                    if ($item->tipo === 'tesis') {
                        // Para tesis: si created_at == updated_at (o muy cercanos),
                        // es "creó"; si updated_at es posterior, es "actualizó"
                        $diff = abs($fechaUpdated - $fechaCreated);
                        $item->es_creacion = ($diff < 60); // menos de 60s de diferencia
                    } else {
                        // Para versiones: v1 = subió, v>1 = actualizó
                        $item->es_creacion = ((int) $item->version_numero === 1);
                    }

                    return $item;
                })
                ->sortByDesc('fecha_accion')
                ->values();

            return view('acciones_recientes', compact('acciones', 'dias'));

        } catch (\Exception $e) {
            return redirect()->route('inicio')
                ->with('error', 'Error al cargar las acciones recientes: ' . $e->getMessage());
        }
    }
}