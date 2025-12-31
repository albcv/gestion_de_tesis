<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\grupos;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Exception;

class gruposController extends Controller
{
    protected $modelo = grupos::class;
    protected $rutaVista = 'gestionarGrupos';
    protected $columnaNumero = 'número';

    public function mostrar()
    {
        try {
        
            $objetos = $this->modelo::orderBy('número', 'asc')->get();
            return view('gestionar.gestionarGrupos', compact('objetos'));
        } catch (Exception $e) {
            return redirect()->route($this->rutaVista)->withErrors([
                'error' => 'Error al cargar los grupos. Por favor, intente nuevamente.'
            ]);
        }
    }

    public function agregar(Request $request)
    {
        try {
            // Validación más robusta
            $request->validate([
                'número' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:999',
                    Rule::unique('grupos', 'número')
                ],
            ], [
                'número.required' => 'El número del grupo es obligatorio.',
                'número.integer' => 'El número del grupo debe ser un número entero.',
                'número.min' => 'El número del grupo debe ser al menos 1.',
                'número.max' => 'El número del grupo no puede ser mayor a 999.',
                'número.unique' => 'Ya existe un grupo con este número.',
            ]);

            // Transacción para asegurar integridad
            DB::beginTransaction();

            $obj = new $this->modelo();
            $obj->{$this->columnaNumero} = $request->número;
            $obj->save();

            DB::commit();

            return redirect(route($this->rutaVista))->with('success', 'Grupo agregado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Error al agregar el grupo. Por favor, intente nuevamente.'
            ])->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            // Validar que el ID existe
            $request->validate([
                'id' => 'required|exists:grupos,id',
            ], [
                'id.required' => 'El ID del grupo es obligatorio.',
                'id.exists' => 'El grupo seleccionado no existe.',
            ]);

            $id = $request['id'];
            
            // Verificar si hay estudiantes asociados a este grupo
            $tieneEstudiantes = DB::table('estudiantes')->where('id_grupo', $id)->exists();
            
            if ($tieneEstudiantes) {
                return redirect()->back()->withErrors([
                    'error' => 'No se puede eliminar el grupo porque tiene estudiantes asociados.'
                ]);
            }

            DB::beginTransaction();
            
            $this->modelo::destroy($id);
            
            DB::commit();

            return redirect(route($this->rutaVista))->with('success', 'Grupo eliminado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Error al eliminar el grupo. Por favor, intente nuevamente.'
            ]);
        }
    }

    public function modificar(Request $request)
    {
        try {
            // Validación más robusta
            $request->validate([
                'id' => 'required|exists:grupos,id',
                'número' => [
                    'required',
                    'integer',
                    'min:1',
                    'max:999',
                    Rule::unique('grupos', 'número')->ignore($request->id)
                ],
            ], [
                'id.required' => 'El ID del grupo es obligatorio.',
                'id.exists' => 'El grupo seleccionado no existe.',
                'número.required' => 'El número del grupo es obligatorio.',
                'número.integer' => 'El número del grupo debe ser un número entero.',
                'número.min' => 'El número del grupo debe ser al menos 1.',
                'número.max' => 'El número del grupo no puede ser mayor a 999.',
                'número.unique' => 'Ya existe otro grupo con este número.',
            ]);

            DB::beginTransaction();

            $grupo = $this->modelo::findOrFail($request->id);
            
            $grupo->{$this->columnaNumero} = $request->número;
            $grupo->save();

            DB::commit();

            return redirect(route($this->rutaVista))->with('success', 'Grupo actualizado correctamente.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->withErrors([
                'error' => 'El grupo que intenta modificar no existe.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Error al modificar el grupo. Por favor, intente nuevamente.'
            ])->withInput();
        }
    }

    public function vaciar()
    {
        try {
            // Verificar si hay estudiantes asociados a cualquier grupo
            $tieneEstudiantes = DB::table('estudiantes')->exists();
            
            if ($tieneEstudiantes) {
                return response()->json([
                    'success' => false, 
                    'error' => 'No se pueden eliminar todos los grupos porque hay estudiantes asociados.'
                ], 400);
            }

            DB::beginTransaction();
            
            $this->modelo::query()->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Todos los grupos han sido eliminados correctamente.'
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'error' => 'Error al eliminar los grupos. Por favor, intente nuevamente.'
            ], 500);
        }
    }
}