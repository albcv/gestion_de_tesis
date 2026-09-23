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

    private function sanitizeFileName($filename)
    {
        $clean = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'],
            $filename
        );
        $clean = str_replace(' ', '_', $clean);
        $clean = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);
        return trim($clean);
    }

    /**
     * Listado, formulario o detalles según query param.
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return $this->crear($request);
        }
        if ($accion === 'editar' && $id) {
            return $this->editar($id);
        }
        if ($accion === 'detalles' && $id) {
            return $this->ver($id);
        }

        try {
            $buscar = $request->input('buscar');
            $filtroFacultad = $request->input('filtro_facultad');
            $filtroCarrera = $request->input('filtro_carrera');
            $filtroEstado = $request->input('filtro_estado');
            $filtroNumeroCorte = $request->input('filtro_numero_corte');
            $porPagina = $request->input('por_pagina', 10);

            $query = $this->modeloCorte::with([
                'tesis.estudiante.carrera.facultad',
                'aprobado',
                'desaprobado',
                'ultimaVersion',
                'versiones' => fn($q) => $q->orderBy('version_numero', 'asc'),
            ]);

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->whereHas('tesis', function ($q) use ($buscar) {
                        $q->where('Nombre_trabajo', 'LIKE', "%{$buscar}%")
                            ->orWhereHas('estudiante', function ($q) use ($buscar) {
                                $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                                    ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                                    ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                                    ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                            });
                    });
                });
            }

            if ($filtroFacultad) {
                $query->whereHas('tesis.estudiante.carrera.facultad', fn($q) => $q->where('idFacultad', $filtroFacultad));
            }

            if ($filtroCarrera) {
                $query->whereHas('tesis.estudiante.carrera', fn($q) => $q->where('id', $filtroCarrera));
            }

            if ($filtroEstado) {
                if ($filtroEstado === 'aprobado') {
                    $query->whereHas('aprobado');
                } elseif ($filtroEstado === 'desaprobado') {
                    $query->whereHas('desaprobado');
                } elseif ($filtroEstado === 'pendiente') {
                    $query->whereDoesntHave('aprobado')->whereDoesntHave('desaprobado');
                }
            }

            if ($filtroNumeroCorte) {
                $query->where($this->columnaNumeroCorte, $filtroNumeroCorte);
            }

            $cortes = $query->paginate($porPagina)->appends($request->query());

            $facultades = Facultad::all();
            $carreras = Carrera::all();

            return view('gestionar.corte.index', compact('cortes', 'facultades', 'carreras'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los cortes: ' . $e->getMessage());
        }
    }

    public function crear(Request $request)
    {
        try {
            $tesisId = $request->input('tesis_id');

            $tesis = $this->modeloTesis::whereHas('fundamentacion.aprobada')
                ->with('estudiante')
                ->get();

            $tesisSeleccionada = null;
            if ($tesisId) {
                $tesisSeleccionada = $this->modeloTesis::with('fundamentacion.aprobada')->find($tesisId);
                if (!$tesisSeleccionada || !$tesisSeleccionada->fundamentacion || !$tesisSeleccionada->fundamentacion->aprobada) {
                    return redirect()->route($this->rutaVista)
                        ->with('error', 'La tesis seleccionada no tiene fundamentación aprobada.');
                }
            }

            return view('gestionar.corte.formulario', compact('tesis', 'tesisId', 'tesisSeleccionada'));

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
                'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
            ])->findOrFail($id);

            return view('gestionar.corte.detalles', compact('corte'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los detalles del corte: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $corte = $this->modeloCorte::with([
                'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
            ])->findOrFail($id);

            $tesis = $this->modeloTesis::whereHas('fundamentacion.aprobada')
                ->with('estudiante')
                ->get();

            $ultimaVersion = $corte->versiones->first();

            return view('gestionar.corte.formulario', compact('corte', 'tesis', 'ultimaVersion'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de edición');
        }
    }

    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
            'número_corte' => 'required|integer|min:1|max:4',
            'documento' => 'required|file|max:10240',
            'enlace' => 'nullable|url|max:500',
            'descripcion' => 'nullable|string|max:500',
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
            'enlace.max' => 'El enlace no puede exceder los 500 caracteres',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        $tesis = $this->modeloTesis::with('fundamentacion.aprobada')->find($request->id_tesis);
        if (!$tesis || !$tesis->fundamentacion || !$tesis->fundamentacion->aprobada) {
            return redirect($urlFormCrear)
                ->with('error', 'La fundamentación de la tesis no está aprobada.')
                ->withInput();
        }

        if ($this->modeloCorte::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaNumeroCorte, $request->número_corte)
                ->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe un corte con el mismo número para esta tesis')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $corte = new $this->modeloCorte();
            $corte->{$this->columnaTesis} = $request->id_tesis;
            $corte->{$this->columnaNumeroCorte} = $request->número_corte;
            $corte->save();

            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $this->allowedExtensions)) {
                throw new \Exception('Solo se permiten archivos PDF, DOC y DOCX');
            }

            $nombreOriginal = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v1_{$nombreOriginal}.{$extension}";
            $path = $file->storeAs("{$this->storageFolder}/{$corte->idCortes_de_tesis}", $nombreArchivo);

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

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Corte creado correctamente con la versión 1.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar el corte: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'El corte no existe o ya ha sido eliminado');
        }

        $id = $request->id;
        $corte = $this->modeloCorte::find($id);

        if ($corte) {
            $versiones = version_corte::where('id_corte', $id)->get();
            foreach ($versiones as $version) {
                if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                    Storage::delete($version->ruta_documento);
                }
                $version->delete();
            }

            $folderPath = "{$this->storageFolder}/{$id}";
            if (Storage::exists($folderPath) && count(Storage::files($folderPath)) === 0) {
                Storage::deleteDirectory($folderPath);
            }

            $this->modeloCorte::destroy($id);
        }

        return redirect()->route($this->rutaVista);
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un corte para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $versiones = version_corte::where('id_corte', $id)->get();
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    $version->delete();
                }
                $folderPath = "{$this->storageFolder}/{$id}";
                if (Storage::exists($folderPath)) {
                    Storage::deleteDirectory($folderPath);
                }
            }

            $this->modeloCorte::whereIn($this->columnaIdCortePrimaria, $ids)->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar los cortes: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, ['accion' => 'editar', 'id' => $request->id]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
            'número_corte' => 'required|integer|min:1|max:4',
            'documento' => 'sometimes|file|max:10240',
            'enlace' => 'nullable|url|max:500',
            'descripcion' => 'nullable|string|max:500',
            'version_id' => 'nullable|exists:version_corte,id',
            'accion_version' => 'nullable|in:actualizar,crear',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        $tesis = $this->modeloTesis::with('fundamentacion.aprobada')->find($request->id_tesis);
        if (!$tesis || !$tesis->fundamentacion || !$tesis->fundamentacion->aprobada) {
            return redirect($urlFormEditar)
                ->with('error', 'La fundamentación de la tesis no está aprobada.')
                ->withInput();
        }

        if ($this->modeloCorte::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaNumeroCorte, $request->número_corte)
                ->where($this->columnaIdCortePrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otro corte con el mismo número para esta tesis')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $corte = $this->modeloCorte::find($request->id);
            if (!$corte) {
                DB::rollBack();
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No se encontró el corte a modificar');
            }

            $corte->{$this->columnaTesis} = $request->id_tesis;
            $corte->{$this->columnaNumeroCorte} = $request->número_corte;
            $corte->save();

            $this->gestionarVersionesCorte($request, $corte);

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar el corte: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function gestionarVersionesCorte($request, $corte)
    {
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->allowedExtensions)) {
                throw new \Exception('Solo se permiten archivos PDF, DOC y DOCX');
            }

            $accion = $request->accion_version ?? 'crear';

            if ($accion === 'actualizar' && $request->version_id) {
                $version = version_corte::find($request->version_id);
                if ($version && $version->id_corte == $corte->idCortes_de_tesis) {
                    if (Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }

                    $nombreOriginal = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                    $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$version->version_numero}_{$nombreOriginal}.{$extension}";
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
                $ultimaVersion = version_corte::where('id_corte', $corte->idCortes_de_tesis)
                    ->orderBy('version_numero', 'desc')
                    ->first();

                $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

                $nombreOriginal = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
                $nombreArchivo = "corte_{$corte->idCortes_de_tesis}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";
                $path = $file->storeAs("{$this->storageFolder}/{$corte->idCortes_de_tesis}", $nombreArchivo);

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
            $version = version_corte::find($request->version_id);
            if ($version && $version->id_corte == $corte->idCortes_de_tesis) {
                $version->descripcion = $request->descripcion;
                $version->Enlace_Github = $request->enlace;
                $version->save();
            }
        }
    }

    public function eliminarVersion(Request $request, $idVersion)
    {
        try {
            $version = version_corte::findOrFail($idVersion);
            $corteId = $version->id_corte;
            $versionNumero = $version->version_numero;

            $totalVersiones = version_corte::where('id_corte', $corteId)->count();
            if ($totalVersiones <= 1) {
                return redirect()->route($this->rutaVista, ['accion' => 'detalles', 'id' => $corteId])
                    ->with('error', 'No se puede eliminar la única versión del corte');
            }

            if (Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }
            $version->delete();
            $this->reordenarVersionesCorte($corteId);

            return redirect()->route($this->rutaVista, ['accion' => 'detalles', 'id' => $corteId]);

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

    public function vaciar()
    {
        try {
            $cortes = $this->modeloCorte::all();

            foreach ($cortes as $corte) {
                $versiones = version_corte::where('id_corte', $corte->idCortes_de_tesis)->get();
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    $version->delete();
                }
                $folderPath = "{$this->storageFolder}/{$corte->idCortes_de_tesis}";
                if (Storage::exists($folderPath)) {
                    Storage::deleteDirectory($folderPath);
                }
            }

            $this->modeloCorte::query()->delete();
            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al vaciar los cortes: ' . $e->getMessage());
        }
    }

    public function exportarCsv()
    {
        $cortes = $this->modeloCorte::with([
            'tesis.estudiante.carrera.facultad',
            'aprobado',
            'desaprobado',
        ])->orderBy($this->columnaNumeroCorte)->get();

        $nombreArchivo = 'cortes_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($cortes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Trabajo', 'Estudiante', 'Carrera', 'Facultad', 'N° Corte', 'Estado'], ';');

            foreach ($cortes as $c) {
                $trabajo = $c->tesis->Nombre_trabajo ?? '—';
                $estudiante = '—';
                $carrera = '—';
                $facultad = '—';

                if ($c->tesis && $c->tesis->estudiante) {
                    $estudiante = trim($c->tesis->estudiante->Nombre_estudiante . ' ' .
                        $c->tesis->estudiante->Apellido1 . ' ' .
                        $c->tesis->estudiante->Apellido2);

                    if ($c->tesis->estudiante->carrera) {
                        $carrera = $c->tesis->estudiante->carrera->Nombre_carrera;
                        if ($c->tesis->estudiante->carrera->facultad) {
                            $facultad = $c->tesis->estudiante->carrera->facultad->Siglas;
                        }
                    }
                }

                $estado = 'Pendiente';
                if ($c->aprobado) $estado = 'Aprobado';
                elseif ($c->desaprobado) $estado = 'Desaprobado';

                fputcsv($out, [$trabajo, $estudiante, $carrera, $facultad, $c->Numero_corte, $estado], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
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

            $ultimaVersion = version_corte::where('id_corte', $id)
                ->orderBy('version_numero', 'desc')
                ->first();

            if (!$ultimaVersion || empty($ultimaVersion->ruta_documento)) {
                abort(404, 'No hay documento asociado a este corte');
            }
            if (!Storage::exists($ultimaVersion->ruta_documento)) {
                abort(404, 'Archivo no encontrado en el almacenamiento');
            }

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

            return Storage::download($version->ruta_documento, $version->nombre_archivo);
        } catch (\Exception $e) {
            abort(500, 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    public function aprobarCorte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'El corte no existe o ya ha sido eliminado');
        }

        $id = $request->id;
        $corte = $this->modeloCorte::find($id);

        if ($corte) {
            if ($corte->aprobado) {
                return redirect()->back()->with('error', 'Este corte ya está aprobado');
            }

            if ($corte->desaprobado) {
                $this->modeloDesaprobado::where('id_corte', $id)->delete();
            }

            $obj = new $this->modeloAprobado();
            $obj->id_corte = $id;
            $obj->save();
        }

        return redirect()->route($this->rutaVista);
    }

    public function desaprobarCorte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'El corte no existe o ya ha sido eliminado');
        }

        $id = $request->id;
        $corte = $this->modeloCorte::find($id);

        if ($corte) {
            if ($corte->desaprobado) {
                return redirect()->back()->with('error', 'Este corte ya está desaprobado');
            }

            if ($corte->aprobado) {
                $this->modeloAprobado::where('id_corte', $id)->delete();
            }

            $obj = new $this->modeloDesaprobado();
            $obj->id_corte = $id;
            $obj->save();
        }

        return redirect()->route($this->rutaVista);
    }

    public function revertirCorte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'El corte no existe o ya ha sido eliminado');
        }

        $id = $request->id;
        $corte = $this->modeloCorte::find($id);

        if ($corte) {
            if ($corte->aprobado) {
                $this->modeloAprobado::where('id_corte', $id)->delete();
            }
            if ($corte->desaprobado) {
                $this->modeloDesaprobado::where('id_corte', $id)->delete();
            }
        }

        return redirect()->route($this->rutaVista);
    }
}