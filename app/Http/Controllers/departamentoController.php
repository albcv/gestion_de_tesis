<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class departamentoController extends Controller
{
    protected $modelo = Departamento::class;
    protected $rutaVista = 'gestionarDepartamento';
    protected $columnaNombre = 'Nombre_departamento';
    protected $columnaId = 'idDepartamento';

    protected $nombreTabla;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $modeloInstancia = new $this->modelo;
        $this->nombreTabla = $modeloInstancia->getTable();
        $this->columnaIdPrimaria = $modeloInstancia->getKeyName();
    }

    /**
     * Listado, formulario de creación o formulario de edición.
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.departamento.formulario');
        }

        if ($accion === 'editar' && $id) {
            $departamento = $this->modelo::find($id);

            if (!$departamento) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'El departamento que intenta editar no existe');
            }

            return view('gestionar.departamento.formulario', compact('departamento'));
        }

        $departamentos = $this->modelo::all();

        return view('gestionar.departamento.index', compact('departamentos'));
    }

    /**
     * Agrega un nuevo departamento.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'departamento' => 'required|string|min:10|max:100',
        ], [
            'departamento.required' => 'El nombre del departamento es obligatorio',
            'departamento.string'   => 'El nombre debe ser una cadena de texto',
            'departamento.min'      => 'El nombre debe tener al menos 10 caracteres',
            'departamento.max'      => 'El nombre no puede exceder 100 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)
                ->withErrors($validator)
                ->withInput();
        }

        if ($this->modelo::where($this->columnaNombre, $request->departamento)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe un departamento con ese nombre')
                ->withInput();
        }

        $departamento = new $this->modelo();
        $departamento->{$this->columnaNombre} = $request->departamento;
        $departamento->save();

        if ($modoContinuar) {
            return redirect($urlFormCrear)
                ->with('success', 'Departamento creado correctamente. Puede seguir agregando.');
        }

        return redirect()->route($this->rutaVista);
    }

    /**
     * Elimina un departamento por ID.
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->nombreTabla . ',' . $this->columnaIdPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'El departamento no existe o ya ha sido eliminado');
        }

        $this->modelo::destroy($request->id);

        return redirect()->route($this->rutaVista);
    }

    /**
     * Elimina varios departamentos a la vez.
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un departamento para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        $this->modelo::whereIn($this->columnaIdPrimaria, $ids)->delete();

        return redirect()->route($this->rutaVista);
    }

    /**
     * Modifica un departamento existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id'           => 'required|exists:' . $this->nombreTabla . ',' . $this->columnaIdPrimaria,
            'departamento' => 'required|string|min:10|max:100',
        ], [
            'id.required'           => 'El ID es obligatorio',
            'id.exists'             => 'El departamento no existe',
            'departamento.required' => 'El nombre del departamento es obligatorio',
            'departamento.string'   => 'El nombre debe ser una cadena de texto',
            'departamento.min'      => 'El nombre debe tener al menos 10 caracteres',
            'departamento.max'      => 'El nombre no puede exceder 100 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)
                ->withErrors($validator)
                ->withInput();
        }

        if ($this->modelo::where($this->columnaNombre, $request->departamento)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otro departamento con ese nombre')
                ->withInput();
        }

        $departamento = $this->modelo::find($request->id);
        if (!$departamento) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se encontró el departamento a modificar');
        }

        $departamento->{$this->columnaNombre} = $request->departamento;
        $departamento->save();

        return redirect()->route($this->rutaVista);
    }

    /**
     * Vacía la tabla de departamentos.
     */
    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return redirect()->route($this->rutaVista);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar los departamentos: ' . $e->getMessage());
        }
    }

    /**
     * Exporta los departamentos a CSV.
     */
    public function exportarCsv()
    {
        $departamentos = $this->modelo::orderBy($this->columnaNombre)->get();

        $nombreArchivo = 'departamentos_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($departamentos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Nombre del Departamento'], ';');

            foreach ($departamentos as $d) {
                fputcsv($out, [$d->{$this->columnaNombre}], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}