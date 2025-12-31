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

    public function mostrar()
    {
        try {
            $objetos = $this->modelo::all();
            return view('gestionar.gestionarPermisos', compact('objetos'));
            
        } catch (\Exception $e) {
            Log::error('Error al mostrar permisos: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al cargar los permisos: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'permiso' => [
                    'required',
                    'string',
                    'min:3',
                    'max:120',
                    'unique:' . $this->tablaPermiso . ',' . $this->columnaPermiso
                ],
            ], [
                'permiso.required' => 'El nombre del permiso es obligatorio',
                'permiso.string' => 'El nombre del permiso debe ser texto',
                'permiso.min' => 'El nombre del permiso debe tener al menos 3 caracteres',
                'permiso.max' => 'El nombre del permiso no puede exceder los 120 caracteres',
                'permiso.unique' => 'Este nombre de permiso ya está registrado',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $obj = new $this->modelo();
            $obj->{$this->columnaPermiso} = $request->permiso;
            $obj->save();
            
            DB::commit();
            
            Log::info('Permiso creado exitosamente', [
                'permiso_id' => $obj->id,
                'permiso_nombre' => $obj->permiso
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Permiso agregado correctamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al agregar permiso: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request_data' => $request->except('_token'),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al agregar el permiso: ' . $e->getMessage())
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
                    'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso
                ],
            ], [
                'id.required' => 'ID del permiso es requerido',
                'id.exists' => 'El permiso no existe o ya ha sido eliminado',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El permiso no existe o ya ha sido eliminado');
            }
            
            $id = $request['id'];
            $permiso = $this->modelo::find($id);
            
            if (!$permiso) {
                return redirect()->back()
                    ->with('error', 'El permiso no existe');
            }
            
            // Guardar información para logs
            $permisoData = $permiso->toArray();
            
            // Verificar si el permiso está siendo utilizado por algún rol
            if ($permiso->roles && $permiso->roles->count() > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede eliminar el permiso porque está siendo utilizado por ' . 
                           $permiso->roles->count() . ' rol(es)');
            }
            
            // Eliminar el permiso
            $this->modelo::destroy($id);
            
            DB::commit();
            
            Log::info('Permiso eliminado exitosamente', [
                'permiso_id' => $id,
                'permiso_data' => $permisoData
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Permiso eliminado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al eliminar permiso: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request' => $request->all(),
                'permiso_id' => $request->id ?? null,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al eliminar el permiso: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'id' => [
                    'required',
                    'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso
                ],
                'permiso' => [
                    'required',
                    'string',
                    'min:3',
                    'max:120',
                    'unique:' . $this->tablaPermiso . ',' . $this->columnaPermiso . ',' . 
                    $request->id . ',' . $this->columnaIdPermiso
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            $obj = $this->modelo::find($request->id);
            
            if (!$obj) {
                return redirect()->back()
                    ->with('error', 'El permiso no existe')
                    ->withInput();
            }
            
            // Guardar datos antiguos para log
            $oldData = $obj->toArray();
            
            $obj->{$this->columnaPermiso} = $request->permiso;
            $obj->save();
            
            DB::commit();
            
            Log::info('Permiso actualizado exitosamente', [
                'permiso_id' => $obj->id,
                'old_data' => $oldData,
                'new_data' => $obj->toArray()
            ]);
            
            return redirect(route($this->rutaVista))
                ->with('success', 'Permiso actualizado correctamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al modificar permiso: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request_data' => $request->except('_token'),
                'permiso_id' => $request->id ?? null,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al modificar el permiso: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        DB::beginTransaction();
        
        try {
            // Obtener información antes de eliminar para logs
            $permisos = $this->modelo::all();
            $permisosData = $permisos->map(function($permiso) {
                return [
                    'id' => $permiso->id,
                    'nombre' => $permiso->permiso,
                    'roles_asociados' => $permiso->roles ? $permiso->roles->count() : 0
                ];
            })->toArray();
            
            // Verificar si hay permisos asociados a roles
            $permisosConRoles = $permisos->filter(function($permiso) {
                return $permiso->roles && $permiso->roles->count() > 0;
            });
            
            if ($permisosConRoles->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pueden eliminar todos los permisos porque ' . 
                              $permisosConRoles->count() . ' permiso(s) están siendo utilizados por roles'
                ], 400);
            }
            
            // Eliminar todos los permisos
            $this->modelo::query()->delete();
            
            DB::commit();
            
            Log::info('Todos los permisos han sido eliminados', [
                'method' => __METHOD__,
                'permisos_eliminados' => $permisosData,
                'total_permisos' => count($permisosData)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Todos los permisos han sido eliminados correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error al vaciar permisos: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar los permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function obtenerPermiso($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'exists:' . $this->tablaPermiso . ',' . $this->columnaIdPermiso
                ],
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El permiso no existe'
                ], 404);
            }
            
            $permiso = $this->modelo::find($id);
            
            if ($permiso) {
                return response()->json([
                    'success' => true,
                    'permiso' => $permiso
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener permiso: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'permiso_id' => $id,
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    public function buscar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'buscar' => 'nullable|string|min:1|max:120'
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Término de búsqueda inválido');
            }
            
            $buscar = $request->input('buscar');
            $porPagina = $request->input('por_pagina', 10);
            
            $query = $this->modelo::query();
            
            if ($buscar) {
                $query->where($this->columnaPermiso, 'LIKE', "%{$buscar}%");
            }
            
            $objetos = $query->paginate($porPagina);
            
            return view('gestionar.gestionarPermisos', compact('objetos'));
            
        } catch (\Exception $e) {
            Log::error('Error al buscar permisos: ' . $e->getMessage(), [
                'method' => __METHOD__,
                'request' => $request->all(),
                'error_trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Error al buscar permisos: ' . $e->getMessage());
        }
    }
}