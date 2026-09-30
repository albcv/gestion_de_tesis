<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\profesorFundamentación;
use App\Models\fundamentaciones;
use App\Models\Profesor;

class ProfesorFundamentaciónController extends Controller
{
    /**
     * Muestra la vista para vincular uno o varios profesores.
     */
    public function mostrarVincular($idFundamentacion)
    {
        try {
            $fundamentacion = fundamentaciones::with(['tesis', 'profesores'])
                ->findOrFail($idFundamentacion);

            // Profesores ya vinculados a esta fundamentación
            $profesoresVinculadosIds = $fundamentacion->profesores
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->toArray();

            // Profesores que aún NO están vinculados
            $profesoresDisponibles = Profesor::whereNotIn('id', $profesoresVinculadosIds)
                ->with(['departamento'])
                ->orderBy('Nombre_profesor')
                ->orderBy('Apellido1')
                ->get();

            return view('gestionar.fundamentacion.asignarOponente',
                compact('fundamentacion', 'profesoresDisponibles'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    /**
     * Vincula uno o varios profesores a la fundamentación.
     * Acepta `profesor_id` como escalar o como array (`profesor_id[]`).
     */
    public function vincular(Request $request)
    {
        // ---------- Normalizar profesor_id a array de enteros ----------
        $raw = $request->input('profesor_id', []);
        if (!is_array($raw)) {
            $raw = [$raw];
        }

        $idsProfesores = array_values(array_unique(
            array_filter(array_map('intval', $raw), fn($id) => $id > 0)
        ));

        // ---------- Validación ----------
        $validator = Validator::make([
            'fundamentacion_id' => $request->input('fundamentacion_id'),
            'profesor_id'       => $idsProfesores,
        ], [
            'fundamentacion_id' => 'required|exists:fundamentaciones,id_fundamentacion',
            'profesor_id'       => 'required|array|min:1',
            'profesor_id.*'     => 'integer|exists:profesor,id',
        ], [
            'fundamentacion_id.required' => 'La fundamentación es obligatoria',
            'fundamentacion_id.exists'   => 'La fundamentación no existe',
            'profesor_id.required'       => 'Debe seleccionar al menos un profesor',
            'profesor_id.min'            => 'Debe seleccionar al menos un profesor',
            'profesor_id.*.exists'       => 'Uno de los profesores seleccionados no existe',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $idFundamentacion = (int) $request->input('fundamentacion_id');

        try {
            DB::beginTransaction();

            // ---------- Filtrar los que ya están vinculados ----------
            $yaVinculados = profesorFundamentación::where('id_fundamentacion', $idFundamentacion)
                ->whereIn('id_profesor', $idsProfesores)
                ->pluck('id_profesor')
                ->map(fn($v) => (int) $v)
                ->toArray();

            $idsNuevos = array_values(array_diff($idsProfesores, $yaVinculados));

            if (empty($idsNuevos)) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'El/los profesor(es) seleccionado(s) ya está(n) vinculado(s) a esta fundamentación.')
                    ->withInput();
            }

            // ---------- Insertar ----------
            $insertados = 0;
            foreach ($idsNuevos as $idProfesor) {
                $relacion = new profesorFundamentación();
                $relacion->id_fundamentacion = $idFundamentacion;
                $relacion->id_profesor       = $idProfesor;
                $relacion->save();
                $insertados++;
            }

            DB::commit();

            $mensaje = $insertados === 1
                ? 'Profesor vinculado correctamente a la fundamentación'
                : $insertados . ' profesores vinculados correctamente a la fundamentación';

            if ($insertados < count($idsProfesores)) {
                $mensaje .= ' (' . (count($idsProfesores) - $insertados) . ' ya estaban vinculados)';
            }

            return redirect()->route('verFundamentación', ['id' => $idFundamentacion])
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al vincular los profesores: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Desvincula un profesor de la fundamentación.
     */
    public function desvincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fundamentacion_id' => 'required|exists:fundamentaciones,id_fundamentacion',
                'profesor_id'       => 'required|exists:profesor,id',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Datos inválidos para desvincular');
            }

            $relacion = profesorFundamentación::where('id_fundamentacion', $request->fundamentacion_id)
                ->where('id_profesor', $request->profesor_id)
                ->first();

            if (!$relacion) {
                return redirect()->back()->with('error', 'No se encontró la relación profesor-fundamentación');
            }

            $relacion->delete();

            return redirect()->back()->with('success', 'Profesor desvinculado correctamente de la fundamentación');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desvincular el profesor: ' . $e->getMessage());
        }
    }
}