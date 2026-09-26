<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TesisHistorico extends Model
{
    protected $table = 'tesis_historico';

    protected $fillable = [
        'año',
        'nombre_tesis',
        'nombre_estudiante',
        'documento_fundamentacion',
        'documento_corte',
    ];

    /**
     * ¿Tiene documento de corte?
     */
    public function getTieneCorteAttribute()
    {
        return !empty($this->documento_corte);
    }

    /**
     * Nombre legible del archivo de fundamentación.
     */
    public function getNombreFundamentacionAttribute()
    {
        return $this->documento_fundamentacion
            ? basename($this->documento_fundamentacion)
            : null;
    }

    /**
     * Nombre legible del archivo de corte.
     */
    public function getNombreCorteAttribute()
    {
        return $this->documento_corte
            ? basename($this->documento_corte)
            : null;
    }
}