<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\fundamentaciones;
use App\Models\version_fundamentacion;
use App\Models\Tesis;
use App\Models\FechaEntregaFundamentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Obtener o crear la fundamentación
            $fundamentacion = $tesis->fundamentacion;
            if (!$fundamentacion) {
                // Crear una nueva fundamentación automáticamente
                $fundamentacion = new fundamentaciones();
                $fundamentacion->id_tesis = $tesis->id;
                $fundamentacion->save();
                
                // Reload the fundamentacion with relationships
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

            // Obtener o crear la fundamentación
            $fundamentacion = $tesis->fundamentacion;
            if (!$fundamentacion) {
                // Crear la fundamentación si no existe
                $fundamentacion = new fundamentaciones();
                $fundamentacion->id_tesis = $tesis->id;
                $fundamentacion->save();
            }

            // Verificar si ya está aprobada
            if ($fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'Tu fundamentación ya está aprobada. No puedes subir nuevas versiones.');
            }

            // Verificar fecha de entrega
            $fechaEntrega = FechaEntregaFundamentacion::first();
            if ($fechaEntrega && now()->greaterThan($fechaEntrega->fecha_entrega)) {
                return redirect()->back()
                    ->with('error', 'La fecha de entrega de la fundamentación ha pasado. No puedes subir nuevas versiones.');
            }

            $validator = Validator::make($request->all(), [
                'documento' => 'required|file|mimes:pdf,doc,docx|max:10240',
                'descripcion' => 'nullable|string|max:500',
            ], [
                'documento.required' => 'El documento es obligatorio',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.mimes' => 'Solo se permiten archivos PDF, DOC y DOCX',
                'documento.max' => 'El documento no puede exceder los 10MB',
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
            $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)
                ->orderBy('version_numero', 'desc')
                ->first();
            
            $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

            // Preparar nombre del archivo
            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";
            
            // Almacenar el archivo
            $path = $file->storeAs("fundamentaciones/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

            // Crear registro de versión
            $version = new version_fundamentacion();
            $version->id_fundamentacion = $fundamentacion->id_fundamentacion;
            $version->version_numero = $nuevaVersionNumero;
            $version->nombre_archivo = $nombreArchivo;
            $version->ruta_documento = $path;
            $version->tamanio = $file->getSize();
            $version->tipo = $extension;
            $version->descripcion = $request->descripcion;
            $version->save();

            return redirect()->route('subirFundamentación')
                ->with('success', 'Versión ' . $nuevaVersionNumero . ' subida correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al subir la versión: ' . $e->getMessage())
                ->withInput();
        }
    }
}