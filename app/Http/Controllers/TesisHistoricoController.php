<?php

namespace App\Http\Controllers;

use App\Models\TesisHistorico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TesisHistoricoController extends Controller
{
    protected $modelo = TesisHistorico::class;
    protected $rutaVista = 'gestionarTesisHistorico';
    protected $storageFolder = 'historico';
    protected $allowedExtensions = ['pdf', 'doc', 'docx'];

    /**
     * Listado, formulario o detalles según query param.
     *
     *  /gestionarTesisHistorico                       → listado
     *  /gestionarTesisHistorico?accion=crear          → formulario crear
     *  /gestionarTesisHistorico?accion=editar&id=X    → formulario editar
     *  /gestionarTesisHistorico?accion=detalles&id=X  → detalles
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return $this->crear();
        }

        if ($accion === 'editar' && $id) {
            return $this->editar($id);
        }

        if ($accion === 'detalles' && $id) {
            return $this->ver($id);
        }

        // ---------- Listado ----------
        try {
            $buscar     = $request->input('buscar');
            $filtroAño  = $request->input('filtro_año');
            $porPagina  = $request->input('por_pagina', 10);

            $query = $this->modelo::query();

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre_tesis', 'LIKE', "%{$buscar}%")
                      ->orWhere('nombre_estudiante', 'LIKE', "%{$buscar}%");
                });
            }

            if ($filtroAño) {
                $query->where('año', $filtroAño);
            }

            $historicos = $query->orderBy('año', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate($porPagina)
                                ->appends($request->query());

            // Años distintos para el filtro
            $años = $this->modelo::select('año')
                ->distinct()
                ->orderBy('año', 'desc')
                ->pluck('año');

            return view('gestionar.tesisHistorico.index', compact(
                'historicos', 'años', 'filtroAño'
            ));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al cargar el histórico: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación.
     */
    public function crear()
    {
        return view('gestionar.tesisHistorico.formulario');
    }

    /**
     * Formulario de edición.
     */
    public function editar($id)
    {
        try {
            $historico = $this->modelo::findOrFail($id);
            return view('gestionar.tesisHistorico.formulario', compact('historico'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Registro histórico no encontrado');
        }
    }

    /**
     * Detalles de un registro.
     */
    public function ver($id)
    {
        try {
            $historico = $this->modelo::findOrFail($id);
            return view('gestionar.tesisHistorico.detalles', compact('historico'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Registro histórico no encontrado');
        }
    }

    /**
     * Guardar un nuevo registro histórico.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVista, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        $validator = Validator::make($request->all(), [
            'año'                       => 'required|integer|min:2000|max:2100',
            'nombre_tesis'              => 'required|string|min:10|max:500',
            'nombre_estudiante'         => 'required|string|min:3|max:500',
            'documento_fundamentacion'  => 'required|file|max:10240',
            'documento_corte'           => 'nullable|file|max:10240',
        ], [
            'año.required'                      => 'El año es obligatorio',
            'año.integer'                       => 'El año debe ser un número entero',
            'año.min'                           => 'El año debe ser mayor o igual a 2000',
            'año.max'                           => 'El año debe ser menor o igual a 2100',
            'nombre_tesis.required'             => 'El nombre de la tesis es obligatorio',
            'nombre_tesis.min'                  => 'El nombre de la tesis debe tener al menos 10 caracteres',
            'nombre_tesis.max'                  => 'El nombre no puede exceder los 500 caracteres',
            'nombre_estudiante.required'        => 'El nombre del estudiante es obligatorio',
            'nombre_estudiante.min'             => 'El nombre debe tener al menos 3 caracteres',
            'nombre_estudiante.max'             => 'El nombre no puede exceder los 500 caracteres',
            'documento_fundamentacion.required' => 'El documento de fundamentación es obligatorio',
            'documento_fundamentacion.file'     => 'El documento debe ser un archivo',
            'documento_fundamentacion.max'      => 'El documento no puede exceder los 10MB',
            'documento_corte.file'              => 'El documento debe ser un archivo',
            'documento_corte.max'               => 'El documento no puede exceder los 10MB',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormCrear)->withErrors($validator)->withInput();
        }

        try {
            // ----- Documento de fundamentación (obligatorio) -----
            $docFundamentacion = $this->guardarArchivo(
                $request->file('documento_fundamentacion'),
                'fundamentacion'
            );

            // ----- Documento de corte (opcional) -----
            $docCorte = null;
            if ($request->hasFile('documento_corte')) {
                $docCorte = $this->guardarArchivo(
                    $request->file('documento_corte'),
                    'corte'
                );
            }

            // ----- Crear registro -----
            $this->modelo::create([
                'año'                       => $request->año,
                'nombre_tesis'              => $request->nombre_tesis,
                'nombre_estudiante'         => $request->nombre_estudiante,
                'documento_fundamentacion'  => $docFundamentacion,
                'documento_corte'           => $docCorte,
            ]);

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Registro histórico creado correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect($urlFormCrear)
                ->with('error', 'Error al crear el registro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Modificar un registro existente.
     */
    public function modificar(Request $request)
    {
        $urlFormEditar = route($this->rutaVista, ['accion' => 'editar', 'id' => $request->id]);

        $validator = Validator::make($request->all(), [
            'id'                        => 'required|exists:tesis_historico,id',
            'año'                       => 'required|integer|min:2000|max:2100',
            'nombre_tesis'              => 'required|string|min:10|max:500',
            'nombre_estudiante'         => 'required|string|min:3|max:500',
            'documento_fundamentacion'  => 'nullable|file|max:10240',
            'documento_corte'           => 'nullable|file|max:10240',
        ], [
            'id.required'           => 'El ID es obligatorio',
            'id.exists'             => 'El registro no existe',
            'año.required'          => 'El año es obligatorio',
            'año.integer'           => 'El año debe ser un número entero',
            'año.min'               => 'El año debe ser mayor o igual a 2000',
            'año.max'               => 'El año debe ser menor o igual a 2100',
            'nombre_tesis.required' => 'El nombre de la tesis es obligatorio',
            'nombre_tesis.min'      => 'El nombre debe tener al menos 10 caracteres',
            'nombre_tesis.max'      => 'El nombre no puede exceder los 500 caracteres',
            'nombre_estudiante.required' => 'El nombre del estudiante es obligatorio',
            'nombre_estudiante.min'      => 'El nombre debe tener al menos 3 caracteres',
            'nombre_estudiante.max'      => 'El nombre no puede exceder los 500 caracteres',
        ]);

        if ($validator->fails()) {
            return redirect($urlFormEditar)->withErrors($validator)->withInput();
        }

        try {
            $historico = $this->modelo::findOrFail($request->id);

            // Reemplazar documento de fundamentación (si se sube uno nuevo)
            if ($request->hasFile('documento_fundamentacion')) {
                $this->borrarArchivo($historico->documento_fundamentacion);
                $historico->documento_fundamentacion = $this->guardarArchivo(
                    $request->file('documento_fundamentacion'),
                    'fundamentacion'
                );
            }

            // Reemplazar documento de corte (si se sube uno nuevo)
            if ($request->hasFile('documento_corte')) {
                $this->borrarArchivo($historico->documento_corte);
                $historico->documento_corte = $this->guardarArchivo(
                    $request->file('documento_corte'),
                    'corte'
                );
            }

            $historico->año               = $request->año;
            $historico->nombre_tesis      = $request->nombre_tesis;
            $historico->nombre_estudiante = $request->nombre_estudiante;
            $historico->save();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect($urlFormEditar)
                ->with('error', 'Error al modificar el registro: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Eliminar un registro.
     */
    public function eliminar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tesis_historico,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'El registro no existe o ya ha sido eliminado');
        }

        try {
            $historico = $this->modelo::findOrFail($request->id);

            $this->borrarArchivo($historico->documento_fundamentacion);
            $this->borrarArchivo($historico->documento_corte);

            $historico->delete();

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar varios registros.
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Debe seleccionar al menos un registro para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        try {
            $registros = $this->modelo::whereIn('id', $ids)->get();

            foreach ($registros as $r) {
                $this->borrarArchivo($r->documento_fundamentacion);
                $this->borrarArchivo($r->documento_corte);
                $r->delete();
            }

            return redirect()->route($this->rutaVista);

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVista)
                ->with('error', 'Error al eliminar los registros: ' . $e->getMessage());
        }
    }

    /**
     * Descargar el documento de fundamentación.
     */
    public function descargarFundamentacion($id)
    {
        try {
            $historico = $this->modelo::findOrFail($id);

            if (empty($historico->documento_fundamentacion) || !Storage::exists($historico->documento_fundamentacion)) {
                abort(404, 'Documento de fundamentación no encontrado');
            }

            return Storage::download(
                $historico->documento_fundamentacion,
                basename($historico->documento_fundamentacion)
            );

        } catch (\Exception $e) {
            abort(500, 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Descargar el documento de corte.
     */
    public function descargarCorte($id)
    {
        try {
            $historico = $this->modelo::findOrFail($id);

            if (empty($historico->documento_corte) || !Storage::exists($historico->documento_corte)) {
                abort(404, 'Documento de corte no encontrado');
            }

            return Storage::download(
                $historico->documento_corte,
                basename($historico->documento_corte)
            );

        } catch (\Exception $e) {
            abort(500, 'Error al descargar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Exportar el histórico a CSV.
     */
    public function exportarCsv()
    {
        $historicos = $this->modelo::orderBy('año', 'desc')->get();

        $nombreArchivo = 'tesis_historico_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($historicos) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Año', 'Tesis', 'Estudiante', 'Fundamentación', 'Corte'], ';');

            foreach ($historicos as $h) {
                fputcsv($out, [
                    $h->año,
                    $h->nombre_tesis,
                    $h->nombre_estudiante,
                    $h->documento_fundamentacion ? basename($h->documento_fundamentacion) : '—',
                    $h->documento_corte ? basename($h->documento_corte) : '—',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Guarda un archivo en la carpeta del histórico y devuelve la ruta relativa.
     */
    private function guardarArchivo($file, $prefijo)
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $this->allowedExtensions)) {
            throw new \Exception("Solo se permiten archivos PDF, DOC y DOCX ({$prefijo})");
        }

        $nombreOriginal = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $nombreOriginal = $this->sanitizeFileName($nombreOriginal);
        $nombreArchivo  = uniqid("hist_{$prefijo}_") . "_{$nombreOriginal}.{$extension}";

        return $file->storeAs($this->storageFolder, $nombreArchivo);
    }

    /**
     * Borra un archivo si existe.
     */
    private function borrarArchivo($ruta)
    {
        if (!empty($ruta) && Storage::exists($ruta)) {
            Storage::delete($ruta);
        }
    }

    /**
     * Limpia el nombre de archivo de caracteres especiales.
     */
    private function sanitizeFileName($filename)
    {
        $clean = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'],
            $filename
        );
        $clean = str_replace(' ', '_', $clean);
        $clean = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $clean);

        return trim($clean);
    }
}