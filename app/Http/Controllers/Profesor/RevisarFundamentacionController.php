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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RevisarFundamentacionController extends Controller
{
    /**
     * Disco donde se almacenan los documentos de revisión.
     * 'local' → storage/app/private/
     */
    private const DISCO = 'local';

    /**
     * Ruta base dentro del disco.
     */
    private const RUTA_BASE = 'documentos_revision/fundamentaciones';

    // Mostrar lista de fundamentaciones asignadas al profesor
    public function index()
    {
        try {
            $profesor = Auth::user()->profesor;

            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            $fundamentacionesAsignadas = profesorFundamentación::where('id_profesor', $profesor->id)
                ->with(['fundamentacion' => function ($query) {
                    $query->with([
                        'tesis.estudiante',
                        'aprobada',
                        'desaprobada',
                        'recomendacion',
                        'versiones' => function ($q) {
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
                'versiones' => function ($query) {
                    $query->orderBy('version_numero', 'desc');
                },
                'profesores'
            ])->findOrFail($id);

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

            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            fundamentaciones_desaprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();

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

            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            fundamentaciones_aprobadas::where('id_fundamentacion', $request->id_fundamentacion)->delete();

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

            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

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
            // ---------- VALIDACIÓN ----------
            // Se usa 'extensions' en lugar de 'mimes' porque los .docx/.xlsx/.pptx
            // son ZIP internamente y PHP puede detectar su MIME como application/zip.
            $validator = Validator::make($request->all(), [
                'id_fundamentacion'  => 'required|exists:fundamentaciones,id_fundamentacion',
                'recomendacion'      => 'required|string|max:2000',
                'documento_revision' => [
                    'nullable',
                    'file',
                    'extensions:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar',
                    'max:10240',
                ],
                'eliminar_documento' => 'nullable|boolean',
            ], [
                'documento_revision.extensions' => 'Solo se permiten archivos PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, ZIP o RAR.',
                'documento_revision.max'        => 'El documento no puede exceder los 10 MB.',
                'recomendacion.required'        => 'Debes escribir una recomendación para el estudiante.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Errores de validación: ' . $validator->errors()->first());
            }

            $profesor = Auth::user()->profesor;
            $fundamentacion = fundamentaciones::find($request->id_fundamentacion);

            $estaVinculado = $fundamentacion->profesores()
                ->where('id_profesor', $profesor->id)
                ->exists();

            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // ---------- DATOS A GUARDAR ----------
            $data = ['recomendacion' => $request->recomendacion];

            // Buscar si ya existe una recomendación previa
            $recomendacionExistente = recomendaciones_fundamentacion::where(
                'id_fundamentacion',
                $request->id_fundamentacion
            )->first();

            // ---------- CASO 1: Eliminar el documento actual ----------
            // (checkbox activado y NO se subió un archivo nuevo)
            if ($request->boolean('eliminar_documento') && !$request->hasFile('documento_revision')) {
                if ($recomendacionExistente && $recomendacionExistente->documento_revision) {
                    if (Storage::disk(self::DISCO)->exists($recomendacionExistente->documento_revision)) {
                        Storage::disk(self::DISCO)->delete($recomendacionExistente->documento_revision);
                    }
                    $data['documento_revision'] = null;
                }
            }

            // ---------- CASO 2: Reemplazar o subir nuevo documento ----------
            if ($request->hasFile('documento_revision')) {
                // Eliminar el archivo anterior si existía
                if ($recomendacionExistente && $recomendacionExistente->documento_revision) {
                    if (Storage::disk(self::DISCO)->exists($recomendacionExistente->documento_revision)) {
                        Storage::disk(self::DISCO)->delete($recomendacionExistente->documento_revision);
                    }
                }

                // ============================================================
                // IMPORTANTE: usar storeAs() en lugar de store()
                // ============================================================
                // store() genera la extensión con guessExtension() basándose
                // en el MIME real. Si PHP no puede detectarlo (pasa con .docx,
                // .xlsx, .pptx, .zip, .rar) devuelve 'bin'.
                //
                // Solución: tomamos la extensión del NOMBRE ORIGINAL del
                // archivo subido por el usuario y la usamos explícitamente.
                // ============================================================
                $file            = $request->file('documento_revision');
                $extension       = strtolower($file->getClientOriginalExtension());
                $nombreOriginal  = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreSanitizado = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreOriginal);
                $nombreArchivo   = "recomendacion_{$request->id_fundamentacion}_{$nombreSanitizado}_" . time() . ".{$extension}";

                // Guardar con el nombre explícito (preserva la extensión real)
                $path = $file->storeAs(self::RUTA_BASE, $nombreArchivo, self::DISCO);
                $data['documento_revision'] = $path;
            }

            // ---------- GUARDAR ----------
            // Si no se subió archivo nuevo y no se marcó "eliminar", updateOrCreate
            // conserva el documento anterior (no está en $data).
            recomendaciones_fundamentacion::updateOrCreate(
                ['id_fundamentacion' => $request->id_fundamentacion],
                $data
            );

            return redirect()->back()
                ->with('success', 'Recomendación guardada correctamente');

        } catch (\Exception $e) {
            \Log::error('Error al guardar recomendación: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Error al guardar la recomendación: ' . $e->getMessage())
                ->withInput();
        }
    }
}