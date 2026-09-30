<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tutor_estudiante;
use App\Models\Profesor;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    /**
     * Punto de entrada.
     *  - Sin id_estudiante → vista de búsqueda con datalist.
     *  - Con id_estudiante → vista de asignación de tutores.
     */
    public function mostrarAsignarTutor($id_estudiante = null)
    {
        // Sin estudiante → mostrar buscador
        if (!$id_estudiante) {
            return view('gestionar.tutor.buscarEstudianteTutor');
        }

        try {
            $estudiante = $this->modeloEstudiante::with(['carrera.facultad', 'tutores.profesor'])
                ->findOrFail($id_estudiante);

            $profesores = $this->modeloProfesor::with(['departamento', 'tutorados'])
                ->get();

            $tutoresActuales  = $estudiante->tutores;
            $cantidadTutores  = $tutoresActuales->count();
            $slotsDisponibles = max(0, self::MAX_TUTORES_POR_ESTUDIANTE - $cantidadTutores);

            return view('gestionar.tutor.asignarTutor', compact(
                'estudiante',
                'profesores',
                'tutoresActuales',
                'cantidadTutores',
                'slotsDisponibles'
            ));
        } catch (\Exception $e) {
            return redirect()->route('asignarTutorEstudiante')
                ->with('error', 'Estudiante no encontrado o inválido.');
        }
    }

    /**
     * AJAX: búsqueda de estudiantes para el datalist.
     * Solo devuelve estudiantes con menos de MAX_TUTORES_POR_ESTUDIANTE tutores.
     */
    public function buscarEstudiantes(Request $request)
    {
        $termino = trim($request->input('q', ''));

        try {
            $query = $this->modeloEstudiante::query()
                ->with(['carrera.facultad'])
                ->has('tutores', '<', self::MAX_TUTORES_POR_ESTUDIANTE);

            if ($termino !== '') {
                $query->where(function ($q) use ($termino) {
                    $q->where('Nombre_estudiante', 'LIKE', "%{$termino}%")
                      ->orWhere('Apellido1',       'LIKE', "%{$termino}%")
                      ->orWhere('Apellido2',       'LIKE', "%{$termino}%")
                      ->orWhere('CI_estudiante',   'LIKE', "%{$termino}%");
                });
            }

            $estudiantes = $query
                ->orderBy('Nombre_estudiante')
                ->orderBy('Apellido1')
                ->limit(30)
                ->get();

            $resultados = $estudiantes->map(function ($e) {
                $nombre = trim(
                    $e->Nombre_estudiante . ' ' .
                    $e->Apellido1 . ' ' .
                    ($e->Apellido2 ?? '')
                );

                return [
                    'id'      => $e->id,
                    'label'   => $nombre . ' (CI: ' . $e->CI_estudiante . ')',
                    'nombre'  => $nombre,
                    'ci'      => $e->CI_estudiante,
                    'carrera' => $e->carrera->Nombre_carrera ?? 'Sin carrera',
                ];
            });

            return response()->json($resultados);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al buscar estudiantes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Asigna uno o varios tutores a un estudiante.
     */
    public function agregar(Request $request)
    {
        $raw = $request->input('id_profesor', []);
        if (!is_array($raw)) {
            $raw = [$raw];
        }

        $idsProfesores = array_values(array_unique(
            array_filter(array_map('intval', $raw), fn($id) => $id > 0)
        ));

        $validator = Validator::make([
            'id_estudiante' => $request->input('id_estudiante'),
            'id_profesor'   => $idsProfesores,
        ], [
            'id_estudiante'  => 'required|exists:' . $this->tablaEstudiante . ',' . $this->columnaIdEstudiante,
            'id_profesor'    => 'required|array|min:1',
            'id_profesor.*'  => 'integer|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesor,
        ], [
            'id_estudiante.required' => 'El estudiante es obligatorio',
            'id_estudiante.exists'   => 'El estudiante seleccionado no existe',
            'id_profesor.required'   => 'Debe seleccionar al menos un profesor',
            'id_profesor.min'        => 'Debe seleccionar al menos un profesor',
            'id_profesor.*.exists'   => 'Uno de los profesores seleccionados no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $idEstudiante = (int) $request->input('id_estudiante');

            $tutoresActuales  = $this->modelo::where($this->columnaEstudiante, $idEstudiante)->count();
            $slotsDisponibles = self::MAX_TUTORES_POR_ESTUDIANTE - $tutoresActuales;

            if ($slotsDisponibles <= 0) {
                return redirect()->back()
                    ->with('error', 'El estudiante ya tiene el máximo de '
                        . self::MAX_TUTORES_POR_ESTUDIANTE . ' tutores asignados.')
                    ->withInput();
            }

            if (count($idsProfesores) > $slotsDisponibles) {
                return redirect()->back()
                    ->with('error', "Solo puede asignar {$slotsDisponibles} tutor(es) más a este estudiante.")
                    ->withInput();
            }

            $yaAsignados = $this->modelo::where($this->columnaEstudiante, $idEstudiante)
                ->whereIn($this->columnaProfesor, $idsProfesores)
                ->pluck($this->columnaProfesor)
                ->map(fn($v) => (int) $v)
                ->toArray();

            $idsNuevos = array_values(array_diff($idsProfesores, $yaAsignados));

            if (empty($idsNuevos)) {
                return redirect()->back()
                    ->with('error', 'El/los profesor(es) seleccionado(s) ya está(n) asignado(s) como tutor(es) de este estudiante.')
                    ->withInput();
            }

            DB::beginTransaction();

            $insertados = 0;
            foreach ($idsNuevos as $idProfesor) {
                $obj = new $this->modelo();
                $obj->{$this->columnaProfesor}   = $idProfesor;
                $obj->{$this->columnaEstudiante} = $idEstudiante;
                $obj->save();
                $insertados++;
            }

            DB::commit();

            $estudiante = $this->modeloEstudiante::find($idEstudiante);

            $mensaje = $insertados === 1
                ? 'Tutor asignado correctamente al estudiante'
                : $insertados . ' tutores asignados correctamente al estudiante';

            if ($insertados < count($idsProfesores)) {
                $mensaje .= ' (' . (count($idsProfesores) - $insertados) . ' ya estaban asignados)';
            }

            return redirect()->route('verUsuario', $estudiante->id_usuario)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
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