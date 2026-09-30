<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\fundamentaciones;
use App\Models\Cortes_de_tesis;
use App\Models\OpinionTutorFundamentacion;
use App\Models\OpinionTutorCorte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentoRevisionController extends Controller
{
    /**
     * Descarga el documento de revisión del tutor para una fundamentación.
     * Solo el estudiante dueño de la tesis puede descargarlo.
     */
    public function descargarRevisionFundamentacion($idFundamentacion)
    {
        $user = Auth::user();

        if (!$user || !$user->estudiante) {
            abort(403, 'Acceso denegado');
        }

        $fundamentacion = fundamentaciones::with('tesis.estudiante')
            ->findOrFail($idFundamentacion);

        // ¿El estudiante autenticado es el dueño de la tesis?
        if ((int) $fundamentacion->tesis->estudiante->id !== (int) $user->estudiante->id) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $opinion = OpinionTutorFundamentacion::where('id_fundamentacion', $idFundamentacion)
            ->whereNotNull('documento_revision')
            ->first();

        if (!$opinion || !$opinion->documento_revision || !Storage::exists($opinion->documento_revision)) {
            abort(404, 'Documento de revisión no encontrado');
        }

        return Storage::download($opinion->documento_revision);
    }

    /**
     * Descarga el documento de revisión del tutor para un corte.
     */
    public function descargarRevisionCorte($idCorte)
    {
        $user = Auth::user();

        if (!$user || !$user->estudiante) {
            abort(403, 'Acceso denegado');
        }

        $corte = Cortes_de_tesis::with('tesis.estudiante')
            ->findOrFail($idCorte);

        if ((int) $corte->tesis->estudiante->id !== (int) $user->estudiante->id) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $opinion = OpinionTutorCorte::where('id_corte', $idCorte)
            ->whereNotNull('documento_revision')
            ->first();

        if (!$opinion || !$opinion->documento_revision || !Storage::exists($opinion->documento_revision)) {
            abort(404, 'Documento de revisión no encontrado');
        }

        return Storage::download($opinion->documento_revision);
    }
}