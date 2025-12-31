<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
   protected $table = 'estudiantes';
   protected $primaryKey = 'id';

   protected $fillable = [
        'CI_estudiante',
        'sexo',
        'Nombre_estudiante',
        'Apellido1',
        'Apellido2',
        'Fecha_ingreso',
        'year_academico',
        'id_grupo',
        'id_modalidad',
        'número',
        'id_usuario',
        'id_carrera'
    ];

    public function tesis()
    {
        return $this->hasOne(Tesis::class, 'id_estudiante', 'id');
    }


   public function grupo()
    {
      return $this->belongsTo(grupos::class, 'id_grupo');
    }

    public function carrera()
    {
      return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    public function modalidad()
    {
      return $this->belongsTo(Modalidad::class, 'id_modalidad');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }


public function tutores()
{
    return $this->hasMany(tutor_estudiante::class, 'id_estudiante');
}


public function tutor()
{
    return $this->belongsToMany(Profesor::class, 'tutor_estudiante', 'id_estudiante', 'id_profesor');
}



}
