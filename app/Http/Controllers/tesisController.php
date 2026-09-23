<?php

namespace App\Http\Controllers;

use App\Models\Tesis;
use App\Models\Estudiante;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\tutor_estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TesisController extends Controller
{
    protected $modelo = Tesis::class;
    protected $modeloEstudiante = Estudiante::class;
    protected $modeloCarrera = Carrera::class;
    protected $modeloFacultad = Facultad::class;

    protected $rutaVista = 'gestionarTesis';

    protected $tablaTesis;
    protected $tablaEstudiante;
    protected $tablaCarrera;
    protected $tablaFacultad;

    protected $columnaIdTesis;
    protected $columnaIdEstudiante;
    protected $columnaIdCarrera;
    protected $columnaIdFacultad;

    protected $columnaEstudiante = 'id_estudiante';
    protected $columnaNombre = 'Nombre_trabajo';

    public function __construct()
    {
        $instanciaTesis = new $this->modelo;
        $instanciaEstudiante = new $this->modeloEstudiante;
        $instanciaCarrera = new $this->modeloCarrera;
        $instanciaFacultad = new $this->modeloFacultad;

        $this->tablaTesis = $instanciaTesis->getTable();
        $this->tablaEstudiante = $instanciaEstudiante->getTable();
        $this->tablaCarrera = $instanciaCarrera->getTable();
        $this->tablaFacultad = $instanciaFacultad->getTable();

        $this->columnaIdTesis = $instanciaTesis->getKeyName();
        $this->columnaIdEstudiante = $instanciaEstudiante->getKeyName();
        $this->columnaIdCarrera = $instanciaCarrera->getKeyName();
        $this->columnaIdFacultad = $instanciaFacultad->getKeyName();
    }

    /**
     * Listado, formulario o detalles según query param.
     *
     *  /gestionarTesis                       → listado
     *  /gestionarTesis?accion=crear          → formulario crear
     *  /gestionarTesis?accion=editar&id=X    → formulario editar
     *  /gestionarTesis?accion=detalles&id=X  → detalles
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return $this->crearTesis();
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
            $porPagina = $request->input('por_pagina', 10);

            $query = $this->modelo::with([
                'estudiante' => fn($q) => $q->with(['carrera.facultad']),
            ]);

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where($this->columnaNombre, 'LIKE', "%{$buscar}%")
                        ->orWhereHas('estudiante', function ($q) use ($buscar) {
                            $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                                ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                        });
                });
            }

            if ($filtroFacultad) {
                $query->whereHas('estudiante.carrera.facultad', fn($q) => $q->where('idFacultad', $filtroFacultad));
            }

            if ($filtroCarrera) {
                $query->whereHas('estudiante.carrera', fn($q) => $q->where('id', $filtroCarrera));
            }

            $trabajos = $query->paginate($porPagina)->appends($request->query());

            $carreras = $this->modeloCarrera::all();
            $facultades = $this->modeloFacultad::all();

            return view('gestionar.tesis.index', compact('trabajos', 'carreras', 'facultades'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar las tesis: ' . $e->getMessage());
        }
    }

    public function crearTesis()
    {
        try {
            $estudiantes = $this->modeloEstudiante::whereDoesntHave('tesis')->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No hay estudiantes disponibles. Todos los estudiantes ya tienen una tesis asignada.');
            }

            return view('gestionar.tesis.formulario', compact('estudiantes'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'id_estudiante' => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
            'nombre_tesis' => 'required|string|min:10|max:500',
        ], [
            'id_estudiante.required' => 'El estudiante es obligatorio',
            'id_estudiante.exists' => 'El estudiante seleccionado no existe',
            'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
            'nombre_tesis.string' => 'El nombre de la tesis debe ser texto',
            'nombre_tesis.min' => 'El nombre de la tesis debe tener al menos 10 caracteres',
            'nombre_tesis.max' => 'El nombre de la tesis no puede exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaEstudiante, $request->id_estudiante)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Este estudiante ya tiene una tesis asignada')
                ->withInput();
        }

        try {
            $trabajo = new $this->modelo();
            $trabajo->{$this->columnaEstudiante} = $request->id_estudiante;
            $trabajo->{$this->columnaNombre} = $request->nombre_tesis;
            $trabajo->save();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Tesis creada correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar la tesis: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function ver($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
            ]);

            if ($validator->fails()) {
                return redirect()->route($this->rutaVista)->with('error', 'La tesis no existe');
            }

            $tesis = $this->modelo::with([
                'estudiante' => fn($q) => $q->with(['carrera.facultad', 'grupo', 'modalidad']),
                'fundamentacion' => function ($query) {
                    $query->with([
                        'aprobada',
                        'desaprobada',
                        'recomendacion',
                        'profesores.departamento',
                        'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
                    ]);
                },
                'cortes' => function ($query) {
                    $query->with([
                        'aprobado',
                        'desaprobado',
                        'versiones' => fn($q) => $q->orderBy('version_numero', 'desc'),
                    ])->orderBy('Numero_corte', 'asc');
                },
            ])->findOrFail($id);

            $tutor = null;
            if ($tesis->estudiante) {
                $tutor = tutor_estudiante::with('profesor')
                    ->where('id_estudiante', $tesis->estudiante->id)
                    ->first();
            }

            return view('gestionar.tesis.detalles', compact('tesis', 'tutor'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los datos de la tesis: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
            ]);

            if ($validator->fails()) {
                return redirect()->route($this->rutaVista)->with('error', 'La tesis no existe');
            }

            $tesis = $this->modelo::with(['estudiante'])->findOrFail($id);

            $estudiantes = $this->modeloEstudiante::whereDoesntHave('tesis')
                ->orWhere('id', $tesis->id_estudiante)
                ->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No hay estudiantes disponibles.');
            }

            return view('gestionar.tesis.formulario', compact('tesis', 'estudiantes'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
            'id_estudiante' => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
            'nombre_tesis' => 'required|string|min:10|max:300',
        ], [
            'id_estudiante.required' => 'El estudiante es obligatorio',
            'id_estudiante.exists' => 'El estudiante seleccionado no existe',
            'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
            'nombre_tesis.min' => 'El nombre de la tesis debe tener al menos 10 caracteres',
            'nombre_tesis.max' => 'El nombre de la tesis no puede exceder los 300 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaEstudiante, $request->id_estudiante)
                ->where($this->columnaIdTesis, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Este estudiante ya tiene una tesis asignada')
                ->withInput();
        }

        try {
            $trabajo = $this->modelo::find($request->id);
            if (!$trabajo) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No se encontró la tesis a modificar');
            }

            $trabajo->{$this->columnaEstudiante} = $request->id_estudiante;
            $trabajo->{$this->columnaNombre} = $request->nombre_tesis;
            $trabajo->save();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar la tesis: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La tesis no existe o ya ha sido eliminada');
        }

        $this->modelo::destroy($request->id);
        return redirect()->route($this->rutaVista);
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una tesis para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        $this->modelo::whereIn($this->columnaIdTesis, $ids)->delete();
        return redirect()->route($this->rutaVista);
    }

    public function exportarCsv()
    {
        $trabajos = $this->modelo::with(['estudiante.carrera.facultad'])
            ->orderBy($this->columnaNombre)
            ->get();

        $nombreArchivo = 'tesis_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($trabajos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Estudiante', 'Nombre del Trabajo', 'Carrera', 'Facultad'], ';');

            foreach ($trabajos as $t) {
                $estudiante = 'Sin estudiante';
                $carrera = 'No asignada';
                $facultad = 'No asignada';

                if ($t->estudiante) {
                    $estudiante = trim($t->estudiante->Nombre_estudiante . ' ' .
                        $t->estudiante->Apellido1 . ' ' .
                        $t->estudiante->Apellido2);

                    if ($t->estudiante->carrera) {
                        $carrera = $t->estudiante->carrera->Nombre_carrera;
                        if ($t->estudiante->carrera->facultad) {
                            $facultad = $t->estudiante->carrera->facultad->Siglas;
                        }
                    }
                }

                fputcsv($out, [$estudiante, $t->{$this->columnaNombre}, $carrera, $facultad], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return redirect()->route($this->rutaVista);
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al vaciar las tesis: ' . $e->getMessage());
        }
    }
}