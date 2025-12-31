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

    public function mostrar(Request $request)
    {
        try {
            $facultad_id = $request->input('facultad_id');
            $carrera_nombre = $request->input('carrera_nombre');
            
            // Consulta base con eager loading
            $query = $this->modelo::with(['facultad', 'modalidades', 'estudiantes'])
                ->orderBy($this->campoNombreCarrera);
            
            // Aplicar filtros
            if ($facultad_id) {
                $query->where($this->campoRelacionFacultad, $facultad_id);
            }
            
            if ($carrera_nombre) {
                $query->where($this->campoNombreCarrera, 'like', '%' . $carrera_nombre . '%');
            }
            
            $carreras = $query->get();
            
            // Contar estudiantes por carrera
            foreach ($carreras as $carrera) {
                $carrera->cantidad_estudiantes = $carrera->estudiantes->count();
            }
            
            // Obtener facultades para el filtro
            $facultadesSelect = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();

            return view('gestionar.gestionarCarrera.gestionarCarrera', compact('carreras', 'facultadesSelect', 'facultad_id', 'carrera_nombre'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las carreras: ' . $e->getMessage());
        }
    }

    public function mostrarAgregar()
    {
        $facultades = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();
        $modalidades = Modalidad::orderBy('Nombre_modalidad')->get();
        
        return view('gestionar.gestionarCarrera.agregarCarrera', compact('facultades', 'modalidades'));
    }

    public function mostrarEditar($id)
    {
        try {
            $carrera = $this->modelo::with(['facultad', 'modalidades'])->findOrFail($id);
            $facultades = $this->modeloFacultad::orderBy($this->columnaNombreFacultad)->get();
            $modalidades = Modalidad::orderBy('Nombre_modalidad')->get();
            
            return view('gestionar.gestionarCarrera.editarCarrera', compact('carrera', 'facultades', 'modalidades'));
        } catch (\Exception $e) {
            return redirect()->route('gestionarCarrera')
                ->with('error', 'Carrera no encontrada');
        }
    }

  public function mostrarDetalles($id)
{
    try {
        $carrera = $this->modelo::with([
            'facultad',
            'modalidades',
            'estudiantes' => function($query) {
                $query->orderBy('year_academico', 'asc') 
                    ->orderBy('Apellido1')
                    ->orderBy('Apellido2');
            },
            'estudiantes.modalidad',
            'estudiantes.grupo'
        ])->findOrFail($id);
        
        // Obtener estadísticas
        $carrera->cantidad_estudiantes = $carrera->estudiantes->count();
        
    
        $carrera->estudiantes_por_ano = $carrera->estudiantes->groupBy('year_academico');
        
        $carrera->estudiantes_por_ano = $carrera->estudiantes_por_ano->sortKeys();
        
        // Obtener modalidades asociadas con su duración
        $modalidades_carrera = Carrera_has_Modalidad::where('Carrera_idCarrera', $id)
            ->join('modalidades', 'carrera_modalidad.Modalidad_idModalidad', '=', 'modalidades.idModalidad')
            ->select('modalidades.*', 'carrera_modalidad.cantidad_years')
            ->get();
        
        return view('gestionar.gestionarCarrera.verCarrera', compact('carrera', 'modalidades_carrera'));
    } catch (\Exception $e) {
        return redirect()->route('gestionarCarrera')
            ->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
    }
}

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'facultad' => 'required',
                'nombre_carrera' => 'required|string|min:10|max:80',
                'modalidades' => 'array',
                'modalidades.*.id' => 'exists:modalidades,idModalidad',
                'modalidades.*.years' => 'nullable|integer|min:1|max:10'
            ], [
                'facultad.required' => 'La facultad es obligatoria',
                'nombre_carrera.required' => 'El nombre de la carrera es obligatorio',
                'nombre_carrera.min' => 'El nombre debe tener al menos 10 caracteres',
                'nombre_carrera.max' => 'El nombre no puede exceder 80 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe una carrera con el mismo nombre en la misma facultad
            $facultad = $this->buscarFacultad($request->facultad);
            
            if (!$facultad) {
                return redirect()->back()
                    ->with('error', 'La facultad no existe')
                    ->withInput();
            }

            $existente = $this->modelo::where($this->campoRelacionFacultad, $facultad->{$this->campoFacultad})
                ->where($this->campoNombreCarrera, $request->nombre_carrera)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una carrera con ese nombre en esta facultad')
                    ->withInput();
            }

            DB::beginTransaction();

            // Crear la carrera
            $carrera = new $this->modelo();
            $carrera->{$this->campoRelacionFacultad} = $facultad->{$this->campoFacultad};
            $carrera->{$this->campoNombreCarrera} = $request->nombre_carrera;
            $carrera->save();

            // Asociar modalidades si existen
            if ($request->has('modalidades')) {
                foreach ($request->modalidades as $modalidad) {
                    if (!empty($modalidad['id']) && !empty($modalidad['years'])) {
                        Carrera_has_Modalidad::create([
                            'Carrera_idCarrera' => $carrera->id,
                            'Modalidad_idModalidad' => $modalidad['id'],
                            'cantidad_years' => $modalidad['years']
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('gestionarCarrera')
                ->with('success', 'Carrera agregada correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al agregar la carrera: ' . $e->getMessage())
                ->withInput();
        }
    }

public function eliminar(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'La carrera no existe o ya ha sido eliminada');
        }

        $id = $request['id'];
        
        
        DB::beginTransaction();
        
        Carrera_has_Modalidad::where('Carrera_idCarrera', $id)->delete();
        $this->modelo::destroy($id);
        
        DB::commit();


        return redirect()->route('gestionarCarrera')
            ->with('success', 'Carrera eliminada correctamente');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error al eliminar la carrera: ' . $e->getMessage());
    }
}

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
                'facultad' => 'required',
                'nombre_carrera' => 'required|string|min:10|max:80',
                'modalidades' => 'array',
                'modalidades.*.id' => 'exists:modalidades,idModalidad',
                'modalidades.*.years' => 'nullable|integer|min:1|max:10'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $carrera = $this->modelo::find($request->id);
            
            if ($carrera) {
                $facultad = $this->buscarFacultad($request->facultad);
                
                if (!$facultad) {
                    return redirect()->back()
                        ->with('error', 'La facultad no existe')
                        ->withInput();
                }

                // Verificar si ya existe otra carrera con el mismo nombre en la misma facultad
                $existente = $this->modelo::where($this->campoRelacionFacultad, $facultad->{$this->campoFacultad})
                    ->where($this->campoNombreCarrera, $request->nombre_carrera)
                    ->where($this->columnaIdCarrera, '!=', $request->id)
                    ->first();

                if ($existente) {
                    return redirect()->back()
                        ->with('error', 'Ya existe otra carrera con ese nombre en esta facultad')
                        ->withInput();
                }

                DB::beginTransaction();

                // Actualizar la carrera
                $carrera->{$this->campoRelacionFacultad} = $facultad->{$this->campoFacultad};
                $carrera->{$this->campoNombreCarrera} = $request->nombre_carrera;
                $carrera->save();

                // Actualizar modalidades
                Carrera_has_Modalidad::where('Carrera_idCarrera', $carrera->id)->delete();
                
                if ($request->has('modalidades')) {
                    foreach ($request->modalidades as $modalidad) {
                        if (!empty($modalidad['id']) && !empty($modalidad['years'])) {
                            Carrera_has_Modalidad::create([
                                'Carrera_idCarrera' => $carrera->id,
                                'Modalidad_idModalidad' => $modalidad['id'],
                                'cantidad_years' => $modalidad['years']
                            ]);
                        }
                    }
                }

                DB::commit();

                return redirect()->route('gestionarCarrera')
                    ->with('success', 'Carrera modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la carrera a modificar');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al modificar la carrera: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function buscarFacultad($dato)
    {
        try {
            if (is_numeric($dato)) {
                return $this->modeloFacultad::where($this->columnaIdFacultad, $dato)->first();
            } else {
                return $this->modeloFacultad::whereRaw('LOWER(' . $this->columnaSiglasFacultad . ') = LOWER(?)', [$dato])->first();
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}