<?php

namespace App\Http\Controllers;

use App\Models\Cortes_de_tesis_has_Profesor_oponente;
use App\Models\Cortes_de_tesis;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class cortesProfesorController extends Controller
{
    protected $modelo = Cortes_de_tesis_has_Profesor_oponente::class;
    protected $modeloCorte = Cortes_de_tesis::class;
    protected $modeloProfesor = Profesor::class;
    protected $rutaVista = 'gestionarCortesProfesor';
    protected $columnaCorte = 'corte_tesis_id';
    protected $columnaProfesor = 'profesor_id';
    
    protected $tablaCorteProfesor;
    protected $tablaCorte;
    protected $tablaProfesor;
    protected $columnaIdCorteProfesor;
    protected $columnaIdCortePrimaria;
    protected $columnaIdProfesorPrimaria;
    protected $columnaIdCorte;
    protected $columnaIdProfesor;

    public function __construct()
    {
        $instanciaCorteProfesor = new $this->modelo;
        $instanciaCorte = new $this->modeloCorte;
        $instanciaProfesor = new $this->modeloProfesor;
        
        $this->tablaCorteProfesor = $instanciaCorteProfesor->getTable();
        $this->tablaCorte = $instanciaCorte->getTable();
        $this->tablaProfesor = $instanciaProfesor->getTable();
        
        $this->columnaIdCorteProfesor = $instanciaCorteProfesor->getKeyName();
        $this->columnaIdCortePrimaria = $instanciaCorte->getKeyName();
        $this->columnaIdProfesorPrimaria = $instanciaProfesor->getKeyName();
        
        $this->columnaIdCorte = 'corte_tesis_id';
        $this->columnaIdProfesor = 'profesor_id';
    }

    public function mostrar()
    {
        try {
            $cps = $this->modelo::with(['corte.tesis', 'profesor'])->get();
            $cortes = $this->modeloCorte::with('tesis')->get();
            $profesores = $this->modeloProfesor::all();
            
            return view('gestionar.gestionarCortesProfesor', compact('cps', 'cortes', 'profesores'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las relaciones corte-profesor: ' . $e->getMessage());
        }
    }

    public function mostrarVincular($idCorte)
{
    try {
        $corte = $this->modeloCorte::with('profesores')->findOrFail($idCorte);
        
        // Obtener profesores que no están ya vinculados a este corte
        $profesoresVinculadosIds = $corte->profesores->pluck('id')->toArray();
        $profesoresDisponibles = Profesor::whereNotIn('id', $profesoresVinculadosIds)
            ->with('departamento')
            ->get();
            
        return view('gestionar.gestionarCortesProfesor.vincularProfesorCorte', compact('corte', 'profesoresDisponibles'));
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
    }
}
    

    public function vincular(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'corte_tesis_id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            'profesor_id' => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
        ], [
            'corte_tesis_id.required' => 'El corte de tesis es obligatorio',
            'corte_tesis_id.exists' => 'El corte de tesis seleccionado no existe',
            'profesor_id.required' => 'El profesor es obligatorio',
            'profesor_id.exists' => 'El profesor seleccionado no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar si ya existe la combinación corte-profesor
        $existente = $this->modelo::where($this->columnaCorte, $request->corte_tesis_id)
            ->where($this->columnaProfesor, $request->profesor_id)
            ->first();

        if ($existente) {
            return redirect()->back()
                ->with('error', 'Ya existe una relación para este corte y este profesor')
                ->withInput();
        }

        $cp = new $this->modelo();
        $cp->{$this->columnaCorte} = $request->corte_tesis_id;
        $cp->{$this->columnaProfesor} = $request->profesor_id;
        $cp->save();

        // Redirigir a la vista del corte específico
        return redirect()->route('verCorte', ['id' => $request->corte_tesis_id])
            ->with('success', 'Profesor vinculado correctamente al corte');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al vincular el profesor: ' . $e->getMessage())
            ->withInput();
    }
}

public function desvincular(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'corte_tesis_id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            'profesor_id' => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Datos inválidos para desvincular');
        }

        // Buscar la relación específica
        $relacion = $this->modelo::where($this->columnaCorte, $request->corte_tesis_id)
            ->where($this->columnaProfesor, $request->profesor_id)
            ->first();

        if (!$relacion) {
            return redirect()->back()
                ->with('error', 'No se encontró la relación corte-profesor');
        }

        // Eliminar la relación
        $relacion->delete();

        // Redirigir a la vista del corte específico
        return redirect()->route('verCorte', ['id' => $request->corte_tesis_id])
            ->with('success', 'Profesor desvinculado correctamente del corte');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al desvincular el profesor: ' . $e->getMessage());
    }
}

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorteProfesor . ',' . $this->columnaIdCorteProfesor,
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'id_profesor' => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
            ], [
                'id_corte.required' => 'El corte de tesis es obligatorio',
                'id_corte.exists' => 'El corte de tesis seleccionado no existe',
                'id_profesor.required' => 'El profesor es obligatorio',
                'id_profesor.exists' => 'El profesor seleccionado no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra combinación corte-profesor (excluyendo la actual)
            $existente = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaProfesor, $request->id_profesor)
                ->where($this->columnaIdCorteProfesor, '!=', $request->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe otra relación para este corte y este profesor')
                    ->withInput();
            }

            $cp = $this->modelo::find($request->id);
            if ($cp) {
                $cp->{$this->columnaCorte} = $request->id_corte;
                $cp->{$this->columnaProfesor} = $request->id_profesor;
                $cp->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Relación corte-profesor modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la relación corte-profesor a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la relación corte-profesor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las relaciones corte-profesor han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las relaciones corte-profesor: ' . $e->getMessage()
            ]);
        }
    }
}