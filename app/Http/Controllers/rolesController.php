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

    public function mostrar()
    {
        try {
            $objetos = $this->modelo::with($this->relacionPermisos)->get();
            $permisos = $this->modeloPermiso::all();

            return view('gestionar.gestionarRoles', compact('objetos', 'permisos'));
            
        } catch (\Exception $e) {
            Log::error('Error al mostrar roles: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al cargar los roles: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'rol' => [
                    'required',
                    'string',
                    'min:3',
                    'max:120',
                    'unique:' . $this->tablaRol . ',' . $this->columnaRol
                ],
                'permisos' => 'nullable|array',
                'permisos.*' => [
                    'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso
                ],
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $obj = new $this->modelo();
            $obj->{$this->columnaRol} = $request->rol;
            $obj->save();
            
            // Asignar permisos
            if ($request->has('permisos') && is_array($request->permisos)) {
                $obj->{$this->relacionPermisos}()->sync($request->permisos);
            }
            
            DB::commit();
            
            Log::info('Rol creado exitosamente', [
                'rol_id' => $obj->id,
                'rol_nombre' => $obj->rol,
                'permisos_asignados' => $request->permisos ?? []
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Rol agregado correctamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al agregar rol: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request_data' => $request->except('_token'),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al agregar el rol: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'exists:' . $this->tablaRol . ',' . $this->columnaIdRol
                ],
            ], [
                'id.required' => 'ID del rol es requerido',
                'id.exists' => 'El rol no existe o ya ha sido eliminado',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El rol no existe o ya ha sido eliminado');
            }
            
            $id = $request['id'];
            $rol = $this->modelo::find($id);
            
            if (!$rol) {
                return redirect()->back()
                    ->with('error', 'El rol no existe');
            }
            
            // Guardar información para logs
            $rolData = $rol->toArray();
            $permisosAsociados = $rol->permisos->pluck('id')->toArray();
            
            // Eliminar relaciones primero (si es necesario)
            $rol->{$this->relacionPermisos}()->detach();
            
            // Eliminar el rol
            $this->modelo::destroy($id);
            
            DB::commit();
            
            Log::info('Rol eliminado exitosamente', [
                'rol_id' => $id,
                'rol_data' => $rolData,
                'permisos_asociados' => $permisosAsociados
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Rol eliminado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al eliminar rol: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request' => $request->all(),
                'rol_id' => $request->id ?? null,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'exists:' . $this->tablaRol . ',' . $this->columnaIdRol
                ],
                'rol' => [
                    'required',
                    'string',
                    'min:3',
                    'max:120',
                    'unique:' . $this->tablaRol . ',' . $this->columnaRol . ',' . $request->id . ',' . $this->columnaIdRol
                ],
                'permisos' => 'nullable|array',
                'permisos.*' => [
                    'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso
                ],
            ], [
                'id.required' => 'ID del rol es requerido',
                'id.exists' => 'El rol no existe',
                'rol.required' => 'El nombre del rol es obligatorio',
                'rol.string' => 'El nombre del rol debe ser texto',
                'rol.min' => 'El nombre del rol debe tener al menos 3 caracteres',
                'rol.max' => 'El nombre del rol no puede exceder los 120 caracteres',
                'rol.unique' => 'Este nombre de rol ya está registrado',
                'permisos.array' => 'Los permisos deben ser un arreglo válido',
                'permisos.*.exists' => 'Uno o más permisos seleccionados no existen',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $obj = $this->modelo::find($request->id);
            
            if (!$obj) {
                return redirect()->back()
                    ->with('error', 'El rol no existe')
                    ->withInput();
            }
            
            // Guardar datos antiguos para log
            $oldData = $obj->toArray();
            $oldPermisos = $obj->permisos->pluck('id')->toArray();
            
            $obj->{$this->columnaRol} = $request->rol;
            $obj->save();
            
            // Actualizar permisos
            if ($request->has('permisos') && is_array($request->permisos)) {
                $obj->{$this->relacionPermisos}()->sync($request->permisos);
            } else {
                // Si no se envían permisos, eliminar todos los existentes
                $obj->{$this->relacionPermisos}()->detach();
            }
            
            DB::commit();
            
            Log::info('Rol actualizado exitosamente', [
                'rol_id' => $obj->id,
                'old_data' => $oldData,
                'new_data' => $obj->toArray(),
                'old_permisos' => $oldPermisos,
                'new_permisos' => $request->permisos ?? []
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Rol actualizado correctamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al modificar rol: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request_data' => $request->except('_token'),
                'rol_id' => $request->id ?? null,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al modificar el rol: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function obtenerPermisosRol($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'exists:' . $this->tablaRol . ',' . $this->columnaIdRol
                ],
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El rol no existe'
                ], 404);
            }
            
            $rol = $this->modelo::with($this->relacionPermisos)->find($id);
            
            if ($rol) {
                return response()->json([
                    'success' => true,
                    'permisos' => $rol->{$this->relacionPermisos}->pluck('id')->toArray()
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Rol no encontrado'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener permisos del rol: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'rol_id' => $id,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos del rol: ' . $e->getMessage()
            ], 500);
        }
    }

    public function vaciar()
    {
        DB::beginTransaction();
        
        try {
            // Obtener información antes de eliminar para logs
            $roles = $this->modelo::all();
            $rolesData = $roles->map(function($rol) {
                return [
                    'id' => $rol->id,
                    'nombre' => $rol->rol,
                    'permisos' => $rol->permisos->pluck('id')->toArray()
                ];
            })->toArray();
            
            // Detachar permisos de todos los roles primero
            foreach ($roles as $rol) {
                $rol->{$this->relacionPermisos}()->detach();
            }
            
            // Eliminar todos los roles
            $this->modelo::query()->delete();
            
            DB::commit();
            
            Log::info('Todos los roles han sido eliminados', [
                'method' => __METHOD__,
                'roles_eliminados' => $rolesData,
                'total_roles' => count($rolesData)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Todos los roles han sido eliminados correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al vaciar roles: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar los roles: ' . $e->getMessage()
            ], 500);
        }
    }
}