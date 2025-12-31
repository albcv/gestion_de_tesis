<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Carrera extends Model
{
    protected $table = 'carreras';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id_facultad',
        'Nombre_carrera'
    ];

    
    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad', 'idFacultad');
    }

   
    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class, 'id_carrera');
    }

    public function modalidades(): BelongsToMany
    {
        return $this->belongsToMany(
            Modalidad::class,
            'carrera_modalidad',
            'Carrera_idCarrera',
            'Modalidad_idModalidad'
        )->withPivot('cantidad_years')
         ->withTimestamps();
    }

   
    public function getCantidadEstudiantesAttribute()
    {
        return $this->estudiantes()->count();
    }

    
    public function getModalidadesConDuracionAttribute()
    {
        return $this->modalidades->map(function ($modalidad) {
            return [
                'id' => $modalidad->idModalidad,
                'nombre' => $modalidad->Nombre_modalidad,
                'duracion' => $modalidad->pivot->cantidad_years
            ];
        });
    }
}