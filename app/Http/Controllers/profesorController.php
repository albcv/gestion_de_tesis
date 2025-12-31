<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Profesor;
use Illuminate\Http\Request;


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

    public function profesoresDepartamento(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');
        $departamentos = $this->modeloDepartamento::all();

        $profesores = null;

        if ($departamentoParam) {
            $profesores = $this->modelo::with('departamento')
                ->where($this->columnaDepartamento, $departamentoParam)
                ->get();
        }

        return view('consultas.profesores.profesoresDepartamento', compact('profesores', 'departamentos', 'departamentoParam'));
    }

    public function profesoresNoTutores(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');
        $departamentos = $this->modeloDepartamento::all();

        $profesores = null;

        if ($departamentoParam) {
            // CORRECCIÓN: Usar 'tutorados' en lugar de 'tutor'
            $profesores = $this->modelo::with(['departamento'])
                ->where($this->columnaDepartamento, $departamentoParam)
                ->whereDoesntHave('tutorados')
                ->get();
        }

        return view('consultas.profesores.profesoresNoTutores', compact('profesores', 'departamentos', 'departamentoParam'));
    }

    public function profesoresDoctores(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');
        $departamentos = $this->modeloDepartamento::all();

        $profesores = null;

        if ($departamentoParam) {
            $profesores = $this->modelo::with('departamento')
                ->where($this->columnaDepartamento, $departamentoParam)
                ->where(function($query) {
                    $query->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor en ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') = LOWER(?)', ['doctor en ciencias'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['doctor%ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%doctor ciencias%']);
                })
                ->get();
        }

        return view('consultas.profesores.profesoresDoctores', compact('profesores', 'departamentos', 'departamentoParam'));
    }

    public function profesoresMáster(Request $request)
    {
        $departamentoParam = $request->input('id_departamento');
        $departamentos = $this->modeloDepartamento::all();

        $profesores = null;

        if ($departamentoParam) {
            $profesores = $this->modelo::with('departamento')
                ->where($this->columnaDepartamento, $departamentoParam)
                ->where(function($query) {
                    $query->whereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['máster%ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['master%ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%máster en %ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%master en %ciencias%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%msc%'])
                          ->orWhereRaw('LOWER(' . $this->columnaCategoriaCientifica . ') LIKE LOWER(?)', ['%m.sc%']);
                })
                ->get();
        }

        return view('consultas.profesores.profesoresMáster', compact('profesores', 'departamentos', 'departamentoParam'));
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