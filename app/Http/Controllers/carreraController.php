<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\Estudiante;
use App\Models\Modalidad;
use App\Models\Carrera_has_Modalidad;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class carreraController extends Controller
{
    protected $modelo = Carrera::class;
    protected $modeloFacultad = Facultad::class;
    protected $rutaVista = 'gestionarCarrera';
    protected $campoFacultad = 'idFacultad';
    protected $campoNombreCarrera = 'Nombre_carrera';
    protected $campoRelacionFacultad = 'id_facultad';

    protected $tablaCarrera;
    protected $tablaFacultad;
    protected $columnaIdCarrera;
    protected $columnaIdFacultad;
    protected $columnaNombreFacultad = 'Nombre_facultad';
    protected $columnaSiglasFacultad = 'Siglas';

    public function __construct()
    {
        $instanciaCarrera = new $this->modelo;
        $instanciaFacultad = new $this->modeloFacultad;

        $this->tablaCarrera = $instanciaCarrera->getTable();
        $this->tablaFacultad = $instanciaFacultad->getTable();

        $this->columnaIdCarrera = $instanciaCarrera->getKeyName();
        $this->columnaIdFacultad = $instanciaFacultad->getKeyName();
    }

    /**
     * Listado, formulario o detalles según query param "accion".
     *
     *  /gestionarCarrera                       → listado
     *  /gestionarCarrera?accion=crear          → formulario crear
     *  /gestionarCarrera?accion=editar&id=X    → formulario editar
     *  /gestionarCarrera?accion=detalles&id=X  → detalles
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return $this->mostrarAgregar();
        }
        if ($accion === 'editar' && $id) {
            return $this->mostrarEditar($id);
        }
        if ($accion === 'detalles' && $id) {
            return $this->mostrarDetalles($id);
        }

        // ---------- Listado ----------
        try {
            $facultad_id = $request->input('facultad_id');
            $carrera_nombre = $request->input('carrera_nombre');

            $query = $this->modelo::with(['facultad', 'modalidades', 'estudiantes'])
                ->orderBy($this->campoNombreCarrera);

            if ($facultad_id) {
                $query->where($this->campoRelacionFacultad, $facultad_id);
            }

            if ($carrera_nombre) {
                $query->where($this->campoNombreCarrera, 'like', '%' . $carrera_nombre . '%');
            }

            $carreras = $query->get();

            foreach ($carreras as $carrera) {
                $carrera->cantidad_estudiantes = $carrera->estudiantes->count();
            }

            $facultadesSelect = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();

            return view('gestionar.carrera.index', compact(
                'carreras', 'facultadesSelect', 'facultad_id', 'carrera_nombre'
            ));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar las carreras: ' . $e->getMessage());
        }
    }

    public function mostrarAgregar()
    {
        $facultades = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();
        $modalidades = Modalidad::orderBy('Nombre_modalidad')->get();

        return view('gestionar.carrera.formulario', compact('facultades', 'modalidades'));
    }

    public function mostrarEditar($id)
    {
        try {
            $carrera = $this->modelo::with(['facultad', 'modalidades'])->findOrFail($id);
            $facultades = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();
            $modalidades = Modalidad::orderBy('Nombre_modalidad')->get();

            return view('gestionar.carrera.formulario', compact('carrera', 'facultades', 'modalidades'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Carrera no encontrada');
        }
    }

    public function mostrarDetalles($id)
    {
        try {
            $carrera = $this->modelo::with([
                'facultad',
                'modalidades',
                'estudiantes' => function ($query) {
                    $query->orderBy('year_academico', 'asc')
                        ->orderBy('Apellido1')
                        ->orderBy('Apellido2');
                },
                'estudiantes.modalidad',
                'estudiantes.grupo',
            ])->findOrFail($id);

            $carrera->cantidad_estudiantes = $carrera->estudiantes->count();

            $carrera->estudiantes_por_ano = $carrera->estudiantes->groupBy('year_academico');
            $carrera->estudiantes_por_ano = $carrera->estudiantes_por_ano->sortKeys();

            $modalidades_carrera = Carrera_has_Modalidad::where('Carrera_idCarrera', $id)
                ->join('modalidades', 'carrera_modalidad.Modalidad_idModalidad', '=', 'modalidades.idModalidad')
                ->select('modalidades.*', 'carrera_modalidad.cantidad_years')
                ->get();

            return view('gestionar.carrera.detalles', compact('carrera', 'modalidades_carrera'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'facultad' => 'required',
            'nombre_carrera' => 'required|string|min:10|max:80',
            'modalidades' => 'array',
            'modalidades.*.id' => 'exists:modalidades,idModalidad',
            'modalidades.*.years' => 'nullable|integer|min:1|max:10',
        ], [
            'facultad.required' => 'La facultad es obligatoria',
            'nombre_carrera.required' => 'El nombre de la carrera es obligatorio',
            'nombre_carrera.min' => 'El nombre debe tener al menos 10 caracteres',
            'nombre_carrera.max' => 'El nombre no puede exceder 80 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        $facultad = $this->buscarFacultad($request->facultad);
        if (!$facultad) {
            return redirect($urlFormCrear)
                ->with('error', 'La facultad no existe')
                ->withInput();
        }

        if ($this->modelo::where($this->campoRelacionFacultad, $facultad->{$this->campoFacultad})
                ->where($this->campoNombreCarrera, $request->nombre_carrera)
                ->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe una carrera con ese nombre en esta facultad')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $carrera = new $this->modelo();
            $carrera->{$this->campoRelacionFacultad} = $facultad->{$this->campoFacultad};
            $carrera->{$this->campoNombreCarrera} = $request->nombre_carrera;
            $carrera->save();

            if ($request->has('modalidades')) {
                foreach ($request->modalidades as $modalidad) {
                    if (!empty($modalidad['id']) && !empty($modalidad['years'])) {
                        Carrera_has_Modalidad::create([
                            'Carrera_idCarrera' => $carrera->id,
                            'Modalidad_idModalidad' => $modalidad['id'],
                            'cantidad_years' => $modalidad['years'],
                        ]);
                    }
                }
            }

            DB::commit();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Carrera creada correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar la carrera: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, ['accion' => 'editar', 'id' => $request->id]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
            'facultad' => 'required',
            'nombre_carrera' => 'required|string|min:10|max:80',
            'modalidades' => 'array',
            'modalidades.*.id' => 'exists:modalidades,idModalidad',
            'modalidades.*.years' => 'nullable|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        $carrera = $this->modelo::find($request->id);
        if (!$carrera) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'No se encontró la carrera a modificar');
        }

        $facultad = $this->buscarFacultad($request->facultad);
        if (!$facultad) {
            return redirect($urlFormEditar)
                ->with('error', 'La facultad no existe')
                ->withInput();
        }

        if ($this->modelo::where($this->campoRelacionFacultad, $facultad->{$this->campoFacultad})
                ->where($this->campoNombreCarrera, $request->nombre_carrera)
                ->where($this->columnaIdCarrera, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otra carrera con ese nombre en esta facultad')
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $carrera->{$this->campoRelacionFacultad} = $facultad->{$this->campoFacultad};
            $carrera->{$this->campoNombreCarrera} = $request->nombre_carrera;
            $carrera->save();

            Carrera_has_Modalidad::where('Carrera_idCarrera', $carrera->id)->delete();

            if ($request->has('modalidades')) {
                foreach ($request->modalidades as $modalidad) {
                    if (!empty($modalidad['id']) && !empty($modalidad['years'])) {
                        Carrera_has_Modalidad::create([
                            'Carrera_idCarrera' => $carrera->id,
                            'Modalidad_idModalidad' => $modalidad['id'],
                            'cantidad_years' => $modalidad['years'],
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar la carrera: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'La carrera no existe o ya ha sido eliminada');
        }

        DB::beginTransaction();
        try {
            $id = $request->id;
            Carrera_has_Modalidad::where('Carrera_idCarrera', $id)->delete();
            $this->modelo::destroy($id);
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar la carrera: ' . $e->getMessage());
        }
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una carrera para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        DB::beginTransaction();
        try {
            Carrera_has_Modalidad::whereIn('Carrera_idCarrera', $ids)->delete();
            $this->modelo::whereIn($this->columnaIdCarrera, $ids)->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar las carreras: ' . $e->getMessage());
        }
    }

    public function exportarCsv()
    {
        $carreras = $this->modelo::with(['facultad', 'modalidades', 'estudiantes'])
            ->orderBy($this->campoNombreCarrera)
            ->get();

        $nombreArchivo = 'carreras_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($carreras) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Carrera', 'Facultad', 'Siglas', 'Modalidades', 'Estudiantes'], ';');

            foreach ($carreras as $c) {
                $modalidades = $c->modalidades->map(function ($m) {
                    $years = $m->pivot->cantidad_years ?? '?';
                    return "{$m->Nombre_modalidad} ({$years} años)";
                })->implode(', ');

                fputcsv($out, [
                    $c->{$this->campoNombreCarrera},
                    $c->facultad->Nombre_facultad ?? '—',
                    $c->facultad->Siglas ?? '—',
                    $modalidades,
                    $c->estudiantes->count(),
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function buscarFacultad($dato)
    {
        try {
            if (is_numeric($dato)) {
                return $this->modeloFacultad::where($this->columnaIdFacultad, $dato)->first();
            } else {
                return $this->modeloFacultad::whereRaw(
                    'LOWER(' . $this->columnaSiglasFacultad . ') = LOWER(?)',
                    [$dato]
                )->first();
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}