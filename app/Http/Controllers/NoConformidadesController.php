<?php

namespace App\Http\Controllers;

use App\Models\NoConformidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoConformidadesController extends Controller
{
    protected $modelo = NoConformidades::class;
    protected $rutaVista = 'gestionarNoConformidades';
    protected $columnaDeficiencias = 'Deficiencias_detectadas';

    protected $tablaNoConformidades;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instancia = new $this->modelo;
        $this->tablaNoConformidades = $instancia->getTable();
        $this->columnaIdPrimaria = $instancia->getKeyName();
    }

    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.noConformidad.formulario');
        }

        if ($accion === 'editar' && $id) {
            $nc = $this->modelo::find($id);

            if (!$nc) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'La no conformidad que intenta editar no existe');
            }

            return view('gestionar.noConformidad.formulario', ['nc' => $nc]);
        }

        $ncs = $this->modelo::all();
        return view('gestionar.noConformidad.index', compact('ncs'));
    }

    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'deficiencias_detectadas' => 'required|string|min:10|max:500',
        ], [
            'deficiencias_detectadas.required' => 'Las deficiencias detectadas son obligatorias',
            'deficiencias_detectadas.string' => 'Las deficiencias detectadas deben ser texto',
            'deficiencias_detectadas.min' => 'Las deficiencias detectadas deben tener al menos 10 caracteres',
            'deficiencias_detectadas.max' => 'Las deficiencias detectadas no pueden exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaDeficiencias, $request->deficiencias_detectadas)->exists()) {
            return redirect($urlFormCrear)
                ->with('error', 'Ya existe una no conformidad con esas deficiencias detectadas')
                ->withInput();
        }

        $nc = new $this->modelo();
        $nc->{$this->columnaDeficiencias} = $request->deficiencias_detectadas;
        $nc->save();

        if ($modoContinuar) {
            return redirect($urlFormCrear)
                ->with('success', 'No conformidad creada correctamente. Puede seguir agregando.');
        }

        return redirect()->route($this->rutaVista);
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaNoConformidades . ',' . $this->columnaIdPrimaria,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'La no conformidad no existe o ya ha sido eliminada');
        }

        $this->modelo::destroy($request->id);
        return redirect()->route($this->rutaVista);
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos una no conformidad para eliminar');
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

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaNoConformidades . ',' . $this->columnaIdPrimaria,
            'deficiencias_detectadas' => 'required|string|min:10|max:500',
        ], [
            'deficiencias_detectadas.required' => 'Las deficiencias detectadas son obligatorias',
            'deficiencias_detectadas.min' => 'Las deficiencias detectadas deben tener al menos 10 caracteres',
            'deficiencias_detectadas.max' => 'Las deficiencias detectadas no pueden exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        if ($this->modelo::where($this->columnaDeficiencias, $request->deficiencias_detectadas)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->exists()) {
            return redirect($urlFormEditar)
                ->with('error', 'Ya existe otra no conformidad con esas deficiencias detectadas')
                ->withInput();
        }

        $nc = $this->modelo::find($request->id);
        if (!$nc) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se encontró la no conformidad a modificar');
        }

        $nc->{$this->columnaDeficiencias} = $request->deficiencias_detectadas;
        $nc->save();

        return redirect()->route($this->rutaVista);
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return redirect()->route($this->rutaVista);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar las no conformidades: ' . $e->getMessage());
        }
    }

    public function exportarCsv()
    {
        $ncs = $this->modelo::orderBy($this->columnaDeficiencias)->get();

        $nombreArchivo = 'no_conformidades_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($ncs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Deficiencias Detectadas'], ';');
            foreach ($ncs as $nc) {
                fputcsv($out, [$nc->{$this->columnaDeficiencias}], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}