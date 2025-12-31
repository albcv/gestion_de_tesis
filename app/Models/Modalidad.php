<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modalidad extends Model
{
    protected $table = 'modalidades';
    protected $primaryKey = 'idModalidad';


    protected $fillable = [
        'Nombre_modalidad'
    ];

public function carreras(): BelongsToMany
    {
        return $this->belongsToMany(
            Carrera::class,
            'carrera_modalidad',
            'Modalidad_idModalidad',
            'Carrera_idCarrera'
        )->withPivot('cantidad_years')
         ->withTimestamps();
    }

  
    public function getCarrerasConDuracionAttribute()
    {
        return $this->carreras->map(function ($carrera) {
            return [
                'id' => $carrera->id,
                'nombre' => $carrera->Nombre_carrera,
                'duracion' => $carrera->pivot->cantidad_years
            ];
        });
    }


    
}
