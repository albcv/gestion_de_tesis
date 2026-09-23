<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class modalidadController extends Controller
{
    protected $modelo = Modalidad::class;
    protected $rutaVista = 'gestionarModalidad';
    protected $columnaNombre = 'Nombre_modalidad';
    protected $columnaId = 'idModalidad';

    protected $tablaModalidad;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instanciaModalidad = new $this->modelo;

        $this->tablaModalidad = $instanciaModalidad->getTable();
        $this->columnaIdPrimaria = $instanciaModalidad->getKeyName();
    }

    /**
     * Listado, formulario de creación o formulario de edición
     * según el query param "accion".
     *
     *  /gestionarModalidad                       → listado
     *  /gestionarModalidad?accion=crear          → formulario crear
     *  /gestionarModalidad?accion=editar&id=X    → formulario editar
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.modalidad.formulario');
        }

        if ($accion === 'editar' && $id) {
            $modalidad = $this->modelo::find($id);

            if (!$modalidad) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'La modalidad que intenta editar no existe');
            }

            return view('gestionar.modalidad.formulario', compact('modalidad'));
        }

        $modalidades = $this->modelo::all();

        return view('gestionar.modalidad.index', compact('modalidades'));
    }

    /**
     * Agrega una nueva modalidad.
     * Si el botón pulsado es "continuar", vuelve al formulario de creación.
     * Si es "guardar" (o no llega accion), redirige al listado.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'nombre_modalidad' => 'required|string|min:10|max:50',
        ], [
            'nombre_modalidad.required' => 'El nombre de la modalidad es obligatorio',
            'nombre_modalidad.string'   => 'El nombre debe ser una cadena de texto',
            'nombre_modalidad.min'      => 'El nombre debe tener al menos 10 caracteres',
            'nombre_modalidad.max'      => 'El nombre no puede exceder 50 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)
                ->withErrors($validator)
                ->withInput();
        }

        // Duplicado
        if ($this->modelo::where($this->columnaNombre, $request->nombre_modalidad)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe una modalidad con ese nombre')
                ->withInput();
        }

        $modalidad = new $this->modelo;
        $modalidad->{$this->columnaNombre} = $request->nombre_modalidad;
        $modalidad->save();

        if ($modoContinuar) {
            return redirect($urlFormCrear)
                ->with('success', 'Modalidad creada correctamente. Puede seguir agregando.');
        }

        return redirect()->route($this->rutaVista);
    }

    /**
     * Elimina una modalidad por ID.
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'La modalidad no existe o ya ha sido eliminada');
        }

        $this->modelo::destroy($request->id);

        return redirect()->route($this->rutaVista);
    }

    /**
     * Elimina varias modalidades a la vez (recibe ids[]).
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una modalidad para eliminar');
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
     * Modifica una modalidad existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id'               => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdPrimaria,
            'nombre_modalidad' => 'required|string|min:10|max:50',
        ], [
            'id.required'               => 'El ID es obligatorio',
            'id.exists'                 => 'La modalidad no existe',
            'nombre_modalidad.required' => 'El nombre de la modalidad es obligatorio',
            'nombre_modalidad.string'   => 'El nombre debe ser una cadena de texto',
            'nombre_modalidad.min'      => 'El nombre debe tener al menos 10 caracteres',
            'nombre_modalidad.max'      => 'El nombre no puede exceder 50 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)
                ->withErrors($validator)
                ->withInput();
        }

        if ($this->modelo::where($this->columnaNombre, $request->nombre_modalidad)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otra modalidad con ese nombre')
                ->withInput();
        }

        $modalidad = $this->modelo::find($request->id);
        if (!$modalidad) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se encontró la modalidad a modificar');
        }

        $modalidad->{$this->columnaNombre} = $request->nombre_modalidad;
        $modalidad->save();

        return redirect()->route($this->rutaVista);
    }

    /**
     * Vacía la tabla de modalidades.
     */
    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();

            return redirect()->route($this->rutaVista);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar las modalidades: ' . $e->getMessage());
        }
    }

    /**
     * Exporta las modalidades a CSV.
     */
    public function exportarCsv()
    {
        $modalidades = $this->modelo::orderBy($this->columnaNombre)->get();

        $nombreArchivo = 'modalidades_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($modalidades) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8
            fwrite($out, "\xEF\xBB\xBF");

            // Cabecera
            fputcsv($out, ['Nombre de la Modalidad'], ';');

            // Filas
            foreach ($modalidades as $m) {
                fputcsv($out, [
                    $m->{$this->columnaNombre},
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}