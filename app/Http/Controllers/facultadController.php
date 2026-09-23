<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facultad;
use Illuminate\Support\Facades\Validator;

class facultadController extends Controller
{
    protected $modelo = Facultad::class;
    protected $rutaVista = 'gestionarFacultad';
    protected $columnaNombre = 'Nombre_facultad';
    protected $columnaSiglas = 'Siglas';
    protected $columnaId = 'idFacultad';

    protected $tablaFacultad;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instanciaFacultad = new $this->modelo;

        $this->tablaFacultad = $instanciaFacultad->getTable();
        $this->columnaIdPrimaria = $instanciaFacultad->getKeyName();
    }

    /**
     * Listado, formulario de creación o formulario de edición
     * según el query param "accion".
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.facultad.formulario');
        }

        if ($accion === 'editar' && $id) {
            $facultad = $this->modelo::find($id);

            if (!$facultad) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'La facultad que intenta editar no existe');
            }

            return view('gestionar.facultad.formulario', compact('facultad'));
        }

        $facultades = $this->modelo::all();

        return view('gestionar.facultad.index', compact('facultades'));
    }

        /**
     * Agrega una nueva facultad.
     * Si el botón pulsado es "continuar", vuelve al formulario de creación.
     * Si es "guardar" (o no llega accion), redirige al listado.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'nombre_facultad' => 'required|string|min:20|max:100',
            'siglas'          => 'required|string|min:3|max:10',
        ], [
            'nombre_facultad.required' => 'El nombre de la facultad es obligatorio',
            'nombre_facultad.string'   => 'El nombre debe ser una cadena de texto',
            'nombre_facultad.min'      => 'El nombre debe tener al menos 20 caracteres',
            'nombre_facultad.max'      => 'El nombre no puede exceder 100 caracteres',
            'siglas.required'          => 'Las siglas son obligatorias',
            'siglas.string'            => 'Las siglas deben ser una cadena de texto',
            'siglas.min'               => 'Las siglas deben tener al menos 3 caracteres',
            'siglas.max'               => 'Las siglas no pueden exceder 10 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)
                ->withErrors($validator)
                ->withInput();
        }

        // Duplicados
        if ($this->modelo::where($this->columnaNombre, $request->nombre_facultad)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe una facultad con ese nombre')
                ->withInput();
        }

        if ($this->modelo::where($this->columnaSiglas, $request->siglas)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existen esas siglas para otra facultad')
                ->withInput();
        }

        $facultad = new $this->modelo;
        $facultad->{$this->columnaNombre} = $request->nombre_facultad;
        $facultad->{$this->columnaSiglas} = $request->siglas;
        $facultad->save();

        // ---- Redirección según el botón pulsado ----
        if ($modoContinuar) {
            return redirect($urlFormCrear)
                ->with('success', 'Facultad creada correctamente. Puede seguir agregando.');
        }

        return redirect()->route($this->rutaVista);
    }
    
    /**
     * Elimina una facultad por ID.
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaFacultad . ',' . $this->columnaIdPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'La facultad no existe o ya ha sido eliminada');
        }

        $this->modelo::destroy($request->id);

        return redirect()->route($this->rutaVista);
    }

    /**
     * Elimina varias facultades a la vez (recibe ids[]).
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una facultad para eliminar');
        }

        // Validar que todos sean enteros y existan
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
     * Modifica una facultad existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id'              => 'required|exists:' . $this->tablaFacultad . ',' . $this->columnaIdPrimaria,
            'nombre_facultad' => 'required|string|min:20|max:100',
            'siglas'          => 'required|string|min:3|max:10',
        ], [
            'id.required'              => 'El ID es obligatorio',
            'id.exists'                => 'La facultad no existe',
            'nombre_facultad.required' => 'El nombre de la facultad es obligatorio',
            'nombre_facultad.string'   => 'El nombre debe ser una cadena de texto',
            'nombre_facultad.min'      => 'El nombre debe tener al menos 20 caracteres',
            'nombre_facultad.max'      => 'El nombre no puede exceder 100 caracteres',
            'siglas.required'          => 'Las siglas son obligatorias',
            'siglas.string'            => 'Las siglas deben ser una cadena de texto',
            'siglas.min'               => 'Las siglas deben tener al menos 3 caracteres',
            'siglas.max'               => 'Las siglas no pueden exceder 10 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)
                ->withErrors($validator)
                ->withInput();
        }

        if ($this->modelo::where($this->columnaNombre, $request->nombre_facultad)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otra facultad con ese nombre')
                ->withInput();
        }

        if ($this->modelo::where($this->columnaSiglas, $request->siglas)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existen esas siglas para otra facultad')
                ->withInput();
        }

        $facultad = $this->modelo::find($request->id);
        if (!$facultad) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se encontró la facultad a modificar');
        }

        $facultad->{$this->columnaNombre} = $request->nombre_facultad;
        $facultad->{$this->columnaSiglas} = $request->siglas;
        $facultad->save();

        return redirect()->route($this->rutaVista);
    }

    /**
     * Vacía la tabla de facultades.
     */
    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();

            return redirect()->route($this->rutaVista);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar las facultades: ' . $e->getMessage());
        }
    }

    /**
     * Exporta las facultades a CSV.
     */
    public function exportarCsv()
    {
        $facultades = $this->modelo::orderBy($this->columnaNombre)->get();

        $nombreArchivo = 'facultades_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($facultades) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel abra bien las tildes
            fwrite($out, "\xEF\xBB\xBF");

            // Cabecera
            fputcsv($out, ['Nombre de la Facultad', 'Siglas'], ';');

            // Filas
            foreach ($facultades as $f) {
                fputcsv($out, [
                    $f->{$this->columnaNombre},
                    $f->{$this->columnaSiglas},
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

  


}


    