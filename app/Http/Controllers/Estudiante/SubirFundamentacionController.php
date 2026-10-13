<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\fundamentaciones;
use App\Models\version_fundamentacion;
use App\Models\Tesis;
use App\Models\FechaEntregaFundamentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubirFundamentacionController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->route('inicio')
                    ->with('error', 'No se encontró información del estudiante');
            }

            $tesis = Tesis::where('id_estudiante', $estudiante->id)->first();

            if (!$tesis) {
                return view('estudiante.subirFundamentacion', compact('tesis'))
                    ->with('info', 'No tienes una tesis registrada aún. Contacta con el administrador.');
            }

            $fundamentacion = $tesis->fundamentacion;
            if (!$fundamentacion) {
                $fundamentacion = new fundamentaciones();
                $fundamentacion->id_tesis = $tesis->id;
                $fundamentacion->save();

                $fundamentacion = $tesis->fundamentacion()->first();
            }

            $fechaEntrega = FechaEntregaFundamentacion::first();

            return view('estudiante.subirFundamentacion', compact('tesis', 'fundamentacion', 'fechaEntrega'));

        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar la página: ' . $e->getMessage());
        }
    }

    public function subirVersion(Request $request)
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->back()
                    ->with('error', 'No se encontró información del estudiante');
            }

            $tesis = Tesis::where('id_estudiante', $estudiante->id)->first();

            if (!$tesis) {
                return redirect()->back()
                    ->with('error', 'No tienes una tesis registrada');
            }

            $fundamentacion = $tesis->fundamentacion;
            if (!$fundamentacion) {
                $fundamentacion = new fundamentaciones();
                $fundamentacion->id_tesis = $tesis->id;
                $fundamentacion->save();
            }

            if ($fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'Tu fundamentación ya está aprobada. No puedes subir nuevas versiones.');
            }

            $fechaEntrega = FechaEntregaFundamentacion::first();
            if ($fechaEntrega && now()->greaterThan($fechaEntrega->fecha_entrega)) {
                return redirect()->back()
                    ->with('error', 'La fecha de entrega de la fundamentación ha pasado. No puedes subir nuevas versiones.');
            }

            // ---------- VALIDACIÓN ----------
            $validator = Validator::make($request->all(), [
                'documento' => [
                    'required',
                    'file',
                    'extensions:pdf,doc,docx',
                    'max:10240',
                ],
                'descripcion' => 'nullable|string|max:500',
            ], [
                'documento.required'    => 'El documento es obligatorio',
                'documento.file'        => 'El documento debe ser un archivo válido',
                'documento.extensions'  => 'Solo se permiten archivos PDF, DOC y DOCX',
                'documento.max'         => 'El documento no puede exceder los 10MB',
                'descripcion.max'       => 'La descripción no puede exceder los 500 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['pdf', 'doc', 'docx'];

            if (!in_array($extension, $allowedExtensions)) {
                return redirect()->back()
                    ->with('error', 'La extensión del archivo no es válida. Solo se aceptan: PDF, DOC, DOCX.')
                    ->withInput();
            }

            $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)
                ->orderBy('version_numero', 'desc')
                ->first();

            $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";

            $path = $file->storeAs("fundamentaciones/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

            $version = new version_fundamentacion();
            $version->id_fundamentacion = $fundamentacion->id_fundamentacion;
            $version->version_numero    = $nuevaVersionNumero;
            $version->nombre_archivo    = $nombreArchivo;
            $version->ruta_documento    = $path;
            $version->tamanio           = $file->getSize();
            $version->tipo              = $extension;
            $version->descripcion       = $request->descripcion;
            $version->save();

            return redirect()->route('subirFundamentación')
                ->with('success', 'Versión ' . $nuevaVersionNumero . ' subida correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al subir la versión: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Elimina una versión de la fundamentación del estudiante autenticado.
     *
     * Reglas:
     *  - El estudiante debe ser dueño de la tesis.
     *  - La fundamentación NO debe estar aprobada.
     *  - Debe existir más de una versión (no se puede borrar la única).
     *  - Se elimina el archivo físico y el registro en la BD.
     *  - NO se reordenan las versiones restantes: se conservan sus números.
     */
    public function eliminarVersion(Request $request, $idVersion)
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->back()
                    ->with('error', 'No se encontró información del estudiante');
            }

            $version = version_fundamentacion::findOrFail($idVersion);

            $fundamentacion = fundamentaciones::with(['tesis.estudiante'])
                ->findOrFail($version->id_fundamentacion);

            if (!$fundamentacion->tesis
                || !$fundamentacion->tesis->estudiante
                || (int) $fundamentacion->tesis->estudiante->id !== (int) $estudiante->id) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para eliminar esta versión');
            }

            if ($fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'No puedes eliminar versiones de una fundamentación aprobada');
            }

            $totalVersiones = version_fundamentacion::where('id_fundamentacion', $version->id_fundamentacion)->count();

            if ($totalVersiones <= 1) {
                return redirect()->route('subirFundamentación')
                    ->with('error', 'No puedes eliminar la única versión de la fundamentación. Sube una nueva antes de eliminar esta.');
            }

            DB::beginTransaction();

            // Eliminar el archivo físico si existe
            if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }

            // Eliminar el registro
            $version->delete();

            // ⚠️ NO reordenar: se conservan los números originales de las versiones restantes

            DB::commit();

            return redirect()->route('subirFundamentación')
                ->with('success', 'Versión eliminada correctamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('subirFundamentación')
                ->with('error', 'La versión que intentas eliminar no existe');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar la versión: ' . $e->getMessage());
        }
    }
}