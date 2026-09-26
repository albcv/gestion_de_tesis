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

    /* =============================================================
       CRUD BÁSICO
       ============================================================= */

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

    /* =============================================================
       CONSULTAS
       ============================================================= */

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
        $yearParam    = $request->input('year_academico');

        $carreras = $this->modeloCarrera::all();
        $estudiantes = null;
        $carreraSeleccionada = null;

        if ($carreraParam) {
            $carreraSeleccionada = $this->modeloCarrera::find($carreraParam);

            $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
                ->where('id_carrera', $carreraParam)
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('tutor_estudiante')
                          ->whereRaw('tutor_estudiante.id_estudiante = estudiantes.id');
                });

            if ($yearParam && $yearParam !== '') {
                $query->where('year_academico', $yearParam);
            }

            $query->orderBy('year_academico', 'desc')
                  ->orderBy('Apellido1')
                  ->orderBy('Apellido2')
                  ->orderBy('Nombre_estudiante');

            $estudiantes = $query->get();
        }

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
                ->whereHas('carrera.facultad', function ($query) use ($facultadParam) {
                    if (is_numeric($facultadParam)) {
                        $query->where('facultades.idFacultad', $facultadParam);
                    } else {
                        $query->where(function ($q) use ($facultadParam) {
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
                ->whereHas('modalidad', function ($query) {
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
                ->whereHas('modalidad', function ($query) {
                    $query->whereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%por encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%curso por encuentro%'])
                          ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%semipresencial%']);
                })
                ->get();
        }

        return view('consultas.estudiantes.estudiantesCursoEncuentro', compact('estudiantes', 'carreras', 'carreraParam'));
    }

    /* =============================================================
       EXPORTAR CONSULTAS A CSV
       ============================================================= */

    /**
     * Exporta a CSV el resultado de cualquiera de las consultas de estudiantes.
     *
     * El parámetro "tipo" indica qué consulta exportar:
     *   - sin_tutor
     *   - atrasados_fundamentacion
     *   - curso_diurno
     *   - curso_encuentro
     *   - facultad
     */
    public function exportarConsultaCsv(Request $request)
    {
        $tipo = $request->input('tipo');

        try {
            switch ($tipo) {
                case 'sin_tutor':
                    $estudiantes = $this->querySinTutor($request);
                    $nombreBase  = 'estudiantes_sin_tutor';
                    break;

                case 'atrasados_fundamentacion':
                    $estudiantes = $this->queryAtrasadosFundamentacion($request);
                    $nombreBase  = 'estudiantes_atrasados_fundamentacion';
                    break;

                case 'curso_diurno':
                    $estudiantes = $this->queryCursoDiurno($request);
                    $nombreBase  = 'estudiantes_curso_diurno';
                    break;

                case 'curso_encuentro':
                    $estudiantes = $this->queryCursoEncuentro($request);
                    $nombreBase  = 'estudiantes_curso_encuentro';
                    break;

                case 'facultad':
                    $estudiantes = $this->queryFacultad($request);
                    $nombreBase  = 'estudiantes_facultad';
                    break;

                default:
                    abort(400, 'Tipo de consulta no válido');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al generar el CSV: ' . $e->getMessage());
        }

        $nombreArchivo = $nombreBase . '_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($estudiantes) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($out, [
                '#', 'Grupo', 'Modalidad', 'CI', 'Nro',
                'Nombre', 'Apellido1', 'Apellido2', 'Sexo',
                'Fecha de Ingreso', 'Año', 'Carrera',
            ], ';');

            foreach ($estudiantes as $index => $e) {
                fputcsv($out, [
                    $index + 1,
                    $e->grupo ? $e->grupo->número : '—',
                    $e->modalidad ? $e->modalidad->Nombre_modalidad : '—',
                    $e->CI_estudiante,
                    $e->número,
                    $e->Nombre_estudiante,
                    $e->Apellido1,
                    $e->Apellido2,
                    $e->sexo,
                    $e->Fecha_ingreso ? date('d/m/Y', strtotime($e->Fecha_ingreso)) : '—',
                    $e->year_academico,
                    $e->carrera ? $e->carrera->Nombre_carrera : '—',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* =============================================================
       QUERIES REUTILIZABLES (usadas por el export CSV)
       ============================================================= */

    private function querySinTutor(Request $request)
    {
        $carreraParam = $request->input('carrera');
        $yearParam    = $request->input('year_academico');

        $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tutor_estudiante')
                      ->whereRaw('tutor_estudiante.id_estudiante = estudiantes.id');
            });

        if ($carreraParam) {
            $query->where('id_carrera', $carreraParam);
        }

        if ($yearParam && $yearParam !== '') {
            $query->where('year_academico', $yearParam);
        }

        return $query->orderBy('year_academico', 'desc')
                     ->orderBy('Apellido1')
                     ->orderBy('Apellido2')
                     ->orderBy('Nombre_estudiante')
                     ->get();
    }

    private function queryAtrasadosFundamentacion(Request $request)
    {
        $carreraParam = $request->input('carrera');

        $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('tesis')
                      ->join('fundamentaciones', 'tesis.id', '=', 'fundamentaciones.id_tesis')
                      ->whereRaw('tesis.id_estudiante = estudiantes.id');
            });

        if ($carreraParam) {
            $query->where('id_carrera', $carreraParam);
        }

        return $query->orderBy('Apellido1')
                     ->orderBy('Apellido2')
                     ->orderBy('Nombre_estudiante')
                     ->get();
    }

    private function queryCursoDiurno(Request $request)
    {
        $carreraParam = $request->input('carrera');

        $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->whereHas('modalidad', function ($query) {
                $query->whereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%diurno%'])
                      ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%regular diurno%'])
                      ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%curso diurno%']);
            });

        if ($carreraParam) {
            $query->where('id_carrera', $carreraParam);
        }

        return $query->orderBy('Apellido1')
                     ->orderBy('Apellido2')
                     ->orderBy('Nombre_estudiante')
                     ->get();
    }

    private function queryCursoEncuentro(Request $request)
    {
        $carreraParam = $request->input('carrera');

        $query = $this->modelo::with(['grupo', 'modalidad', 'carrera'])
            ->whereHas('modalidad', function ($query) {
                $query->whereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%encuentro%'])
                      ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%por encuentro%'])
                      ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%curso por encuentro%'])
                      ->orWhereRaw('LOWER(Nombre_modalidad) LIKE LOWER(?)', ['%semipresencial%']);
            });

        if ($carreraParam) {
            $query->where('id_carrera', $carreraParam);
        }

        return $query->orderBy('Apellido1')
                     ->orderBy('Apellido2')
                     ->orderBy('Nombre_estudiante')
                     ->get();
    }

    private function queryFacultad(Request $request)
    {
        $facultadParam = $request->input('facultad');

        $query = $this->modelo::with(['carrera.facultad']);

        if ($facultadParam) {
            $query->whereHas('carrera.facultad', function ($q) use ($facultadParam) {
                if (is_numeric($facultadParam)) {
                    $q->where('facultades.idFacultad', $facultadParam);
                } else {
                    $q->where(function ($q) use ($facultadParam) {
                        $q->whereRaw('LOWER(facultades.Nombre_facultad) = LOWER(?)', [$facultadParam])
                          ->orWhereRaw('LOWER(facultades.Siglas) = LOWER(?)', [$facultadParam]);
                    });
                }
            });
        }

        return $query->orderBy('Apellido1')
                     ->orderBy('Apellido2')
                     ->orderBy('Nombre_estudiante')
                     ->get();
    }

    /* =============================================================
       UTILIDADES
       ============================================================= */

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