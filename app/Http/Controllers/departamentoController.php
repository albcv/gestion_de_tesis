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
    protected $nombreTabla;
    protected $columnaId;

    public function __construct()
    {
        $modeloInstancia = new $this->modelo;
        $this->nombreTabla = $modeloInstancia->getTable();
        $this->columnaId = $modeloInstancia->getKeyName();
    }

    public function mostrar()
    {
        try {
            $departamentos = $this->modelo::all();
            return view('gestionar.gestionarDepartamento', compact('departamentos'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar los departamentos: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'departamento' => 'required|string|min:10|max:100',
            ], [
                'departamento.required' => 'El nombre del departamento es obligatorio',
                'departamento.string' => 'El nombre debe ser una cadena de texto',
                'departamento.min' => 'El nombre debe tener al menos 10 caracteres',
                'departamento.max' => 'El nombre no puede exceder 100 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $departamento = new $this->modelo();
            $departamento->{$this->columnaNombre} = $request->departamento;
            $departamento->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'Departamento agregado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar el departamento: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->nombreTabla . ',' . $this->columnaId,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El departamento no existe o ya ha sido eliminado');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'Departamento eliminado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el departamento: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->nombreTabla . ',' . $this->columnaId,
                'departamento' => 'required|string|min:10|max:100',
            ], [
                'departamento.required' => 'El nombre del departamento es obligatorio',
                'departamento.string' => 'El nombre debe ser una cadena de texto',
                'departamento.min' => 'El nombre debe tener al menos 10 caracteres',
                'departamento.max' => 'El nombre no puede exceder 100 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $departamento = $this->modelo::find($request->id);
            if ($departamento) {
                $departamento->{$this->columnaNombre} = $request->departamento;
                $departamento->save();
            }

            return redirect(route($this->rutaVista))
                ->with('success', 'Departamento modificado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar el departamento: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todos los departamentos han sido eliminados correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar los departamentos: ' . $e->getMessage()
            ]);
        }
    }
}