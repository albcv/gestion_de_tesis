<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FechaEntregaFundamentacion;
use App\Models\FechaEntregaCorte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class fechaEntregaController extends Controller
{
    public function index()
    {
        try {
            $fechaFundamentacion = FechaEntregaFundamentacion::first();
            $fechasCortes = FechaEntregaCorte::orderBy('numero_corte')->get();
            
            return view('gestionar.fechaEntrega', compact('fechaFundamentacion', 'fechasCortes'));
            
        } catch (\Exception $e) {
            return redirect()->route('inicio')
                ->with('error', 'Error al cargar las fechas de entrega: ' . $e->getMessage());
        }
    }

    public function actualizarFundamentacion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_entrega' => 'required|date|after_or_equal:today',
            ], [
                'fecha_entrega.required' => 'La fecha de entrega es obligatoria',
                'fecha_entrega.date' => 'La fecha debe tener un formato válido',
                'fecha_entrega.after_or_equal' => 'La fecha no puede ser pasada'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            if ($request->has('id')) {
                // Actualizar fecha existente
                $fecha = FechaEntregaFundamentacion::find($request->id);
                if ($fecha) {
                    $fecha->fecha_entrega = $request->fecha_entrega;
                    $fecha->save();
                    
                    DB::commit();
                    return redirect()->route('fechaEntrega')
                        ->with('success', 'Fecha de fundamentación actualizada correctamente');
                }
            } else {
                // Crear nueva fecha
                FechaEntregaFundamentacion::create([
                    'fecha_entrega' => $request->fecha_entrega
                ]);
                
                DB::commit();
                return redirect()->route('fechaEntrega')
                    ->with('success', 'Fecha de fundamentación establecida correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se pudo actualizar la fecha');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la fecha: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function actualizarCorte(Request $request, $numeroCorte)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_entrega' => 'required|date|after_or_equal:today',
            ], [
                'fecha_entrega.required' => 'La fecha de entrega es obligatoria',
                'fecha_entrega.date' => 'La fecha debe tener un formato válido',
                'fecha_entrega.after_or_equal' => 'La fecha no puede ser pasada'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Validar que el número de corte sea válido
            if ($numeroCorte < 1 || $numeroCorte > 4) {
                return redirect()->back()
                    ->with('error', 'Número de corte inválido');
            }

            DB::beginTransaction();

            if ($request->has('id')) {
                // Actualizar fecha existente
                $fecha = FechaEntregaCorte::find($request->id);
                if ($fecha) {
                    $fecha->fecha_entrega = $request->fecha_entrega;
                    $fecha->save();
                    
                    DB::commit();
                    return redirect()->route('fechaEntrega')
                        ->with('success', "Fecha del corte {$numeroCorte} actualizada correctamente");
                }
            } else {
                // Crear nueva fecha
                FechaEntregaCorte::create([
                    'numero_corte' => $numeroCorte,
                    'fecha_entrega' => $request->fecha_entrega
                ]);
                
                DB::commit();
                return redirect()->route('fechaEntrega')
                    ->with('success', "Fecha del corte {$numeroCorte} establecida correctamente");
            }

            return redirect()->back()
                ->with('error', 'No se pudo actualizar la fecha');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al actualizar la fecha: ' . $e->getMessage())
                ->withInput();
        }
    }

    

    public function reiniciarFechas()
    {
        try {
            DB::beginTransaction();

            // Eliminar todas las fechas
            FechaEntregaFundamentacion::query()->delete();
            FechaEntregaCorte::query()->delete();

            DB::commit();

            return redirect()->route('fechaEntrega')
                ->with('success', 'Todas las fechas han sido eliminadas correctamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al eliminar las fechas: ' . $e->getMessage());
        }
    }
}