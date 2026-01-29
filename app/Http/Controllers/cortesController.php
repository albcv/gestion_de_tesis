<?php

namespace App\Http\Controllers;

use App\Models\cortes_aprobados;
use App\Models\Cortes_de_tesis;
use App\Models\cortes_desaprobados;
use App\Models\Tesis;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\version_corte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class cortesController extends Controller
{
    protected $modeloCorte = Cortes_de_tesis::class;
    protected $modeloTesis = Tesis::class;
    protected $modeloAprobado = cortes_aprobados::class;
    protected $modeloDesaprobado = cortes_desaprobados::class;
    protected $modeloVersion = version_corte::class;
    protected $rutaVista = 'gestionarCortes';
    protected $storageFolder = 'cortes';
    protected $allowedExtensions = ['pdf', 'doc', 'docx'];
    protected $columnaTesis = 'id_tesis';
    protected $columnaNumeroCorte = 'Numero_corte';
    protected $columnaIdCorte = 'idCortes_de_tesis';
    
    protected $tablaCorte;
    protected $tablaTesis;
    protected $tablaAprobado;
    protected $tablaDesaprobado;
    protected $tablaVersion;
    protected $columnaIdCortePrimaria;
    protected $columnaIdTesisPrimaria;
    protected $columnaIdAprobadoPrimaria;
    protected $columnaIdDesaprobadoPrimaria;
    protected $columnaIdVersionPrimaria;
    protected $columnaVersionIdCorte = 'id_corte';

    public function __construct()
    {
        $instanciaCorte = new $this->modeloCorte;
        $instanciaTesis = new $this->modeloTesis;
        $instanciaAprobado = new $this->modeloAprobado;
        $instanciaDesaprobado = new $this->modeloDesaprobado;
        $instanciaVersion = new $this->modeloVersion;
        
        $this->tablaCorte = $instanciaCorte->getTable();
        $this->tablaTesis = $instanciaTesis->getTable();
        $this->tablaAprobado = $instanciaAprobado->getTable();
        $this->tablaDesaprobado = $instanciaDesaprobado->getTable();
        $this->tablaVersion = $instanciaVersion->getTable();
        
        $this->columnaIdCortePrimaria = $instanciaCorte->getKeyName();
        $this->columnaIdTesisPrimaria = $instanciaTesis->getKeyName();
        $this->columnaIdAprobadoPrimaria = $instanciaAprobado->getKeyName();
        $this->columnaIdDesaprobadoPrimaria = $instanciaDesaprobado->getKeyName();
        $this->columnaIdVersionPrimaria = $instanciaVersion->getKeyName();
    }

    // Agrega este método en la clase cortesController
private function sanitizeFileName($filename)
{
    // Reemplazar caracteres especiales latinos
    $clean = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'],
        ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'],
        $filename
    );
    
    // Reemplazar espacios por guiones bajos
    $clean = str_replace(' ', '_', $clean);
    
    // Eliminar cualquier otro carácter no alfanumérico (excepto guiones bajos y puntos)
    $clean = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);
    
    return trim($clean);
}

    public function mostrar(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $buscar = $request->input('buscar');
            $filtroFacultad = $request->input('filtro_facultad');
            $filtroCarrera = $request->input('filtro_carrera');
            $filtroEstado = $request->input('filtro_estado');
            $filtroNumeroCorte = $request->input('filtro_numero_corte');
            $porPagina = $request->input('por_pagina', 10);
            
            // Construir la consulta - CARGAR TODAS LAS VERSIONES
            $query = $this->modeloCorte::with([
                'tesis.estudiante.carrera.facultad',
                'aprobado',
                'desaprobado',
                'ultimaVersion',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'asc');
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
                if ($filtroEstado === 'aprobado') {
                    $query->whereHas('aprobado');
                } elseif ($filtroEstado === 'desaprobado') {
                    $query->whereHas('desaprobado');
                } elseif ($filtroEstado === 'pendiente') {
                    $query->whereDoesntHave('aprobado')
                          ->whereDoesntHave('desaprobado');
                }
            }
            
            // Aplicar filtro por número de corte
            if ($filtroNumeroCorte) {
                $query->where($this->columnaNumeroCorte, $filtroNumeroCorte);
            }
            
            // Obtener los cortes con paginación
            $cortes = $query->paginate($porPagina);
            
            // Obtener datos adicionales para la vista
            $tesis = $this->modeloTesis::whereHas('fundamentacion.aprobada')->get();
            $facultades = Facultad::all();
            $carreras = Carrera::all();
            
            return view('gestionar.gestionarCortes.gestionarCortes', compact(
                'cortes', 
                'tesis',
                'facultades',
                'carreras'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar los cortes: ' . $e->getMessage());
        }
    }

    public function crear(Request $request)
    {
        try {
            // Obtener el ID de la tesis si viene de detalles de tesis
            $tesisId = $request->input('tesis_id');
            
            // Obtener todas las tesis que tienen fundamentación aprobada
            $tesis = $this->modeloTesis::whereHas('fundamentacion.aprobada')
                ->with('estudiante')
                ->get();
            
            // Si se especifica una tesis específica, verificar que tenga fundamentación aprobada
            $tesisSeleccionada = null;
            if ($tesisId) {
                $tesisSeleccionada = $this->modeloTesis::with('fundamentacion.aprobada')
                    ->find($tesisId);
                
                // Verificar que la tesis tenga fundamentación aprobada
                if (!$tesisSeleccionada || !$tesisSeleccionada->fundamentacion || !$tesisSeleccionada->fundamentacion->aprobada) {
                    return redirect()->back()
                        ->with('error', 'La tesis seleccionada no tiene fundamentación aprobada. No se puede crear un corte.');
                }
            }
            
            return view('gestionar.gestionarCortes.crearCorte', compact('tesis', 'tesisId', 'tesisSeleccionada'));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }

    public function ver($id)
    {
        try {
            $corte = $this->modeloCorte::with([
                'tesis.estudiante.carrera.facultad',
                'aprobado',
                'desaprobado',
                'noConformidades',
                'profesores.departamento',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'desc');
                }
            ])->findOrFail($id);
            
            return view('gestionar.gestionarCortes.verCorte', compact('corte'));
            
        } catch (\Exception $e) {
            return redirect()->route('gestionarCortes')
                ->with('error', 'Error al cargar los detalles del corte: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $corte = $this->modeloCorte::with(['versiones' => function($query) {
                $query->orderBy('version_numero', 'desc');
            }])->findOrFail($id);
            
            // Obtener todas las tesis que tienen fundamentación aprobada
            $tesis = $this->modeloTesis::whereHas('fundamentacion.aprobada')
                ->with('estudiante')
                ->get();
            
            $ultimaVersion = $corte->versiones->last();
            
            return view('gestionar.gestionarCortes.editarCorte', compact('corte', 'tesis', 'ultimaVersion'));
            
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
                'número_corte' => 'required|integer|min:1|max:4',
                'documento' => 'required|file|max:10240',
                'enlace' => 'nullable|url|max:500',
                'descripcion' => 'nullable|string|max:500'
            ], [
                'id_tesis.required' => 'La tesis es obligatoria',
                'id_tesis.exists' => 'La tesis seleccionada no existe',
                'número_corte.required' => 'El número de corte es obligatorio',
                'número_corte.integer' => 'El número de corte debe ser un número entero',
                'número_corte.min' => 'El número de corte debe ser al menos 1',
                'número_corte.max' => 'El número de corte no puede ser mayor a 4',
                'documento.required' => 'El documento es obligatorio',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.max' => 'El documento no puede exceder los 10MB',
                'enlace.url' => 'El enlace de GitHub debe ser una URL válida',
                'enlace.max' => 'El enlace de GitHub no puede exceder los 500 caracteres',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar que la tesis tenga fundamentación aprobada
            $tesis = $this->modeloTesis::with('fundamentacion.aprobada')->find($request->id_tesis);
            
            if (!$tesis || !$tesis->fundamentacion) {
                return redirect()->back()
                    ->with('error', 'La tesis no tiene fundamentación. Primero debe crear y aprobar una fundamentación.')
                    ->withInput();
            }
            
            if (!$tesis->fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'La fundamentación de la tesis no está aprobada. No se puede crear un corte.')
                    ->withInput();
            }

            // Verificar si ya existe un corte con el mismo número para la misma tesis
            $existente = $this->modeloCorte::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaNumeroCorte, $request->número_corte)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe un corte con el mismo número para esta tesis')
                    ->withInput();
            }

            DB::beginTransaction();

            try {
                // Crear el corte
                $corte = new $this->modeloCorte();
                $corte->{$this->columnaTesis} = $request->id_tesis;
                $corte->{$this->columnaNumeroCorte} = $request->número_corte;
                $corte->save();

                // Procesar el archivo para la primera versión
                $file = $request->file('documento');
                $extension = strtolower($file->getClientOriginalExtension());
                
                if (!in_array($extension, $this->allowedExtensions)) {
                    throw new \Exception('Solo se permiten archivos PDF, DOC y DOCX');
                }

                // Preparar nombre del archivo para la primera versión
                $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // SANITIZAR EL NOMBRE
                $nombreOriginal = $this->sanitizeFileName($nombreOriginal);
                $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v1_{$nombreOriginal}.{$extension}";
                
                // Almacenar el archivo en una carpeta específica para este corte
                $path = $file->storeAs("{$this->storageFolder}/{$corte->idCortes_de_tesis}", $nombreArchivo);

                // Crear la primera versión
                $version = new version_corte();
                $version->id_corte = $corte->idCortes_de_tesis;
                $version->version_numero = 1;
                $version->nombre_archivo = $nombreArchivo;
                $version->ruta_documento = $path;
                $version->Enlace_Github = $request->enlace ?? '';
                $version->tamanio = $file->getSize();
                $version->tipo = $extension;
                $version->descripcion = $request->descripcion;
                $version->save();

                DB::commit();

                return redirect(route($this->rutaVista))
                    ->with('success', 'Corte agregado correctamente con la versión 1');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar el corte: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El corte no existe o ya ha sido eliminado');
            }

            $id = $request['id'];
            $corte = $this->modeloCorte::find($id);

            if ($corte) {
                // Eliminar todas las versiones y sus archivos físicos
                $versiones = version_corte::where('id_corte', $id)->get();
                
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    // Eliminar el registro de la versión
                    $version->delete();
                }
                
                // Intentar eliminar la carpeta del corte si está vacía
                $folderPath = "{$this->storageFolder}/{$id}";
                if (Storage::exists($folderPath) && count(Storage::files($folderPath)) === 0) {
                    Storage::deleteDirectory($folderPath);
                }
                
                // Eliminar el registro del corte
                $this->modeloCorte::destroy($id);
            }

            return redirect(route($this->rutaVista))
                ->with('success', 'Corte y todas sus versiones eliminadas correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el corte: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
                'número_corte' => 'required|integer|min:1|max:4',
                'documento' => 'sometimes|file|max:10240',
                'enlace' => 'nullable|url|max:500',
                'descripcion' => 'nullable|string|max:500',
                'version_id' => 'nullable|exists:version_corte,id',
                'accion_version' => 'nullable|in:actualizar,crear'
            ], [
                'id_tesis.required' => 'La tesis es obligatoria',
                'id_tesis.exists' => 'La tesis seleccionada no existe',
                'número_corte.required' => 'El número de corte es obligatorio',
                'número_corte.integer' => 'El número de corte debe ser un número entero',
                'número_corte.min' => 'El número de corte debe ser al menos 1',
                'número_corte.max' => 'El número de corte no puede ser mayor a 4',
                'documento.file' => 'El documento debe ser un archivo',
                'documento.max' => 'El documento no puede exceder los 10MB',
                'enlace.url' => 'El enlace de GitHub debe ser una URL válida',
                'enlace.max' => 'El enlace de GitHub no puede exceder los 500 caracteres',
                'descripcion.max' => 'La descripción no puede exceder los 500 caracteres'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar que la tesis tenga fundamentación aprobada
            $tesis = $this->modeloTesis::with('fundamentacion.aprobada')->find($request->id_tesis);
            
            if (!$tesis || !$tesis->fundamentacion) {
                return redirect()->back()
                    ->with('error', 'La tesis no tiene fundamentación.')
                    ->withInput();
            }
            
            if (!$tesis->fundamentacion->aprobada) {
                return redirect()->back()
                    ->with('error', 'La fundamentación de la tesis no está aprobada.')
                    ->withInput();
            }

            // Verificar si ya existe otro corte con el mismo número para la misma tesis (excluyendo el actual)
            $existente = $this->modeloCorte::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaNumeroCorte, $request->número_corte)
                ->where($this->columnaIdCortePrimaria, '!=', $request->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe otro corte con el mismo número para esta tesis')
                    ->withInput();
            }

            DB::beginTransaction();

            try {
                $corte = $this->modeloCorte::find($request->id);
                
                if ($corte) {
                    // Actualizar la tesis asociada y número de corte
                    $corte->{$this->columnaTesis} = $request->id_tesis;
                    $corte->{$this->columnaNumeroCorte} = $request->número_corte;
                    $corte->save();

                    // Manejar la gestión de versiones
                    $this->gestionarVersionesCorte($request, $corte);

                    DB::commit();
                    
                    return redirect(route($this->rutaVista))
                        ->with('success', 'Corte modificado correctamente');
                }

                return redirect()->back()
                    ->with('error', 'No se encontró el corte a modificar');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar el corte: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function gestionarVersionesCorte($request, $corte)
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
                $version = version_corte::find($request->version_id);
                
                if ($version && $version->id_corte == $corte->idCortes_de_tesis) {
                    // Eliminar archivo anterior
                    if (Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }

                    // Preparar nuevo nombre manteniendo el número de versión
                    $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    // SANITIZAR EL NOMBRE
                    $nombreOriginal = $this->sanitizeFileName($nombreOriginal);
                    $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$version->version_numero}_{$nombreOriginal}.{$extension}";

                    // Almacenar nuevo archivo
                    $path = $file->storeAs("{$this->storageFolder}/{$corte->idCortes_de_tesis}", $nombreArchivo);

                    $version->nombre_archivo = $nombreArchivo;
                    $version->ruta_documento = $path;
                    $version->Enlace_Github = $request->enlace ?? $version->Enlace_Github;
                    $version->tamanio = $file->getSize();
                    $version->tipo = $extension;
                    $version->descripcion = $request->descripcion;
                    $version->save();
                }
            } else {
                // Crear nueva versión
                $ultimaVersion = version_corte::where('id_corte', $corte->idCortes_de_tesis)
                    ->orderBy('version_numero', 'desc')
                    ->first();
                
                $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

                // Preparar nombre del archivo
                $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // SANITIZAR EL NOMBRE
                $nombreOriginal = $this->sanitizeFileName($nombreOriginal);
                $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";

                $path = $file->storeAs("{$this->storageFolder}/{$corte->idCortes_de_tesis}", $nombreArchivo);

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
            }
        } elseif ($request->has(['descripcion', 'enlace']) && $request->version_id) {
            // Solo actualizar descripción y enlace de una versión existente
            $version = version_corte::find($request->version_id);
            
            if ($version && $version->id_corte == $corte->idCortes_de_tesis) {
                $version->descripcion = $request->descripcion;
                $version->Enlace_Github = $request->enlace;
                $version->save();
            }
        }
    }

    public function vaciar()
    {
        try {
            // Obtener todos los cortes antes de eliminarlos
            $cortes = $this->modeloCorte::all();
            
            // Eliminar todas las versiones y sus archivos
            foreach ($cortes as $corte) {
                $versiones = version_corte::where('id_corte', $corte->idCortes_de_tesis)->get();
                
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    $version->delete();
                }
                
                // Intentar eliminar la carpeta del corte
                $folderPath = "{$this->storageFolder}/{$corte->idCortes_de_tesis}";
                if (Storage::exists($folderPath)) {
                    Storage::deleteDirectory($folderPath);
                }
            }
            
            // Eliminar los registros de la base de datos
            $this->modeloCorte::query()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Todos los cortes y versiones han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar los cortes: ' . $e->getMessage()
            ]);
        }
    }

    public function verDocumento($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            ]);

            if ($validator->fails()) {
                abort(404, 'Corte no encontrado');
            }

            // Obtener la última versión del corte
            $ultimaVersion = version_corte::where('id_corte', $id)
                ->orderBy('version_numero', 'desc')
                ->first();
            
            if (!$ultimaVersion || empty($ultimaVersion->ruta_documento)) {
                abort(404, 'No hay documento asociado a este corte');
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
        try {
            $version = version_corte::findOrFail($idVersion);
            
            if (empty($version->ruta_documento)) {
                abort(404, 'No hay documento asociado a esta versión');
            }
            
            if (!Storage::exists($version->ruta_documento)) {
                abort(404, 'Archivo no encontrado en el almacenamiento');
            }
            
            // Descargar el archivo
            return Storage::download($version->ruta_documento, $version->nombre_archivo);
        } catch (\Exception $e) {
            abort(500, 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    public function aprobarCorte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El corte no existe o ya ha sido eliminado');
            }

            $id = $request->id;
            $corte = $this->modeloCorte::find($id);
            
            if ($corte) {
                // Verificar si ya está aprobado
                if ($corte->aprobado) {
                    return redirect()->back()
                        ->with('error', 'Este corte ya está aprobado');
                }

                // Verificar si está desaprobado y eliminar de desaprobados
                if ($corte->desaprobado) {
                    $this->modeloDesaprobado::where('id_corte', $id)->delete();
                }

                // Mover a cortes_aprobados
                $obj = new $this->modeloAprobado();
                $obj->id_corte = $id;
                $obj->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Corte aprobado correctamente');
            }
            
            return redirect()->back()
                ->with('error', 'No se pudo encontrar el corte');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al aprobar el corte: ' . $e->getMessage());
        }
    }

    public function desaprobarCorte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El corte no existe o ya ha sido eliminado');
            }

            $id = $request->id;
            $corte = $this->modeloCorte::find($id);
            
            if ($corte) {
                // Verificar si ya está desaprobado
                if ($corte->desaprobado) {
                    return redirect()->back()
                        ->with('error', 'Este corte ya está desaprobado');
                }

                // Verificar si está aprobado y eliminar de aprobados
                if ($corte->aprobado) {
                    $this->modeloAprobado::where('id_corte', $id)->delete();
                }

                // Mover a cortes_desaprobados
                $obj = new $this->modeloDesaprobado();
                $obj->id_corte = $id;
                $obj->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Corte desaprobado correctamente');
            }
            
            return redirect()->back()
                ->with('error', 'No se pudo encontrar el corte');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desaprobar el corte: ' . $e->getMessage());
        }
    }

    public function revertirCorte(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El corte no existe o ya ha sido eliminado');
            }

            $id = $request->id;
            $corte = $this->modeloCorte::find($id);
            
            if ($corte) {
                // Eliminar de cortes_aprobados si existe
                if ($corte->aprobado) {
                    $this->modeloAprobado::where('id_corte', $id)->delete();
                }
                
                // Eliminar de cortes_desaprobados si existe
                if ($corte->desaprobado) {
                    $this->modeloDesaprobado::where('id_corte', $id)->delete();
                }
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Corte revertido a pendiente correctamente');
            }
            
            return redirect()->back()
                ->with('error', 'No se pudo encontrar el corte');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al revertir el corte: ' . $e->getMessage());
        }
    }

    public function eliminarVersion(Request $request, $idVersion)
    {
        try {
            $version = version_corte::findOrFail($idVersion);
            $corteId = $version->id_corte;
            $versionNumero = $version->version_numero;

            // Verificar que no sea la única versión
            $totalVersiones = version_corte::where('id_corte', $corteId)->count();
            
            if ($totalVersiones <= 1) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar la única versión del corte');
            }

            // Eliminar archivo físico
            if (Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }

            // Eliminar registro
            $version->delete();

            // Reordenar números de versión
            $this->reordenarVersionesCorte($corteId);

            return redirect()->route('verCorte', $corteId)
                ->with('success', "Versión {$versionNumero} eliminada correctamente");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la versión: ' . $e->getMessage());
        }
    }

    private function reordenarVersionesCorte($idCorte)
    {
        DB::beginTransaction();
        try {
            $versiones = version_corte::where('id_corte', $idCorte)
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
}