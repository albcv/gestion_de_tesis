<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\fundamentaciones;
use App\Models\fundamentaciones_aprobadas;
use App\Models\fundamentaciones_desaprobadas;
use App\Models\Tesis;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\version_fundamentacion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class fundamentacionesController extends Controller
{
    protected $modelo = fundamentaciones::class;
    protected $modeloTesis = Tesis::class;
    protected $modeloAprobada = fundamentaciones_aprobadas::class;
    protected $modeloDesaprobada = fundamentaciones_desaprobadas::class;
    protected $modeloVersion = version_fundamentacion::class;
    protected $rutaVista = 'gestionarFundamentaciones';
    protected $storageFolder = 'fundamentaciones';
    protected $allowedExtensions = ['pdf', 'doc', 'docx'];
    protected $columnaTesis = 'id_tesis';
    protected $columnaIdFundamentacion = 'id_fundamentacion';
    
    protected $tablaFundamentacion;
    protected $tablaTesis;
    protected $tablaAprobada;
    protected $tablaDesaprobada;
    protected $columnaIdFundamentacionPrimaria;
    protected $columnaIdTesisPrimaria;
    protected $columnaIdAprobadaPrimaria;
    protected $columnaIdDesaprobadaPrimaria;

    public function __construct()
    {
        $instanciaFundamentacion = new $this->modelo;
        $instanciaTesis = new $this->modeloTesis;
        $instanciaAprobada = new $this->modeloAprobada;
        $instanciaDesaprobada = new $this->modeloDesaprobada;
        
        $this->tablaFundamentacion = $instanciaFundamentacion->getTable();
        $this->tablaTesis = $instanciaTesis->getTable();
        $this->tablaAprobada = $instanciaAprobada->getTable();
        $this->tablaDesaprobada = $instanciaDesaprobada->getTable();
        
        $this->columnaIdFundamentacionPrimaria = $instanciaFundamentacion->getKeyName();
        $this->columnaIdTesisPrimaria = $instanciaTesis->getKeyName();
        $this->columnaIdAprobadaPrimaria = $instanciaAprobada->getKeyName();
        $this->columnaIdDesaprobadaPrimaria = $instanciaDesaprobada->getKeyName();
    }

    public function mostrar(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $buscar = $request->input('buscar');
            $filtroFacultad = $request->input('filtro_facultad');
            $filtroCarrera = $request->input('filtro_carrera');
            $filtroEstado = $request->input('filtro_estado');
            $porPagina = $request->input('por_pagina', 10);
            
            // Construir la consulta - CARGAR TODAS LAS VERSIONES SIN LIMITE
            $query = $this->modelo::with([
                'tesis.estudiante.carrera.facultad',
                'aprobada',
                'desaprobada',
                'ultimaVersion',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'asc'); // Cambiado a asc para mostrar en orden
                }
            ]);
            
            // Aplicar búsqueda
            if ($buscar) {
                $query->where(function($q) use ($buscar) {
                    $q->whereHas('tesis', function($q) use ($buscar) {
                        $q->where('Nombre_trabajo', 'LIKE', "%{$buscar}%")
                          ->orWhereHas('estudiante', function($q) use ($buscar) {
                              $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                                ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                          });
                    });
                });
            }
            
            // Aplicar filtro por facultad
            if ($filtroFacultad) {
                $query->whereHas('tesis.estudiante.carrera.facultad', function($q) use ($filtroFacultad) {
                    $q->where('id', $filtroFacultad);
                });
            }
            
            // Aplicar filtro por carrera
            if ($filtroCarrera) {
                $query->whereHas('tesis.estudiante.carrera', function($q) use ($filtroCarrera) {
                    $q->where('id', $filtroCarrera);
                });
            }
            
            // Aplicar filtro por estado
            if ($filtroEstado) {
                if ($filtroEstado === 'aprobada') {
                    $query->whereHas('aprobada');
                } elseif ($filtroEstado === 'desaprobada') {
                    $query->whereHas('desaprobada');
                } elseif ($filtroEstado === 'pendiente') {
                    $query->whereDoesntHave('aprobada')
                          ->whereDoesntHave('desaprobada');
                }
            }
            
            // Obtener las fundamentaciones con paginación
            $fundamentaciones = $query->paginate($porPagina);
            
            // Obtener datos adicionales para la vista
            $tesis = $this->modeloTesis::all();
            $facultades = Facultad::all();
            $carreras = Carrera::all();
            
            return view('gestionar.gestionarFundamentación.gestionarFundamentaciones', compact(
                'fundamentaciones', 
                'tesis',
                'facultades',
                'carreras'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar las fundamentaciones: ' . $e->getMessage());
        }
    }

    public function crear(Request $request)
    {
        try {
            $tesis = $this->modeloTesis::all();
            
            // Obtener el ID de la tesis desde la URL (si existe)
            $idTesisSeleccionada = $request->query('tesis_id');
            $tesisSeleccionada = null;
            
            if ($idTesisSeleccionada) {
                $tesisSeleccionada = $this->modeloTesis::find($idTesisSeleccionada);
            }
            
            return view('gestionar.gestionarFundamentación.crearFundamentación', compact('tesis', 'tesisSeleccionada'));
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de creación');
        }
    }

    public function ver($id)
    {
        try {
            // Cargar la fundamentación con todas las relaciones necesarias
            $fundamentacion = $this->modelo::with([
                'tesis.estudiante.carrera.facultad',
                'aprobada',
                'desaprobada',
                'recomendacion',
                'profesores',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'desc');
                }
            ])->findOrFail($id);
            
            return view('gestionar.gestionarFundamentación.verFundamentación', compact('fundamentacion'));
            
        } catch (\Exception $e) {
            return redirect()->route('gestionarFundamentaciones')
                ->with('error', 'Error al cargar los detalles de la fundamentación: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $fundamentacion = $this->modelo::with(['versiones' => function($query) {
                $query->orderBy('version_numero', 'desc');
            }])->findOrFail($id);
            
            $tesis = $this->modeloTesis::all();
            $ultimaVersion = $fundamentacion->versiones->last();
            
            return view('gestionar.gestionarFundamentación.editarFundamentación', compact('fundamentacion', 'tesis', 'ultimaVersion'));
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de edición');
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
                'documento' => 'required|file|max:10240',
                'descripcion' => 'nullable|string|max:500',
                'crear_nueva_version' => 'sometimes|boolean'
            ], [
                'id_tesis.required' => 'La tesis es obligatoria',
                'id_tesis.exists' => 'La tesis seleccionada no existe',
                'documento.required' => 'El documento es obligatorio',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.max' => 'El documento no puede exceder los 10MB',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe una fundamentación para esta tesis
            $existente = $this->modelo::where($this->columnaTesis, $request->id_tesis)->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una fundamentación para esta tesis')
                    ->withInput();
            }

            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $this->allowedExtensions)) {
                return redirect()->back()
                    ->with('error', 'Solo se permiten archivos PDF, DOC y DOCX')
                    ->withInput();
            }

            DB::beginTransaction();

            try {
                // Crear la fundamentación
                $fundamentacion = new $this->modelo();
                $fundamentacion->{$this->columnaTesis} = $request->id_tesis;
                $fundamentacion->save();

                // Preparar nombre del archivo para la primera versión
                $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v1_{$nombreOriginal}.{$extension}";
                
                // Almacenar el archivo en una carpeta específica para esta fundamentación
                $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

                // Crear la primera versión
                $version = new version_fundamentacion();
                $version->id_fundamentacion = $fundamentacion->id_fundamentacion;
                $version->version_numero = 1;
                $version->nombre_archivo = $nombreArchivo;
                $version->ruta_documento = $path;
                $version->tamanio = $file->getSize();
                $version->tipo = $extension;
                $version->descripcion = $request->descripcion;
                $version->save();

                DB::commit();

                return redirect(route($this->rutaVista))
                    ->with('success', 'Fundamentación agregada correctamente con la versión 1');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la fundamentación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
                'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
                'documento' => 'sometimes|file|max:10240',
                'descripcion' => 'nullable|string|max:500',
                'version_id' => 'nullable|exists:version_fundamentacion,id',
                'accion_version' => 'nullable|in:actualizar,crear'
            ], [
                'id_tesis.required' => 'La tesis es obligatoria',
                'id_tesis.exists' => 'La tesis seleccionada no existe',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.max' => 'El documento no puede exceder los 10MB',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra fundamentación para la nueva tesis (excluyendo la actual)
            $existente = $this->modelo::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaIdFundamentacionPrimaria, '!=', $request->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una fundamentación para esta tesis')
                    ->withInput();
            }

            DB::beginTransaction();

            try {
                $fundamentacion = $this->modelo::find($request->id);
                
                if ($fundamentacion) {
                    // Actualizar la tesis asociada
                    $fundamentacion->{$this->columnaTesis} = $request->id_tesis;
                    $fundamentacion->save();

                    // Manejar la gestión de versiones
                    $this->gestionarVersionesFundamentacion($request, $fundamentacion);

                    DB::commit();
                    
                    return redirect(route($this->rutaVista))
                        ->with('success', 'Fundamentación modificada correctamente');
                }

                return redirect()->back()
                    ->with('error', 'No se encontró la fundamentación a modificar');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la fundamentación: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function gestionarVersionesFundamentacion($request, $fundamentacion)
    {
        // Si se sube un documento
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($extension, $this->allowedExtensions)) {
                throw new \Exception('Solo se permiten archivos PDF, DOC y DOCX');
            }

            // Determinar qué acción realizar con la versión
            $accion = $request->accion_version ?? 'crear';
            
            if ($accion === 'actualizar' && $request->version_id) {
                // Actualizar versión existente
                $version = version_fundamentacion::find($request->version_id);
                
                if ($version && $version->id_fundamentacion == $fundamentacion->id_fundamentacion) {
                    // Eliminar archivo anterior
                    if (Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }

                    // Preparar nuevo nombre manteniendo el número de versión
                    $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$version->version_numero}_{$nombreOriginal}.{$extension}";
                    
                    // Almacenar nuevo archivo
                    $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

                    $version->nombre_archivo = $nombreArchivo;
                    $version->ruta_documento = $path;
                    $version->tamanio = $file->getSize();
                    $version->tipo = $extension;
                    $version->descripcion = $request->descripcion;
                    $version->save();
                }
            } else {
                // Crear nueva versión
                $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)
                    ->orderBy('version_numero', 'desc')
                    ->first();
                
                $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

                // Preparar nombre del archivo
                $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";
                
                // Almacenar el archivo
                $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

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
            }
        } elseif ($request->has('descripcion') && $request->version_id) {
            // Solo actualizar descripción de una versión existente
            $version = version_fundamentacion::find($request->version_id);
            
            if ($version && $version->id_fundamentacion == $fundamentacion->id_fundamentacion) {
                $version->descripcion = $request->descripcion;
                $version->save();
            }
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $fundamentacion = $this->modelo::find($id);

            if ($fundamentacion) {
                // Eliminar todas las versiones y sus archivos físicos
                $versiones = version_fundamentacion::where('id_fundamentacion', $id)->get();
                
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    // Eliminar el registro de la versión
                    $version->delete();
                }
                
                // Intentar eliminar la carpeta de la fundamentación si está vacía
                $folderPath = "{$this->storageFolder}/{$id}";
                if (Storage::exists($folderPath) && count(Storage::files($folderPath)) === 0) {
                    Storage::deleteDirectory($folderPath);
                }
                
                // Eliminar el registro de la fundamentación
                $this->modelo::destroy($id);
            }

            return redirect(route($this->rutaVista))
                ->with('success', 'Fundamentación y todas sus versiones eliminadas correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la fundamentación: ' . $e->getMessage());
        }
    }

    public function eliminarVersion(Request $request, $idVersion)
    {
        try {
            $version = version_fundamentacion::findOrFail($idVersion);
            $fundamentacionId = $version->id_fundamentacion;
            $versionNumero = $version->version_numero;

            // Verificar que no sea la única versión
            $totalVersiones = version_fundamentacion::where('id_fundamentacion', $fundamentacionId)->count();
            
            if ($totalVersiones <= 1) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la única versión de la fundamentación');
            }

            // Eliminar archivo físico
            if (Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }

            // Eliminar registro
            $version->delete();

            // Reordenar números de versión
            $this->reordenarVersiones($fundamentacionId);

            return redirect()->route('verFundamentación', $fundamentacionId)
                ->with('success', "Versión {$versionNumero} eliminada correctamente");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la versión: ' . $e->getMessage());
        }
    }

    private function reordenarVersiones($idFundamentacion)
    {
        DB::beginTransaction();
        try {
            $versiones = version_fundamentacion::where('id_fundamentacion', $idFundamentacion)
                ->orderBy('created_at', 'asc')
                ->get();

            $numero = 1;
            foreach ($versiones as $version) {
                $version->version_numero = $numero;
                $version->save();
                $numero++;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function vaciar()
    {
        try {
            // Obtener todas las fundamentaciones antes de eliminarlas
            $fundamentaciones = $this->modelo::all();
            
            // Eliminar todas las versiones y sus archivos
            foreach ($fundamentaciones as $fundamentacion) {
                $versiones = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)->get();
                
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    $version->delete();
                }
                
                // Intentar eliminar la carpeta de la fundamentación
                $folderPath = "{$this->storageFolder}/{$fundamentacion->id_fundamentacion}";
                if (Storage::exists($folderPath)) {
                    Storage::deleteDirectory($folderPath);
                }
            }
            
            // Eliminar los registros de la base de datos
            $this->modelo::query()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Todas las fundamentaciones y versiones han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las fundamentaciones: ' . $e->getMessage()
            ]);
        }
    }

    public function verDocumento($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            ]);

            if ($validator->fails()) {
                abort(404, 'Fundamentación no encontrada');
            }

            // Obtener la última versión de la fundamentación
            $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $id)
                ->orderBy('version_numero', 'desc')
                ->first();
            
            if (!$ultimaVersion || empty($ultimaVersion->ruta_documento)) {
                abort(404, 'No hay documento asociado a esta fundamentación');
            }
            
            if (!Storage::exists($ultimaVersion->ruta_documento)) {
                abort(404, 'Archivo no encontrado en el almacenamiento');
            }
            
            // Descargar el archivo
            return Storage::download($ultimaVersion->ruta_documento, $ultimaVersion->nombre_archivo);
        } catch (\Exception $e) {
            abort(500, 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    public function verDocumentoVersion($idVersion)
    {
       
            $version = version_fundamentacion::findOrFail($idVersion);
            
           
            
            // Descargar el archivo
            return Storage::download($version->ruta_documento, $version->nombre_archivo);
       
    }

    public function aprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
            }

            $id = $request->id;
            $fundamentacion = $this->modelo::find($id);
            
            if ($fundamentacion) {
                // Verificar si ya está aprobada
                if ($fundamentacion->aprobada) {
                    return redirect()->back()
                        ->with('error', 'Esta fundamentación ya está aprobada');
                }

                // Verificar si está desaprobada y eliminar de desaprobadas
                if ($fundamentacion->desaprobada) {
                    $this->modeloDesaprobada::where($this->columnaIdFundamentacion, $id)->delete();
                }

                // Mover a fundamentaciones_aprobadas
                $obj = new $this->modeloAprobada();
                $obj->{$this->columnaIdFundamentacion} = $id;
                $obj->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Fundamentación aprobada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se pudo encontrar la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al aprobar la fundamentación: ' . $e->getMessage());
        }
    }

    public function desaprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
            }

            $id = $request->id;
            $fundamentacion = $this->modelo::find($id);
            
            if ($fundamentacion) {
                // Verificar si ya está desaprobada
                if ($fundamentacion->desaprobada) {
                    return redirect()->back()
                        ->with('error', 'Esta fundamentación ya está desaprobada');
                }

                // Verificar si está aprobada y eliminar de aprobadas
                if ($fundamentacion->aprobada) {
                    $this->modeloAprobada::where($this->columnaIdFundamentacion, $id)->delete();
                }

                // Mover a fundamentaciones_desaprobadas
                $obj = new $this->modeloDesaprobada();
                $obj->{$this->columnaIdFundamentacion} = $id;
                $obj->save();

                return redirect(route($this->rutaVista))
                    ->with('success', 'Fundamentación desaprobada correctamente');
            }
            
            return redirect()->back()
                ->with('error', 'No se pudo encontrar la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desaprobar la fundamentación: ' . $e->getMessage());
        }
    }

    public function revertir(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
            }

            $id = $request->id;
            $fundamentacion = $this->modelo::find($id);
            
            if ($fundamentacion) {
                // Eliminar de fundamentaciones_aprobadas si existe
                if ($fundamentacion->aprobada) {
                    $this->modeloAprobada::where($this->columnaIdFundamentacion, $id)->delete();
                }
                
                // Eliminar de fundamentaciones_desaprobadas si existe
                if ($fundamentacion->desaprobada) {
                    $this->modeloDesaprobada::where($this->columnaIdFundamentacion, $id)->delete();
                }
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Fundamentación revertida a pendiente correctamente');
            }
            
            return redirect()->back()
                ->with('error', 'No se pudo encontrar la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al revertir la fundamentación: ' . $e->getMessage());
        }
    }

    public function fundamentacionesAprobadas()
    {
        try {
            $fundamentaciones = $this->modelo::with(['tesis', 'aprobada', 'ultimaVersion'])
                                ->whereHas('aprobada')
                                ->get();

            return view('consultas.fundamentaciones.fundamentacionesAprobadas', compact('fundamentaciones'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar las fundamentaciones aprobadas: ' . $e->getMessage());
        }
    }
}