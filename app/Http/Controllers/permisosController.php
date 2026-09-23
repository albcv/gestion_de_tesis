<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\permisos;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class permisosController extends Controller
{
    protected $modelo;
    protected $rutaVista = 'gestionarPermisos';
    protected $columnaPermiso = 'permiso';

    protected $tablaPermiso;
    protected $columnaIdPermiso;

    public function __construct()
    {
        $this->modelo = permisos::class;
        $instanciaPermiso = new $this->modelo;
        $this->tablaPermiso = $instanciaPermiso->getTable();
        $this->columnaIdPermiso = $instanciaPermiso->getKeyName();
    }

    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return view('gestionar.permiso.formulario');
        }

        if ($accion === 'editar' && $id) {
            $permiso = $this->modelo::find($id);

            if (!$permiso) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'El permiso que intenta editar no existe');
            }

            return view('gestionar.permiso.formulario', compact('permiso'));
        }

        $objetos = $this->modelo::all();
        return view('gestionar.permiso.index', compact('objetos'));
    }

    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'permiso' => [
                'required', 'string', 'min:3', 'max:120',
                'unique:' . $this->tablaPermiso . ',' . $this->columnaPermiso,
            ],
        ], [
            'permiso.required' => 'El nombre del permiso es obligatorio',
            'permiso.string' => 'El nombre del permiso debe ser texto',
            'permiso.min' => 'El nombre del permiso debe tener al menos 3 caracteres',
            'permiso.max' => 'El nombre del permiso no puede exceder los 120 caracteres',
            'permiso.unique' => 'Este nombre de permiso ya está registrado',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $obj = new $this->modelo();
            $obj->{$this->columnaPermiso} = $request->permiso;
            $obj->save();

            DB::commit();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Permiso creado correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al agregar permiso: ' . $e->getMessage());
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar el permiso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'El permiso no existe o ya ha sido eliminado');
        }

        $permiso = $this->modelo::find($request->id);

        if ($permiso && $permiso->roles && $permiso->roles->count() > 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'No se puede eliminar el permiso porque está siendo utilizado por ' . $permiso->roles->count() . ' rol(es)');
        }

        DB::beginTransaction();
        try {
            $this->modelo::destroy($request->id);
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar el permiso: ' . $e->getMessage());
        }
    }

    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un permiso para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        // Verificar que ninguno tenga roles asociados
        $permisosConRoles = $this->modelo::whereIn($this->columnaIdPermiso, $ids)
            ->whereHas('roles')
            ->count();

        if ($permisosConRoles > 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Alguno de los permisos seleccionados está siendo utilizado por roles y no puede eliminarse');
        }

        DB::beginTransaction();
        try {
            $this->modelo::whereIn($this->columnaIdPermiso, $ids)->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar los permisos: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso,
            'permiso' => [
                'required', 'string', 'min:3', 'max:120',
                'unique:' . $this->tablaPermiso . ',' . $this->columnaPermiso . ',' . $request->id . ',' . $this->columnaIdPermiso,
            ],
        ], [
            'id.required' => 'ID del permiso es requerido',
            'id.exists' => 'El permiso no existe',
            'permiso.required' => 'El nombre del permiso es obligatorio',
            'permiso.string' => 'El nombre del permiso debe ser texto',
            'permiso.min' => 'El nombre del permiso debe tener al menos 3 caracteres',
            'permiso.max' => 'El nombre del permiso no puede exceder los 120 caracteres',
            'permiso.unique' => 'Este nombre de permiso ya está registrado',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $obj = $this->modelo::find($request->id);

            if (!$obj) {
                DB::rollback();
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'El permiso no existe');
            }

            $obj->{$this->columnaPermiso} = $request->permiso;
            $obj->save();

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar el permiso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        DB::beginTransaction();
        try {
            $permisosConRoles = $this->modelo::whereHas('roles')->count();

            if ($permisosConRoles > 0) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'No se pueden eliminar todos los permisos porque algunos están en uso');
            }

            $this->modelo::query()->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar los permisos: ' . $e->getMessage());
        }
    }

    public function exportarCsv()
    {
        $permisos = $this->modelo::orderBy($this->columnaPermiso)->get();

        $nombreArchivo = 'permisos_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($permisos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Permiso'], ';');
            foreach ($permisos as $p) {
                fputcsv($out, [$p->{$this->columnaPermiso}], ';');
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}