<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\recomendaciones_fundamentacion;
use App\Models\fundamentaciones;

class recomendacionesFundamentacionController extends Controller
{
    protected $modelo = recomendaciones_fundamentacion::class;
    protected $modeloFundamentacion = fundamentaciones::class;
    protected $columnaFundamentacion = 'id_fundamentacion';
    protected $columnaRecomendacion = 'recomendacion';

    protected $tablaRecomendaciones;
    protected $tablaFundamentaciones;
    protected $columnaIdRecomendacion;
    protected $columnaIdFundamentacion;

    public function __construct()
    {
        $instanciaRec = new $this->modelo;
        $instanciaFund = new $this->modeloFundamentacion;

        $this->tablaRecomendaciones = $instanciaRec->getTable();
        $this->tablaFundamentaciones = $instanciaFund->getTable();

        $this->columnaIdRecomendacion = $instanciaRec->getKeyName();
        $this->columnaIdFundamentacion = $instanciaFund->getKeyName();
    }

    public function mostrar()
    {
        try {
            $recomendaciones = $this->modelo::with(['fundamentacion'])->get();
            $fundamentaciones = $this->modeloFundamentacion::with('tesis')->get();

            return view('gestionar.gestionarRecomendacionesFundamentación', compact('recomendaciones', 'fundamentaciones'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las recomendaciones: ' . $e->getMessage());
        }
    }

    public function crear($id_fundamentacion)
    {
        try {
            $fundamentacion = $this->modeloFundamentacion::with('tesis.estudiante')->findOrFail($id_fundamentacion);
            return view('gestionar.gestionarRecomendacionesFundamentación.crear', compact('fundamentacion'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $recomendacion = $this->modelo::with('fundamentacion.tesis.estudiante')->findOrFail($id);
            return view('gestionar.gestionarRecomendacionesFundamentación.editar', compact('recomendacion'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    public function agregar(Request $request)
    {
        try {
            // aceptar tanto claves con acento como sin acento si el frontend usa alguna de las dos
            $idFundInput = $request->input('id_fundamentación', $request->input('id_fundamentacion', $request->input('id_fundamentacion')));
            $recInput = $request->input('recomendación', $request->input('recomendacion', $request->input('recomendacion')));

            $validator = Validator::make(
                [
                    'id_fundamentacion' => $idFundInput,
                    'recomendacion' => $recInput,
                ],
                [
                    'id_fundamentacion' => 'required|exists:' . $this->tablaFundamentaciones . ',' . $this->columnaIdFundamentacion,
                    'recomendacion' => 'required|string|min:3|max:2000',
                ],
                [
                    'id_fundamentacion.required' => 'La fundamentación es obligatoria',
                    'id_fundamentacion.exists' => 'La fundamentación seleccionada no existe',
                    'recomendacion.required' => 'La recomendación es obligatoria',
                    'recomendacion.string' => 'La recomendación debe ser texto',
                    'recomendacion.min' => 'La recomendación debe tener al menos 3 caracteres',
                    'recomendacion.max' => 'La recomendación no puede exceder los 2000 caracteres',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $r = new $this->modelo();
            $r->{$this->columnaFundamentacion} = $idFundInput;
            $r->{$this->columnaRecomendacion} = $recInput;
            $r->save();

    
            return redirect()->route('verFundamentación', ['id' => $idFundInput])
                ->with('success', 'Recomendación agregada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al agregar la recomendación: ' . $e->getMessage())->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaRecomendaciones . ',' . $this->columnaIdRecomendacion,
            ], [
                'id.required' => 'Id obligatorio',
                'id.exists' => 'La recomendación no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', 'La recomendación no existe o ya ha sido eliminada');
            }

            $id = $request->input('id');
            
            // Obtener la recomendación antes de eliminar para saber a qué fundamentación pertenecía
            $recomendacion = $this->modelo::find($id);
            $id_fundamentacion = $recomendacion ? $recomendacion->{$this->columnaFundamentacion} : null;
            
            $this->modelo::destroy($id);

    
            if ($id_fundamentacion) {
                return redirect()->route('verFundamentación', ['id' => $id_fundamentacion])
                    ->with('success', 'Recomendación eliminada correctamente');
            } else {
                return redirect()->route('gestionarRecomendacionesFundamentación')
                    ->with('success', 'Recomendación eliminada correctamente');
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la recomendación: ' . $e->getMessage());
        }
    }

    public function modificar(Request $request)
    {
        try {
            // aceptar múltiples formas de nombres de campos
            $id = $request->input('id', $request->input('Id'));
            $idFundInput = $request->input('id_fundamentación', $request->input('id_fundamentacion', $request->input('id_fundamentacion')));
            $recInput = $request->input('recomendación', $request->input('recomendacion', $request->input('recomendacion')));

            $validator = Validator::make(
                [
                    'id' => $id,
                    'id_fundamentacion' => $idFundInput,
                    'recomendacion' => $recInput,
                ],
                [
                    'id' => 'required|exists:' . $this->tablaRecomendaciones . ',' . $this->columnaIdRecomendacion,
                    'id_fundamentacion' => 'required|exists:' . $this->tablaFundamentaciones . ',' . $this->columnaIdFundamentacion,
                    'recomendacion' => 'required|string|min:3|max:2000',
                ],
                [
                    'id.required' => 'Id de la recomendación requerido',
                    'id.exists' => 'La recomendación no existe',
                    'id_fundamentacion.required' => 'La fundamentación es obligatoria',
                    'id_fundamentacion.exists' => 'La fundamentación seleccionada no existe',
                    'recomendacion.required' => 'La recomendación es obligatoria',
                    'recomendacion.string' => 'La recomendación debe ser texto',
                    'recomendacion.min' => 'La recomendación debe tener al menos 3 caracteres',
                    'recomendacion.max' => 'La recomendación no puede exceder los 2000 caracteres',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $r = $this->modelo::find($id);
            if ($r) {
                $r->{$this->columnaFundamentacion} = $idFundInput;
                $r->{$this->columnaRecomendacion} = $recInput;
                $r->save();

    
                return redirect()->route('verFundamentación', ['id' => $idFundInput])
                    ->with('success', 'Recomendación modificada correctamente');
            }

            return redirect()->back()->with('error', 'No se encontró la recomendación a modificar');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al modificar la recomendación: ' . $e->getMessage())->withInput();
        }
    }


}