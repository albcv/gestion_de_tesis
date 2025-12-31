<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tutor_estudiante;
use App\Models\Profesor; 
use App\Models\Estudiante;
use Illuminate\Support\Facades\Validator;

class tutorEstudianteController extends Controller
{
    protected $modelo = tutor_estudiante::class;
    protected $modeloProfesor = Profesor::class; 
    protected $modeloEstudiante = Estudiante::class;
    protected $rutaVista = 'gestionarTutoresEstudiantes';
    
    protected $tablaTutorEstudiante;
    protected $tablaProfesor;
    protected $tablaEstudiante;
    protected $columnaIdTutorEstudiante;
    protected $columnaIdProfesor;
    protected $columnaIdEstudiante;
    protected $columnaProfesor = 'id_profesor'; 
    protected $columnaEstudiante = 'id_estudiante';

    // Constante para límite de tutores por estudiante
    const MAX_TUTORES_POR_ESTUDIANTE = 2;

    public function __construct()
    {
        $instanciaTutorEstudiante = new $this->modelo;
        $instanciaProfesor = new $this->modeloProfesor;
        $instanciaEstudiante = new $this->modeloEstudiante;
        
        $this->tablaTutorEstudiante = $instanciaTutorEstudiante->getTable();
        $this->tablaProfesor = $instanciaProfesor->getTable();
        $this->tablaEstudiante = $instanciaEstudiante->getTable();
        
        $this->columnaIdTutorEstudiante = $instanciaTutorEstudiante->getKeyName();
        $this->columnaIdProfesor = $instanciaProfesor->getKeyName();
        $this->columnaIdEstudiante = $instanciaEstudiante->getKeyName();
    }

    // Método para mostrar la vista de asignar tutor
    public function mostrarAsignarTutor($id_estudiante)
    {
        try {
            $estudiante = $this->modeloEstudiante::with(['carrera.facultad', 'tutores.profesor'])
                ->findOrFail($id_estudiante);
            
            $profesores = $this->modeloProfesor::with(['departamento', 'tutorados'])
                ->get();
            
            $tutoresActuales = $estudiante->tutores;
            $cantidadTutores = $tutoresActuales->count();
            
            return view('gestionar.gestionarTutorEstudiante.asignarTutor', 
                compact('estudiante', 'profesores', 'tutoresActuales', 'cantidadTutores'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                'Error al cargar el formulario de asignación de tutor: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_profesor' => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesor,
                'id_estudiante' => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
            ], [
                'id_profesor.required' => 'El profesor es obligatorio',
                'id_profesor.exists' => 'El profesor seleccionado no existe',
                'id_estudiante.required' => 'El estudiante es obligatorio',
                'id_estudiante.exists' => 'El estudiante seleccionado no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Contar tutores actuales del estudiante
            $tutoresActuales = $this->modelo::where($this->columnaEstudiante, $request->id_estudiante)
                ->count();

            // Verificar si el estudiante ya tiene el máximo de tutores
            if ($tutoresActuales >= self::MAX_TUTORES_POR_ESTUDIANTE) {
                return redirect()->back()
                    ->with('error', 'El estudiante ya tiene el máximo de ' . self::MAX_TUTORES_POR_ESTUDIANTE . ' tutores asignados.')
                    ->withInput();
            }

            // Verificar si ya existe la combinación profesor-estudiante
            $existente = $this->modelo::where($this->columnaProfesor, $request->id_profesor)
                ->where($this->columnaEstudiante, $request->id_estudiante)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Este profesor ya está asignado como tutor de este estudiante.')
                    ->withInput();
            }

            $obj = new $this->modelo();
            $obj->{$this->columnaProfesor} = $request->id_profesor;
            $obj->{$this->columnaEstudiante} = $request->id_estudiante;
            $obj->save();

            // Redirigir a la vista del estudiante
            $estudiante = $this->modeloEstudiante::find($request->id_estudiante);
            return redirect()->route('verUsuario', $estudiante->id_usuario)
                ->with('success', 'Tutor asignado correctamente al estudiante');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al asignar el tutor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaTutorEstudiante . ',' . $this->columnaIdTutorEstudiante,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La relación profesor-estudiante no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $relacion = $this->modelo::find($id);
            $idEstudiante = $relacion->id_estudiante;
            $estudiante = $this->modeloEstudiante::find($idEstudiante);
            
            $this->modelo::destroy($id);

            return redirect()->route('verUsuario', $estudiante->id_usuario)
                ->with('success', 'Tutor desvinculado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la relación profesor-estudiante: ' . $e->getMessage());
        }
    }
}