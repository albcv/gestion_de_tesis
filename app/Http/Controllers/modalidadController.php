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
    
    protected $tablaModalidad;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instanciaModalidad = new $this->modelo;
        
        $this->tablaModalidad = $instanciaModalidad->getTable();
        $this->columnaIdPrimaria = $instanciaModalidad->getKeyName();
    }

    public function mostrar()
    {
        try {
            $modalidades = $this->modelo::all();
            return view('gestionar.gestionarModalidad', compact('modalidades'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las modalidades: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_modalidad' => 'required|string|min:10|max:50',
            ], [
                'nombre_modalidad.required' => 'El nombre de la modalidad es obligatorio',
                'nombre_modalidad.string' => 'El nombre debe ser una cadena de texto',
                'nombre_modalidad.min' => 'El nombre debe tener al menos 10 caracteres',
                'nombre_modalidad.max' => 'El nombre no puede exceder 50 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe una modalidad con el mismo nombre
            $existente = $this->modelo::where($this->columnaNombre, $request->nombre_modalidad)->first();
            
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una modalidad con ese nombre')
                    ->withInput();
            }

            $modalidad = new $this->modelo();
            $modalidad->{$this->columnaNombre} = $request->nombre_modalidad;
            $modalidad->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'Modalidad agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la modalidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La modalidad no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'Modalidad eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la modalidad: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdPrimaria,
                'nombre_modalidad' => 'required|string|min:10|max:50',
            ], [
                'nombre_modalidad.required' => 'El nombre de la modalidad es obligatorio',
                'nombre_modalidad.string' => 'El nombre debe ser una cadena de texto',
                'nombre_modalidad.min' => 'El nombre debe tener al menos 10 caracteres',
                'nombre_modalidad.max' => 'El nombre no puede exceder 50 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra modalidad con el mismo nombre (excluyendo la actual)
            $existente = $this->modelo::where($this->columnaNombre, $request->nombre_modalidad)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->first();
            
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe otra modalidad con ese nombre')
                    ->withInput();
            }

            $modalidad = $this->modelo::find($request->id);
            if ($modalidad) {
                $modalidad->{$this->columnaNombre} = $request->nombre_modalidad;
                $modalidad->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Modalidad modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la modalidad a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la modalidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las modalidades han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las modalidades: ' . $e->getMessage()
            ]);
        }
    }
}