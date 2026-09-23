<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\roles;
use App\Models\permisos;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class rolesController extends Controller
{
    protected $modelo;
    protected $modeloPermiso;
    protected $rutaVista = 'gestionarRoles';
    protected $columnaRol = 'rol';
    protected $relacionPermisos = 'permisos';

    protected $tablaRol;
    protected $tablaPermiso;
    protected $columnaIdRol;
    protected $columnaIdPermiso;

    public function __construct()
    {
        $this->modelo = roles::class;
        $this->modeloPermiso = permisos::class;

        $instanciaRol = new $this->modelo;
        $instanciaPermiso = new $this->modeloPermiso;

        $this->tablaRol = $instanciaRol->getTable();
        $this->tablaPermiso = $instanciaPermiso->getTable();

        $this->columnaIdRol = $instanciaRol->getKeyName();
        $this->columnaIdPermiso = $instanciaPermiso->getKeyName();
    }

    /**
     * Listado, formulario de creación o edición.
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            $permisos = $this->modeloPermiso::all();
            return view('gestionar.rol.formulario', compact('permisos'));
        }

        if ($accion === 'editar' && $id) {
            $rol = $this->modelo::with($this->relacionPermisos)->find($id);

            if (!$rol) {
                return redirect()
                    ->route($this->rutaVista)
                    ->with('error', 'El rol que intenta editar no existe');
            }

            $permisos = $this->modeloPermiso::all();
            return view('gestionar.rol.formulario', compact('rol', 'permisos'));
        }

        $objetos = $this->modelo::with($this->relacionPermisos)->get();
        return view('gestionar.rol.index', compact('objetos'));
    }

    /**
     * Agrega un nuevo rol.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'rol' => [
                'required', 'string', 'min:3', 'max:120',
                'unique:' . $this->tablaRol . ',' . $this->columnaRol,
            ],
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso,
        ], [
            'rol.required' => 'El nombre del rol es obligatorio',
            'rol.string' => 'El nombre del rol debe ser texto',
            'rol.min' => 'El nombre del rol debe tener al menos 3 caracteres',
            'rol.max' => 'El nombre del rol no puede exceder los 120 caracteres',
            'rol.unique' => 'Este nombre de rol ya está registrado',
            'permisos.array' => 'Los permisos deben ser un arreglo válido',
            'permisos.*.exists' => 'Uno o más permisos seleccionados no existen',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $obj = new $this->modelo();
            $obj->{$this->columnaRol} = $request->rol;
            $obj->save();

            if ($request->has('permisos') && is_array($request->permisos)) {
                $obj->{$this->relacionPermisos}()->sync($request->permisos);
            }

            DB::commit();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Rol creado correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al agregar rol: ' . $e->getMessage());
            return redirect($urlFormCrear)
                ->with('error', 'Error al agregar el rol: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Elimina un rol por ID.
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:' . $this->tablaRol . ',' . $this->columnaIdRol,
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'El rol no existe o ya ha sido eliminado');
        }

        DB::beginTransaction();
        try {
            $rol = $this->modelo::find($request->id);
            if ($rol) {
                $rol->{$this->relacionPermisos}()->detach();
                $rol->delete();
            }
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Elimina varios roles a la vez.
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un rol para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        DB::beginTransaction();
        try {
            $roles = $this->modelo::whereIn($this->columnaIdRol, $ids)->get();
            foreach ($roles as $rol) {
                $rol->{$this->relacionPermisos}()->detach();
                $rol->delete();
            }
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al eliminar los roles: ' . $e->getMessage());
        }
    }

    /**
     * Modifica un rol existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, [
            'accion' => 'editar',
            'id'     => $request->id,
        ]);

        $validator = Validator::make($request->all(), [
            'id'  => 'required|exists:' . $this->tablaRol . ',' . $this->columnaIdRol,
            'rol' => [
                'required', 'string', 'min:3', 'max:120',
                'unique:' . $this->tablaRol . ',' . $this->columnaRol . ',' . $request->id . ',' . $this->columnaIdRol,
            ],
            'permisos' => 'nullable|array',
            'permisos.*' => 'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso,
        ], [
            'id.required' => 'ID del rol es requerido',
            'id.exists' => 'El rol no existe',
            'rol.required' => 'El nombre del rol es obligatorio',
            'rol.string' => 'El nombre del rol debe ser texto',
            'rol.min' => 'El nombre del rol debe tener al menos 3 caracteres',
            'rol.max' => 'El nombre del rol no puede exceder los 120 caracteres',
            'rol.unique' => 'Este nombre de rol ya está registrado',
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
                    ->with('error', 'El rol no existe');
            }

            $obj->{$this->columnaRol} = $request->rol;
            $obj->save();

            if ($request->has('permisos') && is_array($request->permisos)) {
                $obj->{$this->relacionPermisos}()->sync($request->permisos);
            } else {
                $obj->{$this->relacionPermisos}()->detach();
            }

            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar el rol: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Vacía la tabla de roles.
     */
    public function vaciar()
    {
        DB::beginTransaction();
        try {
            $roles = $this->modelo::all();
            foreach ($roles as $rol) {
                $rol->{$this->relacionPermisos}()->detach();
            }
            $this->modelo::query()->delete();
            DB::commit();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVista)
                ->with('error', 'Error al vaciar los roles: ' . $e->getMessage());
        }
    }

    /**
     * Exporta los roles a CSV (incluye permisos como texto).
     */
    public function exportarCsv()
    {
        $roles = $this->modelo::with($this->relacionPermisos)->orderBy($this->columnaRol)->get();

        $nombreArchivo = 'roles_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($roles) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Rol', 'Permisos'], ';');

            foreach ($roles as $r) {
                $permisosTexto = $r->{$this->relacionPermisos}
                    ->pluck('permiso')
                    ->implode(', ');

                fputcsv($out, [
                    $r->{$this->columnaRol},
                    $permisosTexto,
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}