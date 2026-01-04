<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fundamentaciones extends Model
{
    protected $table = 'fundamentaciones';
    protected $primaryKey = 'id_fundamentacion';

    public function tesis()
    {
        return $this->belongsTo(Tesis::class, 'id_tesis');
    }

    public function aprobada()
    {
        return $this->hasOne(fundamentaciones_aprobadas::class, 'id_fundamentacion');
    }

    public function desaprobada()
    {
        return $this->hasOne(fundamentaciones_desaprobadas::class, 'id_fundamentacion');
    }

    public function recomendacion()
    {
        return $this->hasOne(recomendaciones_fundamentacion::class, 'id_fundamentacion');
    }

    /**
     * Relación con los profesores vinculados a esta fundamentación
     */
    public function profesores()
    {
        return $this->belongsToMany(
            Profesor::class,              // Modelo relacionado
            'profesor_fundamentacion',    // Tabla pivote
            'id_fundamentacion',          // Foreign key en la tabla pivote
            'id_profesor',                // Other key en la tabla pivote
            'id_fundamentacion',          // Local key
            'id'                          // Owner key
        )->withTimestamps(); // Si la tabla pivote tiene timestamps
    }

    /**
     * Relación directa con la tabla pivote
     */
    public function profesorFundamentacion()
    {
        return $this->hasMany(profesorFundamentación::class, 'id_fundamentacion');
    }

    /**
     * Obtener el estado de la fundamentación como string
     */
    public function getEstadoAttribute()
    {
        if ($this->aprobada) {
            return 'aprobada';
        } elseif ($this->desaprobada) {
            return 'desaprobada';
        }
        return 'pendiente';
    }

    /**
     * Verificar si la fundamentación está aprobada
     */
    public function getEstaAprobadaAttribute()
    {
        return $this->aprobada !== null;
    }

    /**
     * Verificar si la fundamentación está desaprobada
     */
    public function getEstaDesaprobadaAttribute()
    {
        return $this->desaprobada !== null;
    }

    /**
     * Verificar si la fundamentación está pendiente
     */
    public function getEstaPendienteAttribute()
    {
        return $this->aprobada === null && $this->desaprobada === null;
    }

   
public function versiones()
{
    return $this->hasMany(version_fundamentacion::class, 'id_fundamentacion', 'id_fundamentacion')
                ->orderBy('version_numero', 'asc');
}

public function ultimaVersion()
{
    return $this->hasOne(version_fundamentacion::class, 'id_fundamentacion', 'id_fundamentacion')
                ->orderBy('version_numero', 'desc');
}


public function opinionTutor()
{
    return $this->hasOne(OpinionTutorFundamentacion::class, 'id_fundamentacion', 'id_fundamentacion');
}

public function fechaEntrega()
{
    
    return FechaEntregaFundamentacion::first();
}

public function tutor()
{
    return $this->tesis->estudiante->tutor;
}


}