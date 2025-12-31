<?php

namespace App\Http\Controllers;

use App\Models\Tesis;
use App\Models\Estudiante;
use App\Models\Carrera;
use App\Models\Facultad;
use App\Models\tutor_estudiante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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

    public function mostrar(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $buscar = $request->input('buscar');
            $filtroFacultad = $request->input('filtro_facultad');
            $filtroCarrera = $request->input('filtro_carrera');
            $porPagina = $request->input('por_pagina', 10);
            
            // Construir la consulta con relaciones
            $query = $this->modelo::with([
                'estudiante' => function($query) {
                    $query->with(['carrera.facultad']);
                }
            ]);
            
            // Aplicar búsqueda si existe
            if ($buscar) {
                $query->where(function($q) use ($buscar) {
                    $q->where($this->columnaNombre, 'LIKE', "%{$buscar}%")
                      ->orWhereHas('estudiante', function($q) use ($buscar) {
                          $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                            ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                      });
                });
            }
            
            // Aplicar filtro por facultad
            if ($filtroFacultad) {
                $query->whereHas('estudiante.carrera.facultad', function($q) use ($filtroFacultad) {
                    $q->where('idFacultad', $filtroFacultad);
                });
            }
            
            // Aplicar filtro por carrera
            if ($filtroCarrera) {
                $query->whereHas('estudiante.carrera', function($q) use ($filtroCarrera) {
                    $q->where('id', $filtroCarrera);
                });
            }
            
            // Obtener los registros con paginación
            $trabajos = $query->paginate($porPagina);
            
            // Obtener datos adicionales para la vista
            $estudiantes = $this->modeloEstudiante::all();
            $carreras = $this->modeloCarrera::all();
            $facultades = $this->modeloFacultad::all();
            
            return view('gestionar.gestionarTesis.gestionarTesis', compact(
                'trabajos', 
                'estudiantes', 
                'carreras',
                'facultades'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar las tesis: ' . $e->getMessage());
        }
    }

    public function crearTesis()
    {
        try {
            // Filtrar estudiantes sin tesis asignada
            $estudiantes = $this->modeloEstudiante::whereDoesntHave('tesis')->get();
            
            // Verificar si hay estudiantes disponibles
            if ($estudiantes->isEmpty()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No hay estudiantes disponibles. Todos los estudiantes ya tienen una tesis asignada.');
            }
            
            return view('gestionar.gestionarTesis.crearTesis', compact('estudiantes'));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_estudiante' => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
                'nombre_tesis' => 'required|string|min:10|max:300',
            ], [
                'id_estudiante.required' => 'El estudiante es obligatorio',
                'id_estudiante.exists' => 'El estudiante seleccionado no existe',
                'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
                'nombre_tesis.string' => 'El nombre de la tesis debe ser texto',
                'nombre_tesis.min' => 'El nombre de la tesis debe tener al menos 10 caracteres',
                'nombre_tesis.max' => 'El nombre de la tesis no puede exceder los 300 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si el estudiante ya tiene una tesis asignada
            $existente = $this->modelo::where($this->columnaEstudiante, $request->id_estudiante)->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Este estudiante ya tiene una tesis asignada')
                    ->withInput();
            }

            $trabajo = new $this->modelo();
            $trabajo->{$this->columnaEstudiante} = $request->id_estudiante;
            $trabajo->{$this->columnaNombre} = $request->nombre_tesis;
            $trabajo->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'Tesis agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la tesis: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function ver($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis
                ],
            ]);
            
            if ($validator->fails()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'La tesis no existe');
            }
            
            // Cargar la tesis con todas las relaciones necesarias, incluyendo versiones de fundamentación y cortes
            $tesis = $this->modelo::with([
                'estudiante' => function($query) {
                    $query->with(['carrera.facultad', 'grupo', 'modalidad']);
                },
                'fundamentacion' => function($query) {
                    $query->with([
                        'aprobada', 
                        'desaprobada', 
                        'recomendacion',
                        'profesores.departamento',
                        'versiones' => function($q) {
                            $q->orderBy('version_numero', 'desc');
                        }
                    ]);
                },
                'cortes' => function($query) {
                    $query->with([
                        'aprobado', 
                        'desaprobado',
                        'versiones' => function($q) {
                            $q->orderBy('version_numero', 'desc');
                        }
                    ])->orderBy('Numero_corte', 'asc');
                }
            ])->findOrFail($id);
            
            // Obtener el tutor del estudiante
            $tutor = null;
            if ($tesis->estudiante) {
                $tutor = tutor_estudiante::with('profesor')
                    ->where('id_estudiante', $tesis->estudiante->id)
                    ->first();
            }
            
            return view('gestionar.gestionarTesis.verTesis', compact('tesis', 'tutor'));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar los datos de la tesis: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis
                ],
            ]);
            
            if ($validator->fails()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'La tesis no existe');
            }
            
            $tesis = $this->modelo::with(['estudiante'])->findOrFail($id);

            $estudiantes = $this->modeloEstudiante::whereDoesntHave('tesis')
                ->orWhere('id', $tesis->id_estudiante) 
                ->get();

            if ($estudiantes->isEmpty()) {
                return redirect()->route($this->rutaVista)
                    ->with('error', 'No hay estudiantes disponibles.');
            }
            
            return view('gestionar.gestionarTesis.editarTesis', compact('tesis', 'estudiantes'));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
                'id_estudiante' => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
                'nombre_tesis' => 'required|string|min:10|max:300',
            ], [
                'id_estudiante.required' => 'El estudiante es obligatorio',
                'id_estudiante.exists' => 'El estudiante seleccionado no existe',
                'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
                'nombre_tesis.string' => 'El nombre de la tesis debe ser texto',
                'nombre_tesis.min' => 'El nombre de la tesis debe tener al menos 10 caracteres',
                'nombre_tesis.max' => 'El nombre de la tesis no puede exceder los 300 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si otro estudiante ya tiene esa tesis (excluyendo el actual)
            $existente = $this->modelo::where($this->columnaEstudiante, $request->id_estudiante)
                ->where($this->columnaIdTesis, '!=', $request->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Este estudiante ya tiene una tesis asignada')
                    ->withInput();
            }

            $trabajo = $this->modelo::find($request->id);
            if ($trabajo) {
                $trabajo->{$this->columnaEstudiante} = $request->id_estudiante;
                $trabajo->{$this->columnaNombre} = $request->nombre_tesis;
                $trabajo->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Tesis modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la tesis a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la tesis: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaTesis . ',' . $this->columnaIdTesis,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La tesis no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'Tesis eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la tesis: ' . $e->getMessage());
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las tesis han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las tesis: ' . $e->getMessage()
            ]);
        }
    }
}