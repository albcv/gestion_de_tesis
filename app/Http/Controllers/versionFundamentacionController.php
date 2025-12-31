<?php

namespace App\Http\Controllers;

use App\Models\version_fundamentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class versionFundamentacionController extends Controller
{
    protected $modelo = version_fundamentacion::class;
    protected $storageFolder = 'versiones_fundamentacion';
    protected $allowedExtensions = ['pdf', 'doc', 'docx'];
    protected $rutaVista = 'gestionarVersiónFundamentación';
    

    public function mostrar()
    {
        $objetos = $this->modelo::all();
        
        return view('gestionar.gestionarVersiónFundamentación', compact('objetos'));
    }

    public function agregar(Request $request)
    {
        $request->validate([
            'id_fundamentacion' => 'required|integer',
            'documento' => 'required|file|max:10240' 
        ]);

        $file = $request->file('documento');
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!in_array($extension, $this->allowedExtensions)) {
            return redirect()->back()->withErrors([
                'documento' => 'Solo se permiten archivos PDF, DOC y DOCX'
            ]);
        }

        if ($request->hasFile('documento')) {
            // Guardar archivo con extensión
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = $originalName . '_' . time() . '.' . $extension;
            $path = $request->file('documento')->storeAs($this->storageFolder, $fileName);

            $obj = new $this->modelo();
            $obj->ruta_documento = $path;
            $obj->id_fundamentacion = $request->id_fundamentacion;
            $obj->save();
        }

        return redirect(route($this->rutaVista));
    }

    public function eliminar(Request $request)
    {
        $id = $request['id'];
        $obj = $this->modelo::find($id);

        if ($obj) {
            // Eliminar el archivo físico del almacenamiento
            if (!empty($obj->ruta_documento) && Storage::exists($obj->ruta_documento)) {
                Storage::delete($obj->ruta_documento);
            }
            
            // Eliminar el registro de la base de datos
            $this->modelo::destroy($id);
        }

         return redirect(route($this->rutaVista));
    }

    public function modificar(Request $request)
    {
        $request->validate([
            'id_fundamentacion' => 'required|integer',
            'documento' => 'required|file|max:10240' 
        ]);

        $obj = $this->modelo::find($request->id);
        
        if ($obj) {
            if ($request->hasFile('documento')) {
                $file = $request->file('documento');
                $extension = strtolower($file->getClientOriginalExtension());
                
                // Validar extensión
                if (!in_array($extension, $this->allowedExtensions)) {
                    return redirect()->back()->withErrors([
                        'documento' => 'Solo se permiten archivos PDF, DOC y DOCX'
                    ]);
                }
                
                // Eliminar archivo anterior
                if (!empty($obj->ruta_documento) && Storage::exists($obj->ruta_documento)) {
                    Storage::delete($obj->ruta_documento);
                }
                
                // Guardar nuevo archivo con extensión
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = $originalName . '_' . time() . '.' . $extension;
                $path = $request->file('documento')->storeAs($this->storageFolder, $fileName);
                $obj->ruta_documento = $path;
            }

            $obj->id_fundamentacion = $request->id_fundamentacion;
            $obj->save(); 
        }

         return redirect(route($this->rutaVista));
    }

    public function vaciar()
    {
        try {
            // Obtener todas las fundamentaciones antes de eliminarlas
            $objetos = $this->modelo::all();
            
            // Eliminar los archivos físicos
            foreach ($objetos as $obj) {
                if (!empty($obj->ruta_documento) && Storage::exists($obj->ruta_documento)) {
                    Storage::delete($obj->ruta_documento);
                }
            }
            
            // Eliminar los registros de la base de datos
            $this->modelo::query()->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function verDocumento($id)
    {
        $obj = $this->modelo::findOrFail($id);
        
        // Verificar que el campo no sea nulo
        if (empty($obj->ruta_documento)) {
            abort(404, 'No hay documento asociado a esta fundamentación');
        }
        
        // Verificar que el archivo existe en el storage
        if (!Storage::exists($obj->ruta_documento)) {
            abort(404, 'Archivo no encontrado en el almacenamiento');
        }
        
        // Obtener el nombre original del archivo para la descarga
        $fileName = 'fundamentacion_tesis_' . $obj->id_fundamentacion . '.' . 
                   pathinfo($obj->ruta_documento, PATHINFO_EXTENSION);
        
        // Descargar el archivo
        return Storage::download($obj->ruta_documento, $fileName);
    }
}