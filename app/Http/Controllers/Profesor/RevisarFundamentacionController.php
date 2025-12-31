<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\fundamentaciones;
use App\Models\fundamentaciones_aprobadas;
use App\Models\fundamentaciones_desaprobadas;
use App\Models\recomendaciones_fundamentacion;
use App\Models\profesorFundamentación;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RevisarFundamentacionController extends Controller
{
    // Mostrar lista de fundamentaciones asignadas al profesor
    public function index()
    {
        try {
            $profesor = Auth::user()->profesor;
            
            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            // Obtener todas las fundamentaciones asignadas a este profesor
            $fundamentacionesAsignadas = profesorFundamentación::where('id_profesor', $profesor->id)
                ->with(['fundamentacion' => function($query) {
                    $query->with([
                        'tesis.estudiante',
                        'aprobada',
                        'desaprobada',
                        'recomendacion',
                        'versiones' => function($q) {
                            $q->orderBy('version_numero', 'desc');
                        }
                    ]);
                }])
                ->get()
                ->pluck('fundamentacion')
                ->filter();

            return view('profesor.listaFundamentaciones', compact('fundamentacionesAsignadas'));
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar las fundamentaciones asignadas: ' . $e->getMessage());
        }
    }

    // Mostrar vista para revisar una fundamentación específica
    public function show($id)
    {
        try {
            $profesor = Auth::user()->profesor;
            
            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            $fundamentacion = fundamentaciones::with([
                'tesis.estudiante',
                'aprobada',
                'desaprobada',
                'recomendacion',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'desc');
                },
                'profesores'
            ])->findOrFail($id);

            // Verificar que el profesor esté vinculado a esta fundamentación
            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->route('revisarFundamentación')
                    ->with('error', 'No tienes asignada esta fundamentación para revisar');
            }

            return view('profesor.revisarFundamentación', compact('fundamentacion'));
            
        } catch (\Exception $e) {
            return redirect()->route('revisarFundamentación')
                ->with('error', 'Error al cargar la fundamentación: ' . $e->getMessage());
        }
    }

    public function aprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Fundamentación no válida');
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            // Verificar que el profesor esté vinculado a la fundamentación
            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de desaprobadas si existe
            fundamentaciones_desaprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();

            // Agregar a aprobadas
            $aprobada = new fundamentaciones_aprobadas();
            $aprobada->id_fundamentacion = $request->id_fundamentacion;
            $aprobada->save();

            return redirect()->back()
                ->with('success', 'Fundamentación aprobada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al aprobar la fundamentación: ' . $e->getMessage());
        }
    }

    public function desaprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Fundamentación no válida');
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            // Verificar que el profesor esté vinculado a la fundamentación
            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de aprobadas si existe
            fundamentaciones_aprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();

            // Agregar a desaprobadas
            $desaprobada = new fundamentaciones_desaprobadas();
            $desaprobada->id_fundamentacion = $request->id_fundamentacion;
            $desaprobada->save();

            return redirect()->back()
                ->with('success', 'Fundamentación desaprobada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desaprobar la fundamentación: ' . $e->getMessage());
        }
    }

    public function revertir(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Fundamentación no válida');
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            // Verificar que el profesor esté vinculado a la fundamentación
            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de aprobadas y desaprobadas
            fundamentaciones_aprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();
            fundamentaciones_desaprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();

            return redirect()->back()
                ->with('success', 'Fundamentación revertida a pendiente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al revertir la fundamentación: ' . $e->getMessage());
        }
    }

    public function guardarRecomendacion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
                'recomendacion' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            // Verificar que el profesor esté vinculado a la fundamentación
            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Guardar o actualizar recomendación
            $recomendacion = recomendaciones_fundamentacion::updateOrCreate(
                ['id_fundamentacion' => $request->id_fundamentacion],
                ['recomendacion' => $request->recomendacion]
            );

            return redirect()->back()
                ->with('success', 'Recomendación guardada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la recomendación: ' . $e->getMessage())
                ->withInput();
        }
    }
}