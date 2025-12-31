<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cortes_aprobados;

class cortesAprobadosController extends Controller
{
    
    protected $modelo = cortes_aprobados::class;
    protected $rutaVista = 'gestionarCortesAprobados';
    protected $columnaCorte = 'id_corte';

    public function mostrar()
    {
        $aprobados = $this->modelo::all();
        return view('gestionar.gestionarCortesAprobados', compact('aprobados'));
    }

    public function agregar(Request $request)
    {
        $id_corte = $request['id_corte'];

        $obj = new $this->modelo();
        $obj->{$this->columnaCorte} = $id_corte;
        $obj->save();

        return redirect(route($this->rutaVista));
    }

    public function eliminar(Request $request)
    {
        $id = $request['id'];
        $this->modelo::destroy($id);

        return redirect(route($this->rutaVista));
    }

    public function modificar(Request $request)
    {
        $obj = $this->modelo::find($request->id);
        if ($obj) {
            $obj->{$this->columnaCorte} = $request->id_corte;
            $obj->save();
        }

        return redirect(route($this->rutaVista));
    }

    public function vaciar()
    {
        try {
            $this->modelo::query()->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}