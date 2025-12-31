<?php

namespace App\Http\Controllers;

use App\Models\Cortes_de_tesis_has_NoConformidades;
use App\Models\Cortes_de_tesis;
use App\Models\NoConformidades;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class cortesNoConformidadesController extends Controller
{
    protected $modelo = Cortes_de_tesis_has_NoConformidades::class;
    protected $modeloCorte = Cortes_de_tesis::class;
    protected $modeloNoConformidad = NoConformidades::class;
    protected $rutaVista = 'gestionarCortesNoConformidades';
    protected $columnaCorte = 'corte_tesis_id';
    protected $columnaNoConformidad = 'no_conformidad_id';
    
    protected $tablaCorteNoConformidad;
    protected $tablaCorte;
    protected $tablaNoConformidad;
    protected $columnaIdCorteNoConformidad;
    protected $columnaIdCortePrimaria;
    protected $columnaIdNoConformidadPrimaria;
    protected $columnaIdCorte;
    protected $columnaIdNoConformidad;

    public function __construct()
    {
        $instanciaCorteNoConformidad = new $this->modelo;
        $instanciaCorte = new $this->modeloCorte;
        $instanciaNoConformidad = new $this->modeloNoConformidad;
        
        $this->tablaCorteNoConformidad = $instanciaCorteNoConformidad->getTable();
        $this->tablaCorte = $instanciaCorte->getTable();
        $this->tablaNoConformidad = $instanciaNoConformidad->getTable();
        
        $this->columnaIdCorteNoConformidad = $instanciaCorteNoConformidad->getKeyName();
        $this->columnaIdCortePrimaria = $instanciaCorte->getKeyName();
        $this->columnaIdNoConformidadPrimaria = $instanciaNoConformidad->getKeyName();
        
        $this->columnaIdCorte = 'corte_tesis_id';
        $this->columnaIdNoConformidad = 'no_conformidad_id';
    }

    // Método para mostrar formulario de agregar no conformidad a un corte específico
    public function crear($id_corte)
    {
        try {
            $corte = $this->modeloCorte::with('tesis')->findOrFail($id_corte);
            $noConformidades = $this->modeloNoConformidad::all();
            
            return view('gestionar.gestionarCortesNoConformidades.crear', compact('corte', 'noConformidades'));
        } catch (\Exception $e) {
            return redirect()->route('verCorte', ['id' => $id_corte])
                ->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    // Método para mostrar formulario de editar relación específica
    public function editar($id_corte, $id_nc)
    {
        try {
            $corte = $this->modeloCorte::with('tesis')->findOrFail($id_corte);
            $noConformidad = $this->modeloNoConformidad::findOrFail($id_nc);
            $noConformidades = $this->modeloNoConformidad::all();
            
            return view('gestionar.gestionarCortesNoConformidades.editar', compact('corte', 'noConformidad', 'noConformidades'));
        } catch (\Exception $e) {
            return redirect()->route('verCorte', ['id' => $id_corte])
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    // Método para agregar no conformidad existente al corte
    public function agregarExistente(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'no_conformidad_id' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
            ], [
                'id_corte.required' => 'El corte de tesis es obligatorio',
                'id_corte.exists' => 'El corte de tesis seleccionado no existe',
                'no_conformidad_id.required' => 'La no conformidad es obligatoria',
                'no_conformidad_id.exists' => 'La no conformidad seleccionada no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe la combinación corte-no conformidad
            $existente = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $request->no_conformidad_id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una relación para este corte y esta no conformidad')
                    ->withInput();
            }

            // Crear la relación
            $ctnc = new $this->modelo();
            $ctnc->{$this->columnaCorte} = $request->id_corte;
            $ctnc->{$this->columnaNoConformidad} = $request->no_conformidad_id;
            $ctnc->save();

            return redirect()->route('verCorte', ['id' => $request->id_corte])
                ->with('success', 'No conformidad agregada al corte correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la no conformidad al corte: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Método para crear nueva no conformidad y vincularla al corte
    public function crearYVincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'deficiencias_detectadas' => 'required|string|min:10|max:500',
            ], [
                'id_corte.required' => 'El corte de tesis es obligatorio',
                'id_corte.exists' => 'El corte de tesis seleccionado no existe',
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
            $existenteNC = $this->modeloNoConformidad::where('Deficiencias_detectadas', $request->deficiencias_detectadas)->first();

            if ($existenteNC) {
                // Si ya existe, vincular la existente
                $noConformidadId = $existenteNC->idNoConformidades;
            } else {
                // Crear nueva no conformidad
                $nc = new $this->modeloNoConformidad();
                $nc->Deficiencias_detectadas = $request->deficiencias_detectadas;
                $nc->save();
                $noConformidadId = $nc->idNoConformidades;
            }

            // Verificar si ya existe la relación
            $existenteRelacion = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $noConformidadId)
                ->first();

            if ($existenteRelacion) {
                return redirect()->route('verCorte', ['id' => $request->id_corte])
                    ->with('info', 'Esta no conformidad ya estaba vinculada al corte');
            }

            // Crear la relación
            $ctnc = new $this->modelo();
            $ctnc->{$this->columnaCorte} = $request->id_corte;
            $ctnc->{$this->columnaNoConformidad} = $noConformidadId;
            $ctnc->save();

            $mensaje = $existenteNC ? 
                'No conformidad existente vinculada al corte correctamente' : 
                'Nueva no conformidad creada y vinculada al corte correctamente';

            return redirect()->route('verCorte', ['id' => $request->id_corte])
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear y vincular la no conformidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Método para actualizar relación (cambiar no conformidad)
    public function actualizarRelacion(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'no_conformidad_actual' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
                'no_conformidad_nueva' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'Datos inválidos')
                    ->withInput();
            }

            // Verificar si la relación actual existe
            $relacionActual = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $request->no_conformidad_actual)
                ->first();

            if (!$relacionActual) {
                return redirect()->back()
                    ->with('error', 'La relación actual no existe')
                    ->withInput();
            }

            // Verificar si ya existe la nueva relación
            $relacionExistente = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $request->no_conformidad_nueva)
                ->first();

            if ($relacionExistente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una relación con la nueva no conformidad')
                    ->withInput();
            }

            // Actualizar la relación
            $relacionActual->{$this->columnaNoConformidad} = $request->no_conformidad_nueva;
            $relacionActual->save();

            return redirect()->route('verCorte', ['id' => $request->id_corte])
                ->with('success', 'No conformidad actualizada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la relación: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Método para desvincular no conformidad del corte
    public function desvincular(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'corte_tesis_id' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'no_conformidad_id' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La relación no existe o ya ha sido eliminada');
            }

            // Eliminar la relación
            $this->modelo::where($this->columnaCorte, $request->corte_tesis_id)
                         ->where($this->columnaNoConformidad, $request->no_conformidad_id)
                         ->delete();

            return redirect()->back()
                ->with('success', 'No conformidad desvinculada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al desvincular la no conformidad: ' . $e->getMessage());
        }
    }

    // Método para mostrar vista general (mantener compatibilidad)
    public function mostrar()
    {
        try {
            $ctncs = $this->modelo::with(['corte.tesis', 'noConformidad'])->get();
            $cortes = $this->modeloCorte::with('tesis')->get();
            $noConformidades = $this->modeloNoConformidad::all();
            
            return view('gestionar.gestionarCortesNoConformidades', compact('ctncs', 'cortes', 'noConformidades'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las relaciones corte-no conformidad: ' . $e->getMessage());
        }
    }

    // Método para agregar (mantener compatibilidad)
    public function agregar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'id_no_conformidades' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
            ], [
                'id_corte.required' => 'El corte de tesis es obligatorio',
                'id_corte.exists' => 'El corte de tesis seleccionado no existe',
                'id_no_conformidades.required' => 'La no conformidad es obligatoria',
                'id_no_conformidades.exists' => 'La no conformidad seleccionada no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe la combinación corte-no conformidad
            $existente = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $request->id_no_conformidades)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe una relación para este corte y esta no conformidad')
                    ->withInput();
            }

            $ctnc = new $this->modelo();
            $ctnc->{$this->columnaCorte} = $request->id_corte;
            $ctnc->{$this->columnaNoConformidad} = $request->id_no_conformidades;
            $ctnc->save();

            return redirect(route($this->rutaVista))
                ->with('success', 'Relación corte-no conformidad agregada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al agregar la relación corte-no conformidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Métodos existentes (mantener compatibilidad)
    public function modificar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorteNoConformidad . ',' . $this->columnaIdCorteNoConformidad,
                'id_corte' => 'required|exists:' . $this->tablaCorte . ',' . $this->columnaIdCortePrimaria,
                'id_no_conformidades' => 'required|exists:' . $this->tablaNoConformidad . ',' . $this->columnaIdNoConformidadPrimaria,
            ], [
                'id_corte.required' => 'El corte de tesis es obligatorio',
                'id_corte.exists' => 'El corte de tesis seleccionado no existe',
                'id_no_conformidades.required' => 'La no conformidad es obligatoria',
                'id_no_conformidades.exists' => 'La no conformidad seleccionada no existe',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Verificar si ya existe otra combinación corte-no conformidad (excluyendo la actual)
            $existente = $this->modelo::where($this->columnaCorte, $request->id_corte)
                ->where($this->columnaNoConformidad, $request->id_no_conformidades)
                ->where($this->columnaIdCorteNoConformidad, '!=', $request->id)
                ->first();

            if ($existente) {
                return redirect()->back()
                    ->with('error', 'Ya existe otra relación para este corte y esta no conformidad')
                    ->withInput();
            }

            $ctnc = $this->modelo::find($request->id);
            if ($ctnc) {
                $ctnc->{$this->columnaCorte} = $request->id_corte;
                $ctnc->{$this->columnaNoConformidad} = $request->id_no_conformidades;
                $ctnc->save();
                
                return redirect(route($this->rutaVista))
                    ->with('success', 'Relación corte-no conformidad modificada correctamente');
            }

            return redirect()->back()
                ->with('error', 'No se encontró la relación corte-no conformidad a modificar');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al modificar la relación corte-no conformidad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function eliminar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaCorteNoConformidad . ',' . $this->columnaIdCorteNoConformidad,
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'La relación corte-no conformidad no existe o ya ha sido eliminada');
            }

            $id = $request['id'];
            $this->modelo::destroy($id);

            return redirect(route($this->rutaVista))
                ->with('success', 'Relación corte-no conformidad eliminada correctamente');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar la relación corte-no conformidad: ' . $e->getMessage());
        }
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Todas las relaciones corte-no conformidad han sido eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al vaciar las relaciones corte-no conformidad: ' . $e->getMessage()
            ]);
        }
    }
}