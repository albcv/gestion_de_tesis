<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Carrera_has_Modalidad extends Model
{
    protected $table = 'carrera_modalidad';
    protected $primaryKey = 'id';

    protected $fillable = [
        'Carrera_idCarrera',
        'Modalidad_idModalidad',
        'cantidad_years'
    ];


    public function carrera()
{
    return $this->belongsTo(Carrera::class, 'Carrera_idCarrera');
}

public function modalidad()
{
    return $this->belongsTo(Modalidad::class, 'Modalidad_idModalidad');
}
}
