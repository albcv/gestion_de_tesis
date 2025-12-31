<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cortes_de_tesis extends Model
{
    protected $table = 'cortes_de_tesis';
    protected $primaryKey = 'idCortes_de_tesis';

    public function tesis()
    {
        return $this->belongsTo(Tesis::class, 'id_tesis');
    }

    public function aprobado()
    {
        return $this->hasOne(Cortes_aprobados::class, 'id_corte');
    }

    public function desaprobado()
    {
        return $this->hasOne(Cortes_desaprobados::class, 'id_corte');
    }

    public function noConformidades()
    {
        return $this->belongsToMany(
            NoConformidades::class,
            'corte_tesis_no_conformidades',
            'corte_tesis_id',
            'no_conformidad_id'
        );
    }

    public function profesores()
    {
        return $this->belongsToMany(
            Profesor::class,
            'corte_tesis_profesor',
            'corte_tesis_id',
            'profesor_id'
        )->withTimestamps();
    }

    public function profesoresOponentes()
    {
        return $this->belongsToMany(
            Profesor::class,
            'corte_tesis_profesor',
            'corte_tesis_id',
            'profesor_id'
        )->withTimestamps();
    }

    /**
     * Relación con las versiones del corte
     */
    public function versiones()
    {
        return $this->hasMany(version_corte::class, 'id_corte', 'idCortes_de_tesis')
                    ->orderBy('version_numero', 'asc');
    }

    /**
     * Obtener la última versión del corte
     */
    public function ultimaVersion()
    {
        return $this->hasOne(version_corte::class, 'id_corte', 'idCortes_de_tesis')
                    ->orderBy('version_numero', 'desc');
    }

    /**
     * Obtener el estado del corte como string
     */
    public function getEstadoAttribute()
    {
        if ($this->aprobado) {
            return 'aprobado';
        } elseif ($this->desaprobado) {
            return 'desaprobado';
        }
        return 'pendiente';
    }

    /**
     * Verificar si el corte está aprobado
     */
    public function getEstaAprobadoAttribute()
    {
        return $this->aprobado !== null;
    }

    /**
     * Verificar si el corte está desaprobado
     */
    public function getEstaDesaprobadoAttribute()
    {
        return $this->desaprobado !== null;
    }

    /**
     * Verificar si el corte está pendiente
     */
    public function getEstaPendienteAttribute()
    {
        return $this->aprobado === null && $this->desaprobado === null;
    }


   
public function opinionTutor()
{
    return $this->hasOne(OpinionTutorCorte::class, 'id_corte', 'idCortes_de_tesis');
}

public function fechaEntrega()
{
    return FechaEntregaCorte::where('numero_corte', $this->Numero_corte)->first();
}




}