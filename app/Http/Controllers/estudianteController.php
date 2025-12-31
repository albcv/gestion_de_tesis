<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
use App\Models\Carrera;
use App\Models\Estudiante;
use App\Models\Facultad;
use App\Models\grupos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstudianteController extends Controller
{
    protected $modelo = Estudiante::class;
    protected $modeloGrupo = grupos::class;
    protected $modeloModalidad = Modalidad::class;
    protected $modeloCarrera = Carrera::class;
    protected $modeloFacultad = Facultad::class;
    protected $rutaVista = 'gestionarEstudiante';
    
    protected $columnaGrupo = 'id_grupo';
    protected $columnaModalidad = 'id_modalidad';
    protected $columnaCarrera = 'id_carrera';
    protected $columnaCI = 'CI_estudiante';
    protected $columnaNumero = 'número';
    protected $columnaSexo = 'sexo';
    protected $columnaNombre = 'Nombre_estudiante';
    protected $columnaApellido1 = 'Apellido1';
    protected $columnaApellido2 = 'Apellido2';
    protected $columnaFechaIngreso = 'Fecha_ingreso';
    protected $columnaYearAcademico = 'year_academico';
    protected $columnaUsuario = 'id_usuario';

    public function mostrar()
    {
        $estudiantes = $this->modelo::with(['grupo', 'modalidad', 'carrera'])->get();
        $grupos = $this->modeloGrupo::all();
        $modalidades = $this->modeloModalidad::all();
        $carreras = $this->modeloCarrera::all();

        return view('gestionar.gestionarEstudiante', compact('estudiantes', 'grupos', 'modalidades', 'carreras'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'id_grupo' => 'required|exists:grupos,id',
            'id_modalidad' => 'required|exists:modalidades,idModalidad',
            'id_carrera' => 'required|exists:carreras,id',
            'número' => 'required|integer',
            'sexo' => 'required|in:Masculino,Femenino',
            'ci' => 'required|unique:estudiantes,CI_estudiante',
            'nombre_estudiante' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'año_académico' => 'required|integer|min:1|max:6',
            'id_usuario' => 'required|exists:users,id'
        ]);

        $estudiante = new $this->modelo();
        $estudiante->{$this->columnaGrupo} = $request->id_grupo;
        $estudiante->{$this->columnaModalidad} = $request->id_modalidad;
        $estudiante->{$this->columnaCarrera} = $request->id_carrera;
        $estudiante->{$this->columnaCI} = $request->ci;
        $estudiante->{$this->columnaNumero} = $request->número;
        $estudiante->{$this->columnaSexo} = $request->sexo;
        $estudiante->{$this->columnaNombre} = $request->nombre_estudiante;
        $estudiante->{$this->columnaApellido1} = $request->apellido1;
        $estudiante->{$this->columnaApellido2} = $request->apellido2;
        $estudiante->{$this->columnaFechaIngreso} = $request->fecha_ingreso;
        $estudiante->{$this->columnaYearAcademico} = $request->año_académico;
        $estudiante->{$this->columnaUsuario} = $request->id_usuario;
        
        $estudiante->save();

        return redirect(route($this->rutaVista))->with('success', 'Estudiante agregado correctamente');
    }

    public function eliminar(Request $request)
    {
        $id = $request['id'];
        $this->modelo::destroy($id);
        return redirect(route($this->rutaVista))->with('success', 'Estudiante eliminado correctamente');
    }

    public function modificar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:estudiantes,id',
            'id_grupo' => 'required|exists:grupos,id',
            'id_modalidad' => 'required|exists:modalidades,idModalidad',
            'id_carrera' => 'required|exists:carreras,id',
            'número' => 'required|integer',
            'sexo' => 'required|in:Masculino,Femenino',
            'ci' => 'required|unique:estudiantes,CI_estudiante,' . $request->id,
            'nombre_estudiante' => 'required|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'required|string|max:255',
            'fecha_ingreso' => 'required|date',
            'año_académico' => 'required|integer|min:1|max:6',
            'id_usuario' => 'required|exists:users,id'
        ]);

        $estudiante = $this->modelo::find($request->id);
        
        if ($estudiante) {
            $estudiante->{$this->columnaGrupo} = $request->id_grupo;
            $estudiante->{$this->columnaModalidad} = $request->id_modalidad;
            $estudiante->{$this->columnaCarrera} = $request->id_carrera;
            $estudiante->{$this->columnaCI} = $request->ci;
            $estudiante->{$this->columnaNumero} = $request->número;
            $estudiante->{$this->columnaSexo} = $request->sexo;
            $estudiante->{$this->columnaNombre} = $request->nombre_estudiante;
            $estudiante->{$this->columnaApellido1} = $request->apellido1;
            $estudiante->{$this->columnaApellido2} = $request->apellido2;
            $estudiante->{$this->columnaFechaIngreso} = $request->fecha_ingreso;
            $estudiante->{$this->columnaYearAcademico} = $request->año_académico;
            $estudiante->{$this->columnaUsuario} = $request->id_usuario;
            
            $estudiante->save();

            return redirect(route($this->rutaVista))->with('success', 'Estudiante actualizado correctamente');
        }

        return redirect()->back()->withErrors([
            'error' => 'No se pudo encontrar el estudiante a modificar'
        ]);
    }

    public function estudiantesAtrasadosFundamentación(Request $request)
    {
        $carreraParam = $request->input('carrera');
        $carreras = $this->modeloCarrera::all();

        $estudiantes = null;

        if ($carreraParam) {
            $estudiantes = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
                ->where('id_carrera', $carreraParam)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('tesis')
                        ->join('fundamentaciones', 'tesis.id', '=', 'fundamentaciones.id_tesis')
                        ->whereRaw('tesis.id_estudiante = estudiantes.id');
                })
                ->get();
        }

        return view('consultas.estudiantes.estudiantesAtrasadosFundamentación', compact('estudiantes', 'carreras', 'carreraParam'));
    }

   public function estudiantes_sin_tutor(Request $request)
{
    $carreraParam = $request->input('carrera');
    $yearParam = $request->input('year_academico');
    
    $carreras = $this->modeloCarrera::all();
    $estudiantes = null;
    $carreraSeleccionada = null;

    if ($carreraParam) {
        // Obtener la carrera seleccionada para mostrar en la vista
        $carreraSeleccionada = $this->modeloCarrera::find($carreraParam);
        
        // Iniciar la consulta base
        $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->where('id_carrera', $carreraParam)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tutor_estudiante') 
                      ->whereRaw('tutor_estudiante.id_estudiante = estudiantes.id');
            });

        // Aplicar filtro por año académico si se proporciona
        if ($yearParam && $yearParam !== '') {
            $query->where('year_academico', $yearParam);
        }

        // Ordenar por año académico descendente y luego por apellidos
        $query->orderBy('year_academico', 'desc')
              ->orderBy('Apellido1')
              ->orderBy('Apellido2')
              ->orderBy('Nombre_estudiante');

        $estudiantes = $query->get();
    }

    // Pasar también el año seleccionado y la carrera seleccionada a la vista
    return view('consultas.estudiantes.estudiantes_sin_tutor', compact(
        'estudiantes', 
        'carreras', 
        'carreraParam',
        'yearParam',
        'carreraSeleccionada'
    ));
}

    public function buscarEstudiante(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|size:11'
        ], [
            'ci.size' => 'El CI debe tener exactamente 11 caracteres.'
        ]);

        $estudiante = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->where($this->columnaCI, $request->ci)
            ->first();

        if (!$estudiante) {
            return redirect()->back()->withErrors([
                'ci' => 'No se encontró ningún estudiante con ese CI'
            ])->withInput();
        }

        return view('consultas.estudiantes.buscarEstudiante', compact('estudiante'));
    }

    public function estudiantesFacultad(Request $request)
    {
        $facultadParam = $request->input('facultad');
        $facultades = $this->modeloFacultad::all();

        $estudiantes = collect();

        if ($facultadParam) {
            $estudiantes = $this->modelo::with(['carrera.facultad'])
                ->whereHas('carrera.facultad', function($query) use ($facultadParam) {
                    if (is_numeric($facultadParam)) {
                        $query->where('facultades.idFacultad', $facultadParam);
                    } else {
                        $query->where(function($q) use ($facultadParam) {
                            $q->whereRaw('LOWER(facultades.Nombre_facultad) = LOWER(?)', [$facultadParam])
                              ->orWhereRaw('LOWER(facultades.Siglas) = LOWER(?)', [$facultadParam]);
                        });
                    }
                })
                ->get();
        }

        return view('consultas.estudiantes.estudiantesFacultad', compact('estudiantes', 'facultades'));
    }

    public function estudiantesCursoDiurno(Request $request)
    {
        $carreraParam = $request->input('carrera');
        $carreras = $this->modeloCarrera::all();

        $estudiantes = null;

        if ($carreraParam) {
            $estudiantes = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
                ->where('id_carrera', $carreraParam)
                ->whereHas('modalidad', function($query) {
                    $query->whereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%diurno%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%regular diurno%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%curso diurno%']);
                })
                ->get();
        }

        return view('consultas.estudiantes.estudiantesCursoDiurno', compact('estudiantes', 'carreras', 'carreraParam'));
    }

    public function estudiantesCursoEncuentro(Request $request)
    {
        $carreraParam = $request->input('carrera');
        $carreras = $this->modeloCarrera::all();

        $estudiantes = null;

        if ($carreraParam) {
            $estudiantes = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
                ->where('id_carrera', $carreraParam)
                ->whereHas('modalidad', function($query) {
                    $query->whereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%por encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%curso por encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%semipresencial%']);
                })
                ->get();
        }

        return view('consultas.estudiantes.estudiantesCursoEncuentro', compact('estudiantes', 'carreras', 'carreraParam'));
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}