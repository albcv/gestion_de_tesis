<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfesorController extends Controller
{
    protected $modelo = Profesor::class;
    protected $modeloDepartamento = Departamento::class;
    protected $rutaVista = 'gestionarProfesor';
    protected $columnaDepartamento = 'id_departamento';
    protected $columnaCI = 'CI_profesor';
    protected $columnaNombre = 'Nombre_profesor';
    protected $columnaApellido1 = 'Apellido1';
    protected $columnaApellido2 = 'Apellido2';
    protected $columnaCategoriaDocente = 'Categoria_docente';
    protected $columnaCategoriaCientifica = 'Categoria_cientifica';
    protected $columnaUsuario = 'id_usuario';

    /* =============================================================
       CRUD BÁSICO
       ============================================================= */

    public function mostrar()
    {
        $profesores = $this->modelo::with(['departamento'])->get();
        $departamentos = $this->modeloDepartamento::all();

        return view('gestionar.gestionarProfesor', compact('profesores', 'departamentos'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'id_departamento' => 'required|exists:departamentos,idDepartamento',
            'ci' => 'required|unique:profesor,CI_profesor',
            'nombre_profesor' => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'apellido2' => 'required|string|max:40',
            'categoría_docente' => 'required|string|max:30',
            'categoría_científica' => 'required|string|max:30',
            'id_usuario' => 'required|exists:users,id'
        ]);

        $profesor = new $this->modelo();
        $profesor->{$this->columnaDepartamento} = $request->id_departamento;
        $profesor->{$this->columnaCI} = $request->ci;
        $profesor->{$this->columnaNombre} = $request->nombre_profesor;
        $profesor->{$this->columnaApellido1} = $request->apellido1;
        $profesor->{$this->columnaApellido2} = $request->apellido2;
        $profesor->{$this->columnaCategoriaDocente} = $request->categoría_docente;
        $profesor->{$this->columnaCategoriaCientifica} = $request->categoría_científica;
        $profesor->{$this->columnaUsuario} = $request->id_usuario;
        $profesor->save();

        return redirect(route($this->rutaVista))->with('success', 'Profesor agregado correctamente');
    }

    public function eliminar(Request $request)
    {
        $id = $request['id'];
        $this->modelo::destroy($id);

        return redirect(route($this->rutaVista))->with('success', 'Profesor eliminado correctamente');
    }

    public function modificar(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:profesor,id',
            'id_departamento' => 'required|exists:departamentos,idDepartamento',
            'ci' => 'required|unique:profesor,CI_profesor,' . $request->id,
            'nombre_profesor' => 'required|string|max:40',
            'apellido1' => 'required|string|max:40',
            'apellido2' => 'required|string|max:40',
            'categoría_docente' => 'required|string|max:30',
            'categoría_científica' => 'required|string|max:30',
            'id_usuario' => 'required|exists:users,id'
        ]);

        $profesor = $this->modelo::find($request->id);
        if ($profesor) {
            $profesor->{$this->columnaDepartamento} = $request->id_departamento;
            $profesor->{$this->columnaCI} = $request->ci;
            $profesor->{$this->columnaNombre} = $request->nombre_profesor;
            $profesor->{$this->columnaApellido1} = $request->apellido1;
            $profesor->{$this->columnaApellido2} = $request->apellido2;
            $profesor->{$this->columnaCategoriaDocente} = $request->categoría_docente;
            $profesor->{$this->columnaCategoriaCientifica} = $request->categoría_científica;
            $profesor->{$this->columnaUsuario} = $request->id_usuario;
            $profesor->save();

            return redirect(route($this->rutaVista))->with('success', 'Profesor actualizado correctamente');
        }

        return redirect()->back()->withErrors([
            'error' => 'No se pudo encontrar el profesor a modificar'
        ]);
    }

    /* =============================================================
       BÚSQUEDA POR CI (vista individual)
       ============================================================= */

    public function buscarProfesor(Request $request)
    {
        $request->validate([
            'ci' => 'required|string|size:11'
        ], [
            'ci.size' => 'El CI debe tener exactamente 11 caracteres.'
        ]);

        $profesor = $this->modelo::with('departamento')
            ->where($this->columnaCI, $request->ci)
            ->first();

        if (!$profesor) {
            return redirect()->back()->withErrors([
                'ci' => 'No se encontró ningún profesor con ese CI'
            ])->withInput();
        }

        return view('consultas.profesores.buscarProfesor', compact('profesor'));
    }

    /* =============================================================
       CONSULTAS — Métodos públicos que llaman al unificado
       ============================================================= */

    public function profesoresDepartamento(Request $request)
    {
        return $this->consultaProfesoresView($request, 'departamento');
    }

    public function profesoresNoTutores(Request $request)
    {
        return $this->consultaProfesoresView($request, 'no_tutores');
    }

    public function profesoresDoctores(Request $request)
    {
        return $this->consultaProfesoresView($request, 'doctores');
    }

    public function profesoresMáster(Request $request)
    {
        return $this->consultaProfesoresView($request, 'master');
    }

    /* =============================================================
       VISTA UNIFICADA DE CONSULTAS DE PROFESORES
       ============================================================= */

    private function consultaProfesoresView(Request $request, string $tipo)
    {
        $departamentos        = $this->modeloDepartamento::all();
        $profesores           = collect();
        $departamentoSeleccionado = null;

        $departamentoParam = $request->input('id_departamento');

        try {
            if ($departamentoParam) {
                $departamentoSeleccionado = $this->modeloDepartamento::find($departamentoParam);

                switch ($tipo) {

                    /* ---------------- DEPARTAMENTO ---------------- */
                    case 'departamento':
                        $profesores = $this->modelo::with('departamento')
                            ->where($this->columnaDepartamento, $departamentoParam)
                            ->orderBy($this->columnaApellido1)
                            ->orderBy($this->columnaApellido2)
                            ->orderBy($this->columnaNombre)
                            ->get();
                        break;

                    /* ---------------- NO TUTORES ---------------- */
                    case 'no_tutores':
                        $profesores = $this->modelo::with('departamento')
                            ->where($this->columnaDepartamento, $departamentoParam)
                            ->whereDoesntHave('tutorados')
                            ->orderBy($this->columnaApellido1)
                            ->orderBy($this->columnaApellido2)
                            ->orderBy($this->columnaNombre)
                            ->get();
                        break;

                    /* ---------------- DOCTORES EN CIENCIAS ---------------- */
                    case 'doctores':
                        $profesores = $this->modelo::with('departamento')
                            ->where($this->columnaDepartamento, $departamentoParam)
                            ->where(function ($query) {
                                $query->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor en ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') = LOWER(?)', ['doctor en ciencias'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['doctor%ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor ciencias%']);
                            })
                            ->orderBy($this->columnaApellido1)
                            ->orderBy($this->columnaApellido2)
                            ->orderBy($this->columnaNombre)
                            ->get();
                        break;

                    /* ---------------- MÁSTER EN CIENCIAS ---------------- */
                    case 'master':
                        $profesores = $this->modelo::with('departamento')
                            ->where($this->columnaDepartamento, $departamentoParam)
                            ->where(function ($query) {
                                $query->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['máster%ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['master%ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en %ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en %ciencias%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%msc%'])
                                      ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%m.sc%']);
                            })
                            ->orderBy($this->columnaApellido1)
                            ->orderBy($this->columnaApellido2)
                            ->orderBy($this->columnaNombre)
                            ->get();
                        break;

                    default:
                        abort(400, 'Tipo de consulta no válido');
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar la consulta: ' . $e->getMessage());
        }

        $hasFilter = $request->filled('id_departamento');

        return view('consultas.profesores.index', compact(
            'tipo',
            'profesores',
            'departamentos',
            'departamentoSeleccionado',
            'hasFilter'
        ));
    }

    /* =============================================================
       EXPORTAR CONSULTAS A CSV
       ============================================================= */

    public function exportarConsultaCsvProfesores(Request $request)
    {
        $tipo = $request->input('tipo');

        try {
            switch ($tipo) {
                case 'departamento':
                    $profesores = $this->queryDepartamento($request);
                    $nombreBase = 'profesores_departamento';
                    break;

                case 'no_tutores':
                    $profesores = $this->queryNoTutores($request);
                    $nombreBase = 'profesores_no_tutores';
                    break;

                case 'doctores':
                    $profesores = $this->queryDoctores($request);
                    $nombreBase = 'profesores_doctores';
                    break;

                case 'master':
                    $profesores = $this->queryMaster($request);
                    $nombreBase = 'profesores_master';
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

        $callback = function () use ($profesores) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                '#', 'Departamento', 'CI', 'Nombre',
                'Apellido1', 'Apellido2',
                'Categoría Docente', 'Categoría Científica',
            ], ';');

            foreach ($profesores as $index => $p) {
                fputcsv($out, [
                    $index + 1,
                    $p->departamento ? $p->departamento->Nombre_departamento : '—',
                    $p->{$this->columnaCI},
                    $p->{$this->columnaNombre},
                    $p->{$this->columnaApellido1},
                    $p->{$this->columnaApellido2},
                    $p->{$this->columnaCategoriaDocente},
                    $p->{$this->columnaCategoriaCientifica},
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /* =============================================================
       QUERIES REUTILIZABLES (usadas por el export CSV)
       ============================================================= */

    private function queryDepartamento(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');

        $query = $this->modelo::with('departamento');

        if ($departamentoParam) {
            $query->where($this->columnaDepartamento, $departamentoParam);
        }

        return $query->orderBy($this->columnaApellido1)
                     ->orderBy($this->columnaApellido2)
                     ->orderBy($this->columnaNombre)
                     ->get();
    }

    private function queryNoTutores(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');

        $query = $this->modelo::with('departamento')
            ->whereDoesntHave('tutorados');

        if ($departamentoParam) {
            $query->where($this->columnaDepartamento, $departamentoParam);
        }

        return $query->orderBy($this->columnaApellido1)
                     ->orderBy($this->columnaApellido2)
                     ->orderBy($this->columnaNombre)
                     ->get();
    }

    private function queryDoctores(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');

        $query = $this->modelo::with('departamento')
            ->where(function ($q) {
                $q->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor en ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') = LOWER(?)', ['doctor en ciencias'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['doctor%ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor ciencias%']);
            });

        if ($departamentoParam) {
            $query->where($this->columnaDepartamento, $departamentoParam);
        }

        return $query->orderBy($this->columnaApellido1)
                     ->orderBy($this->columnaApellido2)
                     ->orderBy($this->columnaNombre)
                     ->get();
    }

    private function queryMaster(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');

        $query = $this->modelo::with('departamento')
            ->where(function ($q) {
                $q->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['máster%ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['master%ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en %ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en %ciencias%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%msc%'])
                  ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%m.sc%']);
            });

        if ($departamentoParam) {
            $query->where($this->columnaDepartamento, $departamentoParam);
        }

        return $query->orderBy($this->columnaApellido1)
                     ->orderBy($this->columnaApellido2)
                     ->orderBy($this->columnaNombre)
                     ->get();
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