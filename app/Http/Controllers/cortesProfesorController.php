<?php

namespace App\Http\Controllers;

use App\Models\Cortes_de_tesis_has_Profesor_oponente;
use App\Models\Cortes_de_tesis;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

    /**
     * Muestra la vista para vincular uno o varios profesores al corte.
     */
    public function mostrarVincular($idCorte)
    {
        try {
            $corte = $this->modeloCorte::with('profesores')->findOrFail($idCorte);

            // Profesores ya vinculados a este corte
            $profesoresVinculadosIds = $corte->profesores
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            // Profesores que aún NO están vinculados
            $profesoresDisponibles = Profesor::whereNotIn('id', $profesoresVinculadosIds)
                ->with('departamento')
                ->orderBy('Nombre_profesor')
                ->orderBy('Apellido1')
                ->get();

            return view('gestionar.corte.asignarOponente',
                compact('corte', 'profesoresDisponibles'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Vincula uno o varios profesores al corte.
     * Acepta `profesor_id` como escalar o como array (`profesor_id[]`).
     */
    public function vincular(Request $request)
    {
        // ---------- Normalizar profesor_id a array de enteros ----------
        $raw = $request->input('profesor_id', []);
        if (!is_array($raw)) {
            $raw = [$raw];
        }

        $idsProfesores = array_values(array_unique(
            array_filter(array_map('intval', $raw), fn($id) => $id > 0)
        ));

        // ---------- Validación ----------
        $validator = Validator::make([
            'corte_tesis_id' => $request->input('corte_tesis_id'),
            'profesor_id'    => $idsProfesores,
        ], [
            'corte_tesis_id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
            'profesor_id'    => 'required|array|min:1',
            'profesor_id.*'  => 'integer|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
        ], [
            'corte_tesis_id.required' => 'El corte de tesis es obligatorio',
            'corte_tesis_id.exists'   => 'El corte de tesis seleccionado no existe',
            'profesor_id.required'    => 'Debe seleccionar al menos un profesor',
            'profesor_id.min'         => 'Debe seleccionar al menos un profesor',
            'profesor_id.*.exists'    => 'Uno de los profesores seleccionados no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $idCorte = (int) $request->input('corte_tesis_id');

        try {
            DB::beginTransaction();

            // ---------- Filtrar los que ya están vinculados ----------
            $yaVinculados = $this->modelo::where($this->columnaCorte, $idCorte)
                ->whereIn($this->columnaProfesor, $idsProfesores)
                ->pluck($this->columnaProfesor)
                ->map(fn($v) => (int) $v)
                ->toArray();

            $idsNuevos = array_values(array_diff($idsProfesores, $yaVinculados));

            if (empty($idsNuevos)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'El/los profesor(es) seleccionado(s) ya está(n) vinculado(s) a este corte.')
                    ->withInput();
            }

            // ---------- Insertar ----------
            $insertados = 0;
            foreach ($idsNuevos as $idProfesor) {
                $cp = new $this->modelo();
                $cp->{$this->columnaCorte}    = $idCorte;
                $cp->{$this->columnaProfesor} = $idProfesor;
                $cp->save();
                $insertados++;
            }

            DB::commit();

            $mensaje = $insertados === 1
                ? 'Profesor vinculado correctamente al corte'
                : $insertados . ' profesores vinculados correctamente al corte';

            if ($insertados < count($idsProfesores)) {
                $mensaje .= ' (' . (count($idsProfesores) - $insertados) . ' ya estaban vinculados)';
            }

            return redirect()->route('verCorte', ['id' => $idCorte])
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al vincular los profesores: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function desvincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'corte_tesis_id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'profesor_id'    => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Datos inválidos para desvincular');
            }

            $relacion = $this->modelo::where($this->columnaCorte, $request->corte_tesis_id)
                ->where($this->columnaProfesor, $request->profesor_id)
                ->first();

            if (!$relacion) {
                return redirect()->back()
                    ->with('error', 'No se encontró la relación corte-profesor');
            }

            $relacion->delete();

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
                'id'          => 'required|exists:' . $this->tablaCorteProfesor . ',' . $this->columnaIdCorteProfesor,
                'id_corte'    => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'id_profesor' => 'required|exists:' . $this->tablaProfesor . ',' . $this->columnaIdProfesorPrimaria,
            ], [
                'id_corte.required'    => 'El corte de tesis es obligatorio',
                'id_corte.exists'      => 'El corte de tesis seleccionado no existe',
                'id_profesor.required' => 'El profesor es obligatorio',
                'id_profesor.exists'   => 'El profesor seleccionado no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

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
                $cp->{$this->columnaCorte}    = $request->id_corte;
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