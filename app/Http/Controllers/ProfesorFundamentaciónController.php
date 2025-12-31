<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\profesorFundamentación; 
use App\Models\fundamentaciones;     
use App\Models\Profesor;              

class ProfesorFundamentaciónController extends Controller
{
    public function mostrarVincular($idFundamentacion)
    {
        try {
            $fundamentacion = fundamentaciones::with(['tesis', 'profesores'])->findOrFail($idFundamentacion);
            
            // Obtener profesores que no están ya vinculados a esta fundamentación
            $profesoresVinculadosIds = $fundamentacion->profesores->pluck('id')->toArray();
            $profesoresDisponibles = Profesor::whereNotIn('id', $profesoresVinculadosIds)
                ->with('departamento')
                ->get();
                
            return view('gestionar.gestionarProfesorFundamentación.vincularProfesorFundamentación', 
                compact('fundamentacion', 'profesoresDisponibles'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function vincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fundamentacion_id' => 'required|exists:fundamentaciones,id_fundamentacion',
                'profesor_id' => 'required|exists:profesor,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe la relación
            $existente = profesorFundamentación::where('id_fundamentacion', $request->fundamentacion_id)
                ->where('id_profesor', $request->profesor_id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Este profesor ya está vinculado a esta fundamentación')
                    ->withInput();
            }

            // Crear la relación
            $relacion = new profesorFundamentación();
            $relacion->id_fundamentacion = $request->fundamentacion_id;
            $relacion->id_profesor = $request->profesor_id;
            $relacion->save();

            return redirect()->route('verFundamentación', ['id' => $request->fundamentacion_id])
                ->with('success', 'Profesor vinculado correctamente a la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al vincular el profesor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function desvincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fundamentacion_id' => 'required|exists:fundamentaciones,id_fundamentacion',
                'profesor_id' => 'required|exists:profesor,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Datos inválidos para desvincular');
            }

            // Buscar y eliminar la relación
            $relacion = profesorFundamentación::where('id_fundamentacion', $request->fundamentacion_id)
                ->where('id_profesor', $request->profesor_id)
                ->first();

            if (!$relacion) {
                return redirect()->back()->with('error', 'No se encontró la relación profesor-fundamentación');
            }

            $relacion->delete();

            return redirect()->back()->with('success', 'Profesor desvinculado correctamente de la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al desvincular el profesor: ' . $e->getMessage());
        }
    }
}