<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\fundamentaciones;
use App\Models\Cortes_de_tesis;
use App\Models\Cortes_de_tesis_has_NoConformidades;
use App\Models\NoConformidades;
use App\Models\OpinionTutorFundamentacion;
use App\Models\OpinionTutorCorte;
use App\Models\recomendaciones_fundamentacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentoRevisionController extends Controller
{
    /**
     * Disco donde se almacenan los documentos de revisión.
     * Al ser privado ('local'), no es accesible directamente por URL,
     * solo a través de estos controladores que validan permisos.
     */
    private const DISCO = 'local';

    /**
     * Verifica si el usuario autenticado tiene acceso al documento
     * de una fundamentación, ya sea como:
     *   - Estudiante dueño de la tesis
     *   - Profesor vinculado a la fundamentación
     */
    private function usuarioTieneAccesoFundamentacion($user, $fundamentacion): bool
    {
        if (!$user) {
            return false;
        }

        // Caso 1: Es el estudiante dueño de la tesis
        if ($user->estudiante
            && $fundamentacion->tesis
            && $fundamentacion->tesis->estudiante
            && (int) $fundamentacion->tesis->estudiante->id === (int) $user->estudiante->id) {
            return true;
        }

        // Caso 2: Es un profesor vinculado a la fundamentación
        if ($user->profesor) {
            return $fundamentacion->profesores()
                ->where('id_profesor', $user->profesor->id)
                ->exists();
        }

        return false;
    }

    /**
     * Verifica si el usuario autenticado tiene acceso al documento
     * de un corte, ya sea como:
     *   - Estudiante dueño de la tesis del corte
     *   - Profesor vinculado al corte
     */
    private function usuarioTieneAccesoCorte($user, $corte): bool
    {
        if (!$user) {
            return false;
        }

        // Caso 1: Es el estudiante dueño de la tesis del corte
        if ($user->estudiante
            && $corte->tesis
            && $corte->tesis->estudiante
            && (int) $corte->tesis->estudiante->id === (int) $user->estudiante->id) {
            return true;
        }

        // Caso 2: Es un profesor vinculado al corte
        if ($user->profesor) {
            return $corte->profesores()
                ->where('profesor_id', $user->profesor->id)
                ->exists();
        }

        return false;
    }

    /**
     * Descarga el documento de revisión del tutor para una fundamentación.
     */
    public function descargarRevisionFundamentacion($idFundamentacion)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        $fundamentacion = fundamentaciones::with(['tesis.estudiante', 'profesores'])
            ->findOrFail($idFundamentacion);

        if (!$this->usuarioTieneAccesoFundamentacion($user, $fundamentacion)) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $opinion = OpinionTutorFundamentacion::where('id_fundamentacion', $idFundamentacion)
            ->whereNotNull('documento_revision')
            ->first();

        if (!$opinion
            || !$opinion->documento_revision
            || !Storage::disk(self::DISCO)->exists($opinion->documento_revision)) {
            abort(404, 'Documento de revisión no encontrado');
        }

        return Storage::disk(self::DISCO)->download($opinion->documento_revision);
    }

    /**
     * Descarga el documento de revisión del tutor para un corte.
     */
    public function descargarRevisionCorte($idCorte)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        $corte = Cortes_de_tesis::with(['tesis.estudiante', 'profesores'])
            ->findOrFail($idCorte);

        if (!$this->usuarioTieneAccesoCorte($user, $corte)) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $opinion = OpinionTutorCorte::where('id_corte', $idCorte)
            ->whereNotNull('documento_revision')
            ->first();

        if (!$opinion
            || !$opinion->documento_revision
            || !Storage::disk(self::DISCO)->exists($opinion->documento_revision)) {
            abort(404, 'Documento de revisión no encontrado');
        }

        return Storage::disk(self::DISCO)->download($opinion->documento_revision);
    }

    /**
     * Descarga el documento de revisión adjunto a la recomendación
     * de una fundamentación (subido por el profesor oponente).
     */
    public function descargarRevisionRecomendacionFundamentacion($idFundamentacion)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        $fundamentacion = fundamentaciones::with(['tesis.estudiante', 'profesores'])
            ->findOrFail($idFundamentacion);

        if (!$this->usuarioTieneAccesoFundamentacion($user, $fundamentacion)) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $recomendacion = recomendaciones_fundamentacion::where('id_fundamentacion', $idFundamentacion)
            ->whereNotNull('documento_revision')
            ->first();

        if (!$recomendacion
            || !$recomendacion->documento_revision
            || !Storage::disk(self::DISCO)->exists($recomendacion->documento_revision)) {
            abort(404, 'Documento de revisión no encontrado');
        }

        return Storage::disk(self::DISCO)->download($recomendacion->documento_revision);
    }

    /**
     * Descarga el documento de revisión adjunto a una no conformidad
     * (subido por el profesor oponente).
     *
     * IMPORTANTE: el documento ya NO vive en `no_conformidades` sino en la
     * tabla pivote `corte_tesis_no_conformidades`. Por eso aquí:
     *   1. Buscamos todas las relaciones pivote con esa no conformidad que
     *      TENGAN un documento adjunto.
     *   2. Filtramos por aquellas cuyo corte pertenezca al usuario autenticado
     *      (estudiante dueño o profesor vinculado).
     *   3. Descargamos el documento de la primera relación accesible.
     */
    public function descargarRevisionNoConformidad($idNoConformidad)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Acceso denegado');
        }

        // Comprobar que la no conformidad existe
        $noConformidad = NoConformidades::find($idNoConformidad);

        if (!$noConformidad) {
            abort(404, 'No conformidad no encontrada');
        }

        // Buscar todas las relaciones pivote con esa no conformidad
        // que además tengan un documento adjunto.
        $relaciones = Cortes_de_tesis_has_NoConformidades::where('no_conformidad_id', $idNoConformidad)
            ->whereNotNull('documento_revision')
            ->with(['corte.tesis.estudiante', 'corte.profesores'])
            ->get();

        if ($relaciones->isEmpty()) {
            abort(404, 'Esta no conformidad no tiene documento de revisión adjunto');
        }

        // Buscar la primera relación a la que el usuario tenga acceso
        $relacionAccesible = null;

        foreach ($relaciones as $relacion) {
            if (!$relacion->corte) {
                continue;
            }

            if ($this->usuarioTieneAccesoCorte($user, $relacion->corte)) {
                $relacionAccesible = $relacion;
                break;
            }
        }

        if (!$relacionAccesible) {
            abort(403, 'No tienes permisos para descargar este documento');
        }

        $rutaDocumento = $relacionAccesible->documento_revision;

        if (!$rutaDocumento || !Storage::disk(self::DISCO)->exists($rutaDocumento)) {
            abort(404, 'Documento de revisión no encontrado en el almacenamiento');
        }

        return Storage::disk(self::DISCO)->download($rutaDocumento);
    }
}