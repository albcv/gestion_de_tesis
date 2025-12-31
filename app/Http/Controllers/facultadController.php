<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Facultad;
use Illuminate\Support\Facades\Validator;

class facultadController extends Controller
{
    protected $modelo = Facultad::class;
    protected $rutaVista = 'gestionarFacultad';
    protected $columnaNombre = 'Nombre_facultad';
    protected $columnaSiglas = 'Siglas';
    protected $columnaId = 'idFacultad';
    
    protected $tablaFacultad;
    protected $columnaIdPrimaria;

    public function __construct()
    {
        $instanciaFacultad = new $this->modelo;
        
        $this->tablaFacultad = $instanciaFacultad->getTable();
        $this->columnaIdPrimaria = $instanciaFacultad->getKeyName();
    }

    public function mostrar()
    {
        try {
            $facultades = $this->modelo::all();
            return view('gestionar.gestionarFacultad', compact('facultades'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las facultades: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre_facultad' => 'required|string|min:20|max:100',
                'siglas' => 'required|string|min:3|max:10',
            ], [
                'nombre_facultad.required' => 'El nombre de la facultad es obligatorio',
                'nombre_facultad.string' => 'El nombre debe ser una cadena de texto',
                'nombre_facultad.min' => 'El nombre debe tener al menos 20 caracteres',
                'nombre_facultad.max' => 'El nombre no puede exceder 100 caracteres',
                'siglas.required' => 'Las siglas son obligatorias',
                'siglas.string' => 'Las siglas deben ser una cadena de texto',
                'siglas.min' => 'Las siglas deben tener al menos 3 caracteres',
                'siglas.max' => 'Las siglas no pueden exceder 10 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe una facultad con el mismo nombre
            $existenteNombre = $this->modelo::where($this->columnaNombre, $request->nombre_facultad)->first();
            if ($existenteNombre) {
                return redirect()->back()
                    ->with('error', 'Ya existe una facultad con ese nombre')
                    ->withInput();
            }

            // Verificar si ya existen las mismas siglas
            $existenteSiglas = $this->modelo::where($this->columnaSiglas, $request->siglas)->first();
            if ($existenteSiglas) {
                return redirect()->back()
                    ->with('error', 'Ya existen esas siglas para otra facultad')
                    ->withInput();
            }

            $facultad = new $this->modelo;
            $facultad->{$this->columnaNombre} = $request->nombre_facultad;
            $facultad->{$this->columnaSiglas} = $request->siglas;
            $facultad->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'Facultad agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la facultad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFacultad . ',' . $this->columnaIdPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La facultad no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'Facultad eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la facultad: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaFacultad . ',' . $this->columnaIdPrimaria,
                'nombre_facultad' => 'required|string|min:20|max:100',
                'siglas' => 'required|string|min:3|max:10',
            ], [
                'nombre_facultad.required' => 'El nombre de la facultad es obligatorio',
                'nombre_facultad.string' => 'El nombre debe ser una cadena de texto',
                'nombre_facultad.min' => 'El nombre debe tener al menos 20 caracteres',
                'nombre_facultad.max' => 'El nombre no puede exceder 100 caracteres',
                'siglas.required' => 'Las siglas son obligatorias',
                'siglas.string' => 'Las siglas deben ser una cadena de texto',
                'siglas.min' => 'Las siglas deben tener al menos 3 caracteres',
                'siglas.max' => 'Las siglas no pueden exceder 10 caracteres',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra facultad con el mismo nombre (excluyendo la actual)
            $existenteNombre = $this->modelo::where($this->columnaNombre, $request->nombre_facultad)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->first();
            if ($existenteNombre) {
                return redirect()->back()
                    ->with('error', 'Ya existe otra facultad con ese nombre')
                    ->withInput();
            }

            // Verificar si ya existen las mismas siglas para otra facultad (excluyendo la actual)
            $existenteSiglas = $this->modelo::where($this->columnaSiglas, $request->siglas)
                ->where($this->columnaIdPrimaria, '!=', $request->id)
                ->first();
            if ($existenteSiglas) {
                return redirect()->back()
                    ->with('error', 'Ya existen esas siglas para otra facultad')
                    ->withInput();
            }

            $facultad = $this->modelo::find($request->id);
            if ($facultad) {
                $facultad->{$this->columnaNombre} = $request->nombre_facultad;
                $facultad->{$this->columnaSiglas} = $request->siglas;
                $facultad->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Facultad modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la facultad a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la facultad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las facultades han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las facultades: ' . $e->getMessage()
            ]);
        }
    }
}