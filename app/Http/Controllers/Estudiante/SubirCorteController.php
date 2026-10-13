<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Cortes_de_tesis;
use App\Models\version_corte;
use App\Models\Tesis;
use App\Models\FechaEntregaCorte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SubirCorteController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró información del estudiante');
            }

            $tesis = Tesis::where('id_estudiante', $estudiante->id)->first();

            if (!$tesis) {
                return view('subirCorte', compact('tesis'))
                    ->with('info', 'No tienes una tesis registrada aún');
            }

            if (!$tesis->fundamentacion || !$tesis->fundamentacion->aprobada) {
                return view('estudiante.subirCorte', compact('tesis'))
                    ->with('error', 'Debes tener una fundamentación aprobada para subir cortes');
            }

            $cortes = Cortes_de_tesis::where('id_tesis', $tesis->id)
                ->with(['versiones', 'aprobado', 'desaprobado', 'noConformidades'])
                ->get();

            $fechasEntrega = FechaEntregaCorte::all()->keyBy('numero_corte');

            return view('estudiante.subirCorte', compact('tesis', 'cortes', 'fechasEntrega'));

        } catch (\Exception $e) {
            return redirect()->route('inicio')
                ->with('error', 'Error al cargar la página: ' . $e->getMessage());
        }
    }

    public function subirVersion(Request $request, $numeroCorte)
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

            if (!$tesis->fundamentacion || !$tesis->fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'Debes tener una fundamentación aprobada para subir cortes');
            }

            $corte = Cortes_de_tesis::where('id_tesis', $tesis->id)
                ->where('Numero_corte', $numeroCorte)
                ->first();

            if (!$corte) {
                $corte = new Cortes_de_tesis();
                $corte->id_tesis = $tesis->id;
                $corte->Numero_corte = $numeroCorte;
                $corte->save();
            }

            if ($corte->aprobado) {
                return redirect()->back()
                    ->with('error', "El corte {$numeroCorte} ya está aprobado. No puedes subir nuevas versiones.");
            }

            $fechaEntrega = FechaEntregaCorte::where('numero_corte', $numeroCorte)->first();
            if ($fechaEntrega && now()->greaterThan($fechaEntrega->fecha_entrega)) {
                return redirect()->back()
                    ->with('error', "La fecha de entrega del corte {$numeroCorte} ha pasado. No puedes subir nuevas versiones.");
            }

            // ---------- VALIDACIÓN ----------
            $validator = Validator::make($request->all(), [
                'documento' => [
                    'required',
                    'file',
                    'extensions:pdf,doc,docx',
                    'max:10240',
                ],
                'enlace'      => 'nullable|url|max:500',
                'descripcion' => 'nullable|string|max:500',
            ], [
                'documento.required'    => 'El documento es obligatorio',
                'documento.file'        => 'El documento debe ser un archivo válido',
                'documento.extensions'  => 'Solo se permiten archivos PDF, DOC y DOCX',
                'documento.max'         => 'El documento no puede exceder los 10MB',
                'enlace.url'            => 'El enlace de GitHub debe ser una URL válida',
                'enlace.max'            => 'El enlace no puede exceder los 500 caracteres',
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

            $ultimaVersion = version_corte::where('id_corte', $corte->idCortes_de_tesis)
                ->orderBy('version_numero', 'desc')
                ->first();

            $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";

            $path = $file->storeAs("cortes/{$corte->idCortes_de_tesis}", $nombreArchivo);

            $version = new version_corte();
            $version->id_corte       = $corte->idCortes_de_tesis;
            $version->version_numero = $nuevaVersionNumero;
            $version->nombre_archivo = $nombreArchivo;
            $version->ruta_documento = $path;
            $version->Enlace_Github  = $request->enlace ?? '';
            $version->tamanio        = $file->getSize();
            $version->tipo           = $extension;
            $version->descripcion    = $request->descripcion;
            $version->save();

            return redirect()->route('subirCorte')
                ->with('success', "Versión {$nuevaVersionNumero} del corte {$numeroCorte} subida correctamente");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al subir la versión: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Elimina una versión de un corte del estudiante autenticado.
     *
     * Reglas:
     *  - El estudiante debe ser dueño de la tesis.
     *  - El corte NO debe estar aprobado ni desaprobado.
     *  - Debe existir más de una versión (no se puede borrar la única).
     *  - Se elimina el archivo físico y el registro en la BD.
     *  - Se reordenan las versiones restantes (v1, v2, ...).
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

            // Buscar la versión
            $version = version_corte::findOrFail($idVersion);

            // Cargar el corte con la tesis y el estudiante
            $corte = Cortes_de_tesis::with(['tesis.estudiante'])
                ->findOrFail($version->id_corte);

            // Verificar que el estudiante autenticado sea dueño de la tesis
            if (!$corte->tesis
                || !$corte->tesis->estudiante
                || (int) $corte->tesis->estudiante->id !== (int) $estudiante->id) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para eliminar esta versión');
            }

            // No se puede eliminar si el corte está aprobado o desaprobado
            if ($corte->aprobado || $corte->desaprobado) {
                return redirect()->back()
                    ->with('error', 'No puedes eliminar versiones de un corte aprobado o desaprobado');
            }

            // Verificar que no sea la única versión
            $totalVersiones = version_corte::where('id_corte', $corte->idCortes_de_tesis)->count();

            if ($totalVersiones <= 1) {
                return redirect()->route('subirCorte')
                    ->with('error', 'No puedes eliminar la única versión del corte. Sube una nueva antes de eliminar esta.');
            }

            DB::beginTransaction();

            // Eliminar el archivo físico si existe
            if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }

            // Eliminar el registro
            $version->delete();

            // Reordenar versiones restantes
            $this->reordenarVersiones($corte->idCortes_de_tesis);

            DB::commit();

            return redirect()->route('subirCorte')
                ->with('success', 'Versión eliminada correctamente');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('subirCorte')
                ->with('error', 'La versión que intentas eliminar no existe');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar la versión: ' . $e->getMessage());
        }
    }

    /**
     * Reordena las versiones de un corte asignando números consecutivos
     * (1, 2, 3...) según su fecha de creación.
     */
    private function reordenarVersiones($idCorte)
    {
        $versiones = version_corte::where('id_corte', $idCorte)
            ->orderBy('created_at', 'asc')
            ->get();

        $numero = 1;
        foreach ($versiones as $v) {
            if ((int) $v->version_numero !== $numero) {
                $v->version_numero = $numero;
                $v->save();
            }
            $numero++;
        }
    }
}