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

    /**
     * Listado, formulario o detalles según query param.
     *
     *  /gestionarFundamentaciones                       → listado
     *  /gestionarFundamentaciones?accion=crear          → formulario crear
     *  /gestionarFundamentaciones?accion=editar&id=X    → formulario editar
     *  /gestionarFundamentaciones?accion=detalles&id=X  → detalles
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
            $porPagina = $request->input('por_pagina', 10);

            $query = $this->modelo::with([
                'tesis.estudiante.carrera.facultad',
                'aprobada',
                'desaprobada',
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
                if ($filtroEstado === 'aprobada') {
                    $query->whereHas('aprobada');
                } elseif ($filtroEstado === 'desaprobada') {
                    $query->whereHas('desaprobada');
                } elseif ($filtroEstado === 'pendiente') {
                    $query->whereDoesntHave('aprobada')->whereDoesntHave('desaprobada');
                }
            }

            $fundamentaciones = $query->paginate($porPagina)->appends($request->query());

            $facultades = Facultad::all();
            $carreras = Carrera::all();

            return view('gestionar.fundamentacion.index', compact(
                'fundamentaciones', 'facultades', 'carreras'
            ));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar las fundamentaciones: ' . $e->getMessage());
        }
    }

    public function crear(Request $request)
    {
        try {
            // Excluir tesis que ya tienen fundamentación
            $tesis = $this->modeloTesis::whereDoesntHave('fundamentacion')->get();

            $idTesisSeleccionada = $request->query('tesis_id');
            $tesisSeleccionada = null;

            if ($idTesisSeleccionada) {
                $tesisSeleccionada = $this->modeloTesis::find($idTesisSeleccionada);
            }

            return view('gestionar.fundamentacion.formulario', compact('tesis', 'tesisSeleccionada'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de creación');
        }
    }

    public function ver($id)
    {
        try {
            $fundamentacion = $this->modelo::with([
                'tesis.estudiante.carrera.facultad',
                'tesis.estudiante.grupo',
                'tesis.estudiante.modalidad',
                'aprobada',
                'desaprobada',
                'recomendacion',
                'profesores.departamento',
                'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
            ])->findOrFail($id);

            return view('gestionar.fundamentacion.detalles', compact('fundamentacion'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los detalles de la fundamentación: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $fundamentacion = $this->modelo::with([
                'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
            ])->findOrFail($id);

            $tesis = $this->modeloTesis::all();
            $ultimaVersion = $fundamentacion->versiones->first();

            return view('gestionar.fundamentacion.formulario', compact('fundamentacion', 'tesis', 'ultimaVersion'));

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
            'documento' => 'required|file|max:10240',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'id_tesis.required' => 'La tesis es obligatoria',
            'id_tesis.exists' => 'La tesis seleccionada no existe',
            'documento.required' => 'El documento es obligatorio',
            'documento.file' => 'El documento debe ser un archivo',
            'documento.max' => 'El documento no puede exceder los 10MB',
            'descripcion.max' => 'La descripción no puede exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaTesis, $request->id_tesis)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe una fundamentación para esta tesis')
                ->withInput();
        }

        $file = $request->file('documento');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $this->allowedExtensions)) {
            return redirect($urlFormCrear)
                ->with('error', 'Solo se permiten archivos PDF, DOC y DOCX')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $fundamentacion = new $this->modelo();
            $fundamentacion->{$this->columnaTesis} = $request->id_tesis;
            $fundamentacion->save();

            $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v1_{$nombreOriginal}.{$extension}";
            $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

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

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Fundamentación creada correctamente con la versión 1.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar la fundamentación: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, ['accion' => 'editar', 'id' => $request->id]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
            'id_tesis' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesisPrimaria,
            'documento' => 'sometimes|file|max:10240',
            'descripcion' => 'nullable|string|max:500',
            'version_id' => 'nullable|exists:version_fundamentacion,id',
            'accion_version' => 'nullable|in:actualizar,crear',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaTesis, $request->id_tesis)
                ->where($this->columnaIdFundamentacionPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe una fundamentación para esta tesis')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $fundamentacion = $this->modelo::find($request->id);
            if (!$fundamentacion) {
                DB::rollBack();
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No se encontró la fundamentación a modificar');
            }

            $fundamentacion->{$this->columnaTesis} = $request->id_tesis;
            $fundamentacion->save();

            $this->gestionarVersionesFundamentacion($request, $fundamentacion);

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar la fundamentación: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function gestionarVersionesFundamentacion($request, $fundamentacion)
    {
        if ($request->hasFile('documento')) {
            $file = $request->file('documento');
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->allowedExtensions)) {
                throw new \Exception('Solo se permiten archivos PDF, DOC y DOCX');
            }

            $accion = $request->accion_version ?? 'crear';

            if ($accion === 'actualizar' && $request->version_id) {
                $version = version_fundamentacion::find($request->version_id);

                if ($version && $version->id_fundamentacion == $fundamentacion->id_fundamentacion) {
                    if (Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }

                    $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$version->version_numero}_{$nombreOriginal}.{$extension}";
                    $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

                    $version->nombre_archivo = $nombreArchivo;
                    $version->ruta_documento = $path;
                    $version->tamanio = $file->getSize();
                    $version->tipo = $extension;
                    $version->descripcion = $request->descripcion;
                    $version->save();
                }
            } else {
                $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)
                    ->orderBy('version_numero', 'desc')
                    ->first();

                $nuevaVersionNumero = $ultimaVersion ? $ultimaVersion->version_numero + 1 : 1;

                $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $nombreArchivo = "fundamentacion_{$fundamentacion->id_fundamentacion}_v{$nuevaVersionNumero}_{$nombreOriginal}.{$extension}";
                $path = $file->storeAs("{$this->storageFolder}/{$fundamentacion->id_fundamentacion}", $nombreArchivo);

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
            $version = version_fundamentacion::find($request->version_id);
            if ($version && $version->id_fundamentacion == $fundamentacion->id_fundamentacion) {
                $version->descripcion = $request->descripcion;
                $version->save();
            }
        }
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
        }

        $id = $request->id;
        $fundamentacion = $this->modelo::find($id);

        if ($fundamentacion) {
            $versiones = version_fundamentacion::where('id_fundamentacion', $id)->get();

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

            $this->modelo::destroy($id);
        }

        return redirect()->route($this->rutaVista);
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una fundamentación para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $versiones = version_fundamentacion::where('id_fundamentacion', $id)->get();
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

            $this->modelo::whereIn($this->columnaIdFundamentacionPrimaria, $ids)->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar las fundamentaciones: ' . $e->getMessage());
        }
    }

    public function eliminarVersion(Request $request, $idVersion)
    {
        try {
            $version = version_fundamentacion::findOrFail($idVersion);
            $fundamentacionId = $version->id_fundamentacion;
            $versionNumero = $version->version_numero;

            $totalVersiones = version_fundamentacion::where('id_fundamentacion', $fundamentacionId)->count();

            if ($totalVersiones <= 1) {
                return redirect()->route($this->rutaVista, ['accion' => 'detalles', 'id' => $fundamentacionId])
                    ->with('error', 'No se puede eliminar la única versión de la fundamentación');
            }

            if (Storage::exists($version->ruta_documento)) {
                Storage::delete($version->ruta_documento);
            }

            $version->delete();
            $this->reordenarVersiones($fundamentacionId);

            return redirect()->route($this->rutaVista, ['accion' => 'detalles', 'id' => $fundamentacionId]);

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
            $fundamentaciones = $this->modelo::all();

            foreach ($fundamentaciones as $fundamentacion) {
                $versiones = version_fundamentacion::where('id_fundamentacion', $fundamentacion->id_fundamentacion)->get();
                foreach ($versiones as $version) {
                    if (!empty($version->ruta_documento) && Storage::exists($version->ruta_documento)) {
                        Storage::delete($version->ruta_documento);
                    }
                    $version->delete();
                }
                $folderPath = "{$this->storageFolder}/{$fundamentacion->id_fundamentacion}";
                if (Storage::exists($folderPath)) {
                    Storage::deleteDirectory($folderPath);
                }
            }

            $this->modelo::query()->delete();
            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al vaciar las fundamentaciones: ' . $e->getMessage());
        }
    }

    public function verDocumento($id)
    {
        try {
            $ultimaVersion = version_fundamentacion::where('id_fundamentacion', $id)
                ->orderBy('version_numero', 'desc')
                ->first();

            if (!$ultimaVersion || empty($ultimaVersion->ruta_documento)) {
                abort(404, 'No hay documento asociado a esta fundamentación');
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
            $version = version_fundamentacion::findOrFail($idVersion);

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

    public function aprobar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
        }

        $id = $request->id;
        $fundamentacion = $this->modelo::find($id);

        if ($fundamentacion) {
            if ($fundamentacion->aprobada) {
                return redirect()->back()->with('error', 'Esta fundamentación ya está aprobada');
            }

            if ($fundamentacion->desaprobada) {
                $this->modeloDesaprobada::where($this->columnaIdFundamentacion, $id)->delete();
            }

            $obj = new $this->modeloAprobada();
            $obj->{$this->columnaIdFundamentacion} = $id;
            $obj->save();
        }

        return redirect()->route($this->rutaVista);
    }

    public function desaprobar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
        }

        $id = $request->id;
        $fundamentacion = $this->modelo::find($id);

        if ($fundamentacion) {
            if ($fundamentacion->desaprobada) {
                return redirect()->back()->with('error', 'Esta fundamentación ya está desaprobada');
            }

            if ($fundamentacion->aprobada) {
                $this->modeloAprobada::where($this->columnaIdFundamentacion, $id)->delete();
            }

            $obj = new $this->modeloDesaprobada();
            $obj->{$this->columnaIdFundamentacion} = $id;
            $obj->save();
        }

        return redirect()->route($this->rutaVista);
    }

    public function revertir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFundamentacion . ',' . $this->columnaIdFundamentacionPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La fundamentación no existe o ya ha sido eliminada');
        }

        $id = $request->id;
        $fundamentacion = $this->modelo::find($id);

        if ($fundamentacion) {
            if ($fundamentacion->aprobada) {
                $this->modeloAprobada::where($this->columnaIdFundamentacion, $id)->delete();
            }
            if ($fundamentacion->desaprobada) {
                $this->modeloDesaprobada::where($this->columnaIdFundamentacion, $id)->delete();
            }
        }

        return redirect()->route($this->rutaVista);
    }

    public function exportarCsv()
    {
        $fundamentaciones = $this->modelo::with([
            'tesis.estudiante.carrera.facultad',
            'aprobada',
            'desaprobada',
        ])->get();

        $nombreArchivo = 'fundamentaciones_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($fundamentaciones) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Trabajo', 'Estudiante', 'Carrera', 'Facultad', 'Estado'], ';');

            foreach ($fundamentaciones as $f) {
                $trabajo = $f->tesis->Nombre_trabajo ?? '—';
                $estudiante = '—';
                $carrera = '—';
                $facultad = '—';

                if ($f->tesis && $f->tesis->estudiante) {
                    $estudiante = trim($f->tesis->estudiante->Nombre_estudiante . ' ' .
                        $f->tesis->estudiante->Apellido1 . ' ' .
                        $f->tesis->estudiante->Apellido2);

                    if ($f->tesis->estudiante->carrera) {
                        $carrera = $f->tesis->estudiante->carrera->Nombre_carrera;
                        if ($f->tesis->estudiante->carrera->facultad) {
                            $facultad = $f->tesis->estudiante->carrera->facultad->Nombre_facultad;
                        }
                    }
                }

                $estado = 'Pendiente';
                if ($f->aprobada) $estado = 'Aprobada';
                elseif ($f->desaprobada) $estado = 'Desaprobada';

                fputcsv($out, [$trabajo, $estudiante, $carrera, $facultad, $estado], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
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


        /**
     * Búsqueda de tesis para el datalist (AJAX).
     * Solo devuelve tesis SIN fundamentación,
     * más la tesis actual si estamos editando.
     */
    public function buscarTesis(Request $request)
    {
        $termino = trim($request->input('q', ''));
        $idActual = $request->input('id_actual'); // id de la tesis actual en modo edición

        try {
            $query = $this->modeloTesis::with(['estudiante'])
                ->where(function ($q) use ($idActual) {
                    // Tesis sin fundamentación
                    $q->whereDoesntHave('fundamentacion');
                    // O la tesis actual (si estamos editando)
                    if ($idActual) {
                        $q->orWhere('id', $idActual);
                    }
                });

            if ($termino !== '') {
                $query->where(function ($q) use ($termino) {
                    $q->where('Nombre_trabajo', 'LIKE', "%{$termino}%")
                      ->orWhereHas('estudiante', function ($q) use ($termino) {
                          $q->where('Nombre_estudiante', 'LIKE', "%{$termino}%")
                            ->orWhere('Apellido1', 'LIKE', "%{$termino}%")
                            ->orWhere('Apellido2', 'LIKE', "%{$termino}%")
                            ->orWhere('CI_estudiante', 'LIKE', "%{$termino}%");
                      });
                });
            }

            $tesis = $query->orderBy('Nombre_trabajo')
                ->limit(30)
                ->get();

            $resultados = $tesis->map(function ($t) {
                $est = $t->estudiante;
                $nombreEst = $est
                    ? trim($est->Nombre_estudiante . ' ' . $est->Apellido1)
                    : 'Sin estudiante';

                $label = $t->Nombre_trabajo . ' - ' . $nombreEst;

                return [
                    'id'         => $t->id,
                    'label'      => $label,
                    'nombre'     => $t->Nombre_trabajo,
                    'estudiante' => $nombreEst,
                ];
            });

            return response()->json($resultados);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar tesis: ' . $e->getMessage(),
            ], 500);
        }
    }
}