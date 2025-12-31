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
        $instanciaNoConformidades = new $this->modelo;
        
        $this->tablaNoConformidades = $instanciaNoConformidades->getTable();
        $this->columnaIdPrimaria = $instanciaNoConformidades->getKeyName();
    }

    public function mostrar()
    {
        try {
            $ncs = $this->modelo::all();
            return view('gestionar.gestionarNoConformidades', compact('ncs'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las no conformidades: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'deficiencias_detectadas' => 'required|string|min:10|max:500',
            ], [
                'deficiencias_detectadas.required' => 'Las deficiencias detectadas son obligatorias',
                'deficiencias_detectadas.string' => 'Las deficiencias detectadas deben ser texto',
                'deficiencias_detectadas.min' => 'Las deficiencias detectadas deben tener al menos 10 caracteres',
                'deficiencias_detectadas.max' => 'Las deficiencias detectadas no pueden exceder los 500 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe una no conformidad con las mismas deficiencias
            $existente = $this->modelo::where($this->columnaDeficiencias, $request->deficiencias_detectadas)->first();
            
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una no conformidad con esas deficiencias detectadas')
                    ->withInput();
            }

            $nc = new $this->modelo();
            $nc->{$this->columnaDeficiencias} = $request->deficiencias_detectadas;
            $nc->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'No conformidad agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la no conformidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaNoConformidades . ',' . $this->columnaIdPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La no conformidad no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'No conformidad eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la no conformidad: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaNoConformidades . ',' . $this->columnaIdPrimaria,
                'deficiencias_detectadas' => 'required|string|min:10|max:500',
            ], [
                'deficiencias_detectadas.required' => 'Las deficiencias detectadas son obligatorias',
                'deficiencias_detectadas.string' => 'Las deficiencias detectadas deben ser texto',
                'deficiencias_detectadas.min' => 'Las deficiencias detectadas deben tener al menos 10 caracteres',
                'deficiencias_detectadas.max' => 'Las deficiencias detectadas no pueden exceder los 500 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra no conformidad con las mismas deficiencias (excluyendo la actual)
            $existente = $this->modelo::where($this->columnaDeficiencias, $request->deficiencias_detectadas)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->first();
            
            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe otra no conformidad con esas deficiencias detectadas')
                    ->withInput();
            }

            $nc = $this->modelo::find($request->id);
            if ($nc) {
                $nc->{$this->columnaDeficiencias} = $request->deficiencias_detectadas;
                $nc->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'No conformidad modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la no conformidad a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la no conformidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las no conformidades han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las no conformidades: ' . $e->getMessage()
            ]);
        }
    }
}