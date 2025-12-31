<?php

namespace App\Http\Controllers\Profesor;

use App\Http\Controllers\Controller;
use App\Models\Cortes_de_tesis;
use App\Models\cortes_aprobados;
use App\Models\cortes_desaprobados;
use App\Models\Cortes_de_tesis_has_NoConformidades;
use App\Models\NoConformidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RevisarCorteController extends Controller
{
    // Mostrar lista de cortes asignados al profesor 
    public function index()
    {
        try {
            $profesor = Auth::user()->profesor;
            
            if (!$profesor) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el perfil de profesor');
            }

            // Obtener los cortes vinculados a este profesor a través de la tabla corte_tesis_profesor
            $cortesAsignados = Cortes_de_tesis::whereHas('profesores', function($query) use ($profesor) {
                $query->where('profesor_id', $profesor->id);
            })
            ->with([
                'tesis.estudiante',
                'aprobado',
                'desaprobado',
                'noConformidades',
                'versiones' => function($q) {
                    $q->orderBy('version_numero', 'desc');
                }
            ])
            ->get();

            return view('profesor.listaCortes', compact('cortesAsignados'));
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Error al cargar los cortes asignados: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::with([
                'tesis.estudiante',
                'aprobado',
                'desaprobado',
                'noConformidades',
                'versiones' => function($query) {
                    $query->orderBy('version_numero', 'desc');
                }
            ])->findOrFail($id);

            // Verificar que el profesor esté vinculado a este corte (como oponente)
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->route('profesor.dashboard')
                    ->with('error', 'No tienes permisos para revisar este corte');
            }

            $noConformidadesLista = NoConformidades::all();

            return view('profesor.revisarCorte', compact('corte', 'noConformidadesLista'));
            
        } catch (\Exception $e) {
            return redirect()->route('profesor.dashboard')
                ->with('error', 'Error al cargar el corte: ' . $e->getMessage());
        }
    }

    public function aprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Corte no válido');
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor esté vinculado al corte
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de desaprobados si existe
            cortes_desaprobados::where('id_corte', $request->id_corte)->delete();

            // Agregar a aprobados
            $aprobado = new cortes_aprobados();
            $aprobado->id_corte = $request->id_corte;
            $aprobado->save();

            return redirect()->back()
                ->with('success', 'Corte aprobado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al aprobar el corte: ' . $e->getMessage());
        }
    }

    public function desaprobar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Corte no válido');
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor esté vinculado al corte
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de aprobados si existe
            cortes_aprobados::where('id_corte', $request->id_corte)->delete();

            // Agregar a desaprobados
            $desaprobado = new cortes_desaprobados();
            $desaprobado->id_corte = $request->id_corte;
            $desaprobado->save();

            return redirect()->back()
                ->with('success', 'Corte desaprobado correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desaprobar el corte: ' . $e->getMessage());
        }
    }

    public function revertir(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Corte no válido');
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor esté vinculado al corte
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar de aprobados y desaprobados
            cortes_aprobados::where('id_corte', $request->id_corte)->delete();
            cortes_desaprobados::where('id_corte', $request->id_corte)->delete();

            return redirect()->back()
                ->with('success', 'Corte revertido a pendiente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al revertir el corte: ' . $e->getMessage());
        }
    }

    public function agregarNoConformidad(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
                'no_conformidad_id' => 'required|exists:no_conformidades,idNoConformidades',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Datos no válidos');
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor esté vinculado al corte
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Verificar si ya existe la relación
            $existente = Cortes_de_tesis_has_NoConformidades::where('corte_tesis_id', $request->id_corte)
                ->where('no_conformidad_id', $request->no_conformidad_id)
                ->exists();

            if (!$existente) {
                $relacion = new Cortes_de_tesis_has_NoConformidades();
                $relacion->corte_tesis_id = $request->id_corte;
                $relacion->no_conformidad_id = $request->no_conformidad_id;
                $relacion->save();
            }

            return redirect()->back()
                ->with('success', 'No conformidad agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la no conformidad: ' . $e->getMessage());
        }
    }

    public function crearNuevaNoConformidad(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
            'nueva_no_conformidad' => 'required|string|min:5|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $profesor = Auth::user()->profesor;
        $corte = Cortes_de_tesis::find($request->id_corte);

        // Verificar que el profesor esté vinculado al corte
        $estaVinculado = $corte->profesores()
            ->where('profesor_id', $profesor->id)
            ->exists();
        
        if (!$estaVinculado) {
            return redirect()->back()
                ->with('error', 'No tienes permisos para realizar esta acción');
        }

        // Crear nueva no conformidad
        $noConformidad = new NoConformidades();
        $noConformidad->Deficiencias_detectadas = $request->nueva_no_conformidad;
        $noConformidad->save();

        // Asignar la nueva no conformidad al corte
        $relacion = new Cortes_de_tesis_has_NoConformidades();
        $relacion->corte_tesis_id = $request->id_corte;
        $relacion->no_conformidad_id = $noConformidad->idNoConformidades;
        $relacion->save();

        return redirect()->back()
            ->with('success', 'Nueva no conformidad creada y asignada correctamente');

    } catch (\Exception $e) {
        return redirect()->back()
            ->with('error', 'Error al crear la no conformidad: ' . $e->getMessage())
            ->withInput();
    }
}

    public function eliminarNoConformidad(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:cortes_de_tesis,idCortes_de_tesis',
                'no_conformidad_id' => 'required|exists:no_conformidades,idNoConformidades',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Datos no válidos');
            }

            $profesor = Auth::user()->profesor;
            $corte = Cortes_de_tesis::find($request->id_corte);

            // Verificar que el profesor esté vinculado al corte
            $estaVinculado = $corte->profesores()
                ->where('profesor_id', $profesor->id)
                ->exists();
            
            if (!$estaVinculado) {
                return redirect()->back()
                    ->with('error', 'No tienes permisos para realizar esta acción');
            }

            // Eliminar la relación
            Cortes_de_tesis_has_NoConformidades::where('corte_tesis_id', $request->id_corte)
                ->where('no_conformidad_id', $request->no_conformidad_id)
                ->delete();

            return redirect()->back()
                ->with('success', 'No conformidad eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la no conformidad: ' . $e->getMessage());
        }
    }
}