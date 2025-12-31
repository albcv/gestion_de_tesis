<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Cortes_de_tesis;
use App\Models\version_corte;
use App\Models\Tesis;
use App\Models\FechaEntregaCorte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Verificar si la fundamentación está aprobada
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

            // Verificar si la fundamentación está aprobada
            if (!$tesis->fundamentacion || !$tesis->fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'Debes tener una fundamentación aprobada para subir cortes');
            }

            // Buscar el corte
            $corte = Cortes_de_tesis::where('id_tesis', $tesis->id)
                ->where('Numero_corte', $numeroCorte)
                ->first();
            
            if (!$corte) {
                // Si no existe el corte, crearlo
                $corte = new Cortes_de_tesis();
                $corte->id_tesis = $tesis->id;
                $corte->Numero_corte = $numeroCorte;
                $corte->save();
            }

            // Verificar si ya está aprobado
            if ($corte->aprobado) {
                return redirect()->back()
                    ->with('error', "El corte {$numeroCorte} ya está aprobado. No puedes subir nuevas versiones.");
            }

            // Verificar fecha de entrega
            $fechaEntrega = FechaEntregaCorte::where('numero_corte', $numeroCorte)->first();
            if ($fechaEntrega && now()->greaterThan($fechaEntrega->fecha_entrega)) {
                return redirect()->back()
                    ->with('error', "La fecha de entrega del corte {$numeroCorte} ha pasado. No puedes subir nuevas versiones.");
            }

            $validator = Validator::make($request->all(), [
                'documento' => 'required|file|mimes:pdf,doc,docx|max:10240',
                'enlace' => 'nullable|url|max:500',
                'descripcion' => 'nullable|string|max:500',
            ], [
                'documento.required' => 'El documento es obligatorio',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.mimes' => 'Solo se permiten archivos PDF, DOC y DOCX',
                'documento.max' => 'El documento no puede exceder los 10MB',
                'enlace.url' => 'El enlace de GitHub debe ser una URL válida',
                'enlace.max' => 'El enlace no puede exceder los 500 caracteres',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres'
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
                    ->with('error', 'Solo se permiten archivos PDF, DOC y DOCX')
                    ->withInput();
            }

            // Obtener la última versión
            $ultimaVersion = version_corte::where('id_corte', $corte->idCortes_de_tesis)
                ->orderBy('version_numero', 'desc')
                ->first();
            
            $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

            // Preparar nombre del archivo
            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";
            
            // Almacenar el archivo
            $path = $file->storeAs("cortes/{$corte->idCortes_de_tesis}", $nombreArchivo);

            // Crear registro de versión
            $version = new version_corte();
            $version->id_corte = $corte->idCortes_de_tesis;
            $version->version_numero = $nuevaVersionNumero;
            $version->nombre_archivo = $nombreArchivo;
            $version->ruta_documento = $path;
            $version->Enlace_Github = $request->enlace ?? '';
            $version->tamanio = $file->getSize();
            $version->tipo = $extension;
            $version->descripcion = $request->descripcion;
            $version->save();

            return redirect()->route('subirCorte')
                ->with('success', "Versión {$nuevaVersionNumero} del corte {$numeroCorte} subida correctamente");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al subir la versión: ' . $e->getMessage())
                ->withInput();
        }
    }
}