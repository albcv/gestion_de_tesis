<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Tesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CambiarTesisController extends Controller
{
    /**
     * Muestra el formulario para crear o cambiar el nombre de la tesis.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->route('inicio')
                    ->with('error', 'No se encontró información del estudiante');
            }

            $tesis = Tesis::with('fundamentacion')
                ->where('id_estudiante', $estudiante->id)
                ->first();

            $tieneTesis = $tesis !== null;

            $fundamentacionAprobada = $tesis
                && $tesis->fundamentacion
                && $tesis->fundamentacion->aprobada;

            return view('estudiante.cambiarTesis', compact(
                'estudiante',
                'tesis',
                'tieneTesis',
                'fundamentacionAprobada'
            ));

        } catch (\Exception $e) {
            return redirect()->route('inicio')
                ->with('error', 'Error al cargar la página: ' . $e->getMessage());
        }
    }

    /**
     * Crea la tesis si no existe, o actualiza el nombre si ya existe.
     */
    public function guardar(Request $request)
    {
        try {
            $user = Auth::user();
            $estudiante = $user->estudiante;

            if (!$estudiante) {
                return redirect()->back()
                    ->with('error', 'No se encontró información del estudiante');
            }

            $tesisExistente = Tesis::where('id_estudiante', $estudiante->id)->first();

            // ---------- Reglas de validación ----------
            $reglasNombre = [
                'required',
                'string',
                'min:10',
                'max:300',
            ];

            if ($tesisExistente) {
                // Excluir la tesis actual de la regla unique
                $reglasNombre[] = 'unique:tesis,Nombre_trabajo,' . $tesisExistente->id;
            } else {
                $reglasNombre[] = 'unique:tesis,Nombre_trabajo';
            }

            $validator = Validator::make($request->all(), [
                'nombre_tesis' => $reglasNombre,
            ], [
                'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
                'nombre_tesis.string'   => 'El nombre de la tesis debe ser texto',
                'nombre_tesis.min'      => 'El nombre de la tesis debe tener al menos 10 caracteres',
                'nombre_tesis.max'      => 'El nombre de la tesis no puede exceder los 300 caracteres',
                'nombre_tesis.unique'   => 'Ya existe una tesis con ese nombre. Elige otro.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // ---------- Crear o actualizar ----------
            if ($tesisExistente) {
                $tesisExistente->Nombre_trabajo = $request->nombre_tesis;
                $tesisExistente->save();

                $mensaje = 'Nombre de la tesis actualizado correctamente';
            } else {
                $nuevaTesis = new Tesis();
                $nuevaTesis->id_estudiante   = $estudiante->id;
                $nuevaTesis->Nombre_trabajo  = $request->nombre_tesis;
                $nuevaTesis->save();

                $mensaje = 'Tesis creada correctamente. Ya puedes subir tu fundamentación.';
            }

            return redirect()->route('cambiarTesis')->with('success', $mensaje);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al guardar la tesis: ' . $e->getMessage())
                ->withInput();
        }
    }
}