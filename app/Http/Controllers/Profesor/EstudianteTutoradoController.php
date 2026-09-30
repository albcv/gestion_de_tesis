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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EstudianteTutoradoController extends Controller
{
    /**
     * Reglas de validación comunes para el archivo de revisión.
     */
    private function reglasDocumento(): array
    {
        return [
            'documento_revision' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,odt,txt',
                'max:10240', // 10 MB
            ],
        ];
    }

    private function mensajesDocumento(): array
    {
        return [
            'documento_revision.file'  => 'El documento debe ser un archivo válido',
            'documento_revision.mimes' => 'Solo se permiten archivos PDF, DOC, DOCX, ODT o TXT',
            'documento_revision.max'   => 'El documento no puede superar los 10 MB',
        ];
    }

    /* ============================================================
       LISTADO
       ============================================================ */
    public function index()
    {
        try {
            $profesor = Auth::user()->profesor;

            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            $estudiantesTutorados = $profesor->tutorados()
                ->with(['tesis' => function ($query) {
                    $query->with([
                        'fundamentacion' => function ($q) {
                            $q->with([
                                'aprobada',
                                'desaprobada',
                                'versiones' => fn($v) => $v->orderBy('version_numero', 'desc'),
                            ]);
                        },
                        'cortes' => function ($q) {
                            $q->with([
                                'aprobado',
                                'desaprobado',
                                'versiones' => fn($v) => $v->orderBy('version_numero', 'desc'),
                            ]);
                        },
                    ]);
                }])
                ->get();

            return view('profesor.listaEstudiantesTutorados', compact('estudiantesTutorados'));

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar los estudiantes tutorados: ' . $e->getMessage());
        }
    }

    /* ============================================================
       VISTA DETALLADA
       ============================================================ */
    public function show($id)
    {
        try {
            $profesor = Auth::user()->profesor;

            $estudiante = Estudiante::with([
                'tesis' => function ($query) {
                    $query->with([
                        'fundamentacion' => function ($q) {
                            $q->with([
                                'aprobada',
                                'desaprobada',
                                'versiones' => fn($v) => $v->orderBy('version_numero', 'desc'),
                                'recomendacion',
                            ]);
                        },
                        'cortes' => function ($q) {
                            $q->with([
                                'aprobado',
                                'desaprobado',
                                'versiones' => fn($v) => $v->orderBy('version_numero', 'desc'),
                                'noConformidades',
                            ]);
                        },
                    ]);
                },
            ])->findOrFail($id);

            $esTutor = $estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$esTutor) {
                return redirect()->route('profesor.dashboard')
                    ->with('error', 'No tienes permisos para revisar este estudiante');
            }

            $opinionFundamentacion = null;
            if ($estudiante->tesis && $estudiante->tesis->fundamentacion) {
                $opinionFundamentacion = OpinionTutorFundamentacion::where(
                        'id_fundamentacion',
                        $estudiante->tesis->fundamentacion->id_fundamentacion
                    )
                    ->where('id_profesor', $profesor->id)
                    ->first();
            }

            $opinionesCortes = [];
            if ($estudiante->tesis && $estudiante->tesis->cortes) {
                foreach ($estudiante->tesis->cortes as $corte) {
                    $opinionesCortes[$corte->idCortes_de_tesis] = OpinionTutorCorte::where(
                            'id_corte',
                            $corte->idCortes_de_tesis
                        )
                        ->where('id_profesor', $profesor->id)
                        ->first();
                }
            }

            return view('profesor.revisarEstudianteTutorado', compact(
                'estudiante',
                'opinionFundamentacion',
                'opinionesCortes'
            ));

        } catch (\Exception $e) {
            return redirect()->route('profesor.dashboard')
                ->with('error', 'Error al cargar el estudiante: ' . $e->getMessage());
        }
    }

    /* ============================================================
       GUARDAR OPINIÓN FUNDAMENTACIÓN (con documento)
       ============================================================ */
    public function guardarOpinionFundamentacion(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array_merge([
                    'id_fundamentacion' => 'required|exists:fundamentaciones,id_fundamentacion',
                    'opinion'           => 'required|string|max:2000',
                ], $this->reglasDocumento()),
                $this->mensajesDocumento()
            );

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            $esTutor = $fundamentacion->tesis->estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$esTutor) {
                return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción');
            }

            // ----- Buscar la opinión existente (si hay) -----
            $opinion = OpinionTutorFundamentacion::where('id_fundamentacion', $request->id_fundamentacion)
                ->where('id_profesor', $profesor->id)
                ->first();

            $rutaDocumento = $opinion->documento_revision ?? null;

            // ----- Reemplazar archivo si se sube uno nuevo -----
            if ($request->hasFile('documento_revision')) {
                // Borrar el anterior para no acumular basura
                if ($rutaDocumento && Storage::exists($rutaDocumento)) {
                    Storage::delete($rutaDocumento);
                }

                $rutaDocumento = $request->file('documento_revision')
                    ->store('opiniones/fundamentacion/' . $request->id_fundamentacion, 'local');
            }

            // ----- Crear o actualizar -----
            if (!$opinion) {
                $opinion = new OpinionTutorFundamentacion();
                $opinion->id_fundamentacion = $request->id_fundamentacion;
                $opinion->id_profesor       = $profesor->id;
            }

            $opinion->opinion            = $request->opinion;
            $opinion->documento_revision = $rutaDocumento;
            $opinion->save();

            return redirect()->back()
                ->with('success', 'Opinión sobre fundamentación guardada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la opinión: ' . $e->getMessage())
                ->withInput();
        }
    }

    /* ============================================================
       GUARDAR OPINIÓN CORTE (con documento)
       ============================================================ */
    public function guardarOpinionCorte(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                array_merge([
                    'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
                    'opinion'  => 'required|string|max:2000',
                ], $this->reglasDocumento()),
                $this->mensajesDocumento()
            );

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            $esTutor = $corte->tesis->estudiante->tutor()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$esTutor) {
                return redirect()->back()->with('error', 'No tienes permisos para realizar esta acción');
            }

            $opinion = OpinionTutorCorte::where('id_corte', $request->id_corte)
                ->where('id_profesor', $profesor->id)
                ->first();

            $rutaDocumento = $opinion->documento_revision ?? null;

            if ($request->hasFile('documento_revision')) {
                if ($rutaDocumento && Storage::exists($rutaDocumento)) {
                    Storage::delete($rutaDocumento);
                }

                $rutaDocumento = $request->file('documento_revision')
                    ->store('opiniones/corte/' . $request->id_corte, 'local');
            }

            if (!$opinion) {
                $opinion = new OpinionTutorCorte();
                $opinion->id_corte    = $request->id_corte;
                $opinion->id_profesor = $profesor->id;
            }

            $opinion->opinion            = $request->opinion;
            $opinion->documento_revision = $rutaDocumento;
            $opinion->save();

            return redirect()->back()
                ->with('success', 'Opinión sobre corte guardada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la opinión: ' . $e->getMessage())
                ->withInput();
        }
    }

    /* ============================================================
       DESCARGAR DOCUMENTO DE REVISIÓN
       ============================================================ */
    public function descargarDocumentoFundamentacion($id)
    {
        $profesor = Auth::user()->profesor;

        $opinion = OpinionTutorFundamentacion::findOrFail($id);

        // El profesor solo puede descargar SU propia opinión
        if ((int) $opinion->id_profesor !== (int) $profesor->id) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        if (!$opinion->documento_revision || !Storage::exists($opinion->documento_revision)) {
            abort(404, 'Documento no encontrado');
        }

        return Storage::download($opinion->documento_revision);
    }

    public function descargarDocumentoCorte($id)
    {
        $profesor = Auth::user()->profesor;

        $opinion = OpinionTutorCorte::findOrFail($id);

        if ((int) $opinion->id_profesor !== (int) $profesor->id) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        if (!$opinion->documento_revision || !Storage::exists($opinion->documento_revision)) {
            abort(404, 'Documento no encontrado');
        }

        return Storage::download($opinion->documento_revision);
    }
}