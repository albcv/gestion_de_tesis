<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\fundamentaciones;
use App\Models\Cortes_de_tesis;
use App\Models\OpinionTutorFundamentacion;
use App\Models\OpinionTutorCorte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EstudianteTutoradoController extends Controller
{
    // Mostrar lista de estudiantes tutorados
    public function index()
    {
        try {
            $profesor = Auth::user()->profesor;
            
            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            // Obtener estudiantes tutorados por este profesor
            $estudiantesTutorados = $profesor->tutorados()
                ->with(['tesis' => function($query) {
                    $query->with([
                        'fundamentacion' => function($q) {
                            $q->with([
                                'aprobada',
                                'desaprobada',
                                'versiones' => function($v) {
                                    $v->orderBy('version_numero', 'desc');
                                }
                            ]);
                        },
                        'cortes' => function($q) {
                            $q->with([
                                'aprobado',
                                'desaprobado',
                                'versiones' => function($v) {
                                    $v->orderBy('version_numero', 'desc');
                                }
                            ]);
                        }
                    ]);
                }])
                ->get();

            return view('profesor.listaEstudiantesTutorados', compact('estudiantesTutorados'));
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar los estudiantes tutorados: ' . $e->getMessage());
        }
    }

    // Mostrar vista detallada de un estudiante tutorado
    public function show($id)
    {
        try {
            $profesor = Auth::user()->profesor;
            $estudiante = Estudiante::with([
                'tesis' => function($query) {
                    $query->with([
                        'fundamentacion' => function($q) {
                            $q->with([
                                'aprobada',
                                'desaprobada',
                                'versiones' => function($v) {
                                    $v->orderBy('version_numero', 'desc');
                                },
                                'recomendacion'
                            ]);
                        },
                        'cortes' => function($q) {
                            $q->with([
                                'aprobado',
                                'desaprobado',
                                'versiones' => function($v) {
                                    $v->orderBy('version_numero', 'desc');
                                },
                                'noConformidades'
                            ]);
                        }
                    ]);
                }
            ])->findOrFail($id);

            // Verificar que el profesor sea tutor del estudiante
            $esTutor = $estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$esTutor) {
                return redirect()->route('profesor.dashboard')
                    ->with('error', 'No tienes permisos para revisar este estudiante');
            }

            // Obtener opiniones del tutor
            $opinionFundamentacion = null;
            if ($estudiante->tesis && $estudiante->tesis->fundamentacion) {
                $opinionFundamentacion = OpinionTutorFundamentacion::where('id_fundamentacion', $estudiante->tesis->fundamentacion->id_fundamentacion)
                    ->where('id_profesor', $profesor->id)
                    ->first();
            }

            $opinionesCortes = [];
            if ($estudiante->tesis && $estudiante->tesis->cortes) {
                foreach ($estudiante->tesis->cortes as $corte) {
                    $opinionesCortes[$corte->idCortes_de_tesis] = OpinionTutorCorte::where('id_corte', $corte->idCortes_de_tesis)
                        ->where('id_profesor', $profesor->id)
                        ->first();
                }
            }

            return view('profesor.revisarEstudianteTutorado', compact('estudiante', 'opinionFundamentacion', 'opinionesCortes'));
            
        } catch (\Exception $e) {
            return redirect()->route('profesor.dashboard')
                ->with('error', 'Error al cargar el estudiante: ' . $e->getMessage());
        }
    }

    // Guardar opinión sobre fundamentación
    public function guardarOpinionFundamentacion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
                'opinion' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            // Verificar que el profesor sea tutor del estudiante
            $esTutor = $fundamentacion->tesis->estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$esTutor) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Guardar o actualizar opinión
            $opinion = OpinionTutorFundamentacion::updateOrCreate(
                [
                    'id_fundamentacion' => $request->id_fundamentacion,
                    'id_profesor' => $profesor->id
                ],
                ['opinion' => $request->opinion]
            );

            return redirect()->back()
                ->with('success', 'Opinión sobre fundamentación guardada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la opinión: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Guardar opinión sobre corte
    public function guardarOpinionCorte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
                'opinion' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor sea tutor del estudiante
            $esTutor = $corte->tesis->estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();
            
            if (!$esTutor) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Guardar o actualizar opinión
            $opinion = OpinionTutorCorte::updateOrCreate(
                [
                    'id_corte' => $request->id_corte,
                    'id_profesor' => $profesor->id
                ],
                ['opinion' => $request->opinion]
            );

            return redirect()->back()
                ->with('success', 'Opinión sobre corte guardada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la opinión: ' . $e->getMessage())
                ->withInput();
        }
    }
}