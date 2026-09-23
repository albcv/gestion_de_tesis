<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\grupos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Exception;

class gruposController extends Controller
{
    protected $modelo = grupos::class;
    protected $rutaVista = 'gestionarGrupos';
    protected $columnaNumero = 'número';
    protected $columnaId = 'id';

    protected $tablaGrupo;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instancia = new $this->modelo;
        $this->tablaGrupo = $instancia->getTable();
        $this->columnaIdPrimaria = $instancia->getKeyName();
    }

    /**
     * Listado, formulario de creación o formulario de edición.
     *
     *  /gestionarGrupos                       → listado
     *  /gestionarGrupos?accion=crear          → formulario crear
     *  /gestionarGrupos?accion=editar&id=X    → formulario editar
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.grupo.formulario');
        }

        if ($accion === 'editar' && $id) {
            $grupo = $this->modelo::find($id);

            if (!$grupo) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'El grupo que intenta editar no existe');
            }

            return view('gestionar.grupo.formulario', ['grupo' => $grupo]);
        }

        $grupos = $this->modelo::orderBy($this->columnaNumero, 'asc')->get();

        return view('gestionar.grupo.index', compact('grupos'));
    }

    /**
     * Agrega un nuevo grupo.
     * Si el botón pulsado es "continuar", vuelve al formulario de creación.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'número' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique($this->tablaGrupo, $this->columnaNumero),
            ],
        ], [
            'número.required' => 'El número del grupo es obligatorio',
            'número.integer'  => 'El número del grupo debe ser un número entero',
            'número.min'      => 'El número del grupo debe ser al menos 1',
            'número.max'      => 'El número del grupo no puede ser mayor a 999',
            'número.unique'   => 'Ya existe un grupo con ese número',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $grupo = new $this->modelo();
            $grupo->{$this->columnaNumero} = $request->número;
            $grupo->save();

            DB::commit();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Grupo creado correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar el grupo: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Elimina un grupo por ID (valida que no tenga estudiantes asociados).
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaGrupo . ',' . $this->columnaIdPrimaria,
        ], [
            'id.required' => 'El ID del grupo es obligatorio',
            'id.exists'   => 'El grupo seleccionado no existe',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'El grupo no existe o ya ha sido eliminado');
        }

        // Verificar si hay estudiantes asociados
        $tieneEstudiantes = DB::table('estudiantes')->where('id_grupo', $request->id)->exists();

        if ($tieneEstudiantes) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se puede eliminar el grupo porque tiene estudiantes asociados');
        }

        try {
            DB::beginTransaction();
            $this->modelo::destroy($request->id);
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar el grupo: ' . $e->getMessage());
        }
    }

    /**
     * Elimina varios grupos a la vez (recibe ids[]).
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un grupo para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        // Verificar si alguno tiene estudiantes asociados
        $conEstudiantes = DB::table('estudiantes')->whereIn('id_grupo', $ids)->exists();

        if ($conEstudiantes) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Alguno de los grupos seleccionados tiene estudiantes asociados y no puede eliminarse');
        }

        try {
            DB::beginTransaction();
            $this->modelo::whereIn($this->columnaIdPrimaria, $ids)->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar los grupos: ' . $e->getMessage());
        }
    }

    /**
     * Modifica un grupo existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id'     => 'required|exists:' . $this->tablaGrupo . ',' . $this->columnaIdPrimaria,
            'número' => [
                'required',
                'integer',
                'min:1',
                'max:999',
                Rule::unique($this->tablaGrupo, $this->columnaNumero)->ignore($request->id, $this->columnaIdPrimaria),
            ],
        ], [
            'id.required'     => 'El ID del grupo es obligatorio',
            'id.exists'       => 'El grupo seleccionado no existe',
            'número.required' => 'El número del grupo es obligatorio',
            'número.integer'  => 'El número del grupo debe ser un número entero',
            'número.min'      => 'El número del grupo debe ser al menos 1',
            'número.max'      => 'El número del grupo no puede ser mayor a 999',
            'número.unique'   => 'Ya existe otro grupo con ese número',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $grupo = $this->modelo::find($request->id);
            if (!$grupo) {
                DB::rollBack();
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'No se encontró el grupo a modificar');
            }

            $grupo->{$this->columnaNumero} = $request->número;
            $grupo->save();

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar el grupo: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vacía la tabla de grupos.
     */
    public function vaciar()
    {
        try {
            $tieneEstudiantes = DB::table('estudiantes')->exists();

            if ($tieneEstudiantes) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'No se pueden eliminar todos los grupos porque hay estudiantes asociados');
            }

            DB::beginTransaction();
            $this->modelo::query()->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar los grupos: ' . $e->getMessage());
        }
    }

    /**
     * Exporta los grupos a CSV.
     */
    public function exportarCsv()
    {
        $grupos = $this->modelo::orderBy($this->columnaNumero)->get();

        $nombreArchivo = 'grupos_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($grupos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Número del Grupo'], ';');

            foreach ($grupos as $g) {
                fputcsv($out, [$g->{$this->columnaNumero}], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}