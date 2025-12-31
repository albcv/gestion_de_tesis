<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\fundamentaciones_aprobadas;

class fundamentacionesAprobadasController extends Controller
{

    protected $modelo = fundamentaciones_aprobadas::class;
    protected $rutaVista = 'gestionarFundamentacionesAprobadas';
    protected $columnaFundamentacion = 'id_fundamentacion';

    public function mostrar()
    {
        $aprobadas = $this->modelo::all();
        return view('gestionar.gestionarFundamentacionesAprobadas', compact('aprobadas'));
    }

    public function agregar(Request $request)
    {
        $id_fundamentación = $request['id_fundamentación'];

        $fund_aprobada = new $this->modelo();
        $fund_aprobada->{$this->columnaFundamentacion} = $id_fundamentación;
        $fund_aprobada->save();

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
        $f = $this->modelo::find($request->id);
        if ($f) {
            $f->{$this->columnaFundamentacion} = $request->id_fundamentación;
            $f->save();
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