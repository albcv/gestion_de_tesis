<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'id';

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function cortesProfesores()
{
    return $this->hasMany(Cortes_de_tesis_has_Profesor_oponente::class, 'profesor_id');
}

public function cortesTesis()
    {
        return $this->belongsToMany(
            Cortes_de_tesis::class,
            'corte_tesis_profesor',
            'profesor_id',
            'corte_tesis_id'
        )->withTimestamps();
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }


    public function fundamentaciones()
    {
        return $this->belongsToMany(
            fundamentaciones::class,
            'profesor_fundamentacion',
            'id_profesor',
            'id_fundamentacion'
        );
    }

    public function profesorFundamentacion()
    {
        return $this->hasMany(profesorFundamentación::class, 'id_profesor');
    }

  public function tutorados()
{
    return $this->belongsToMany(Estudiante::class, 'tutor_estudiante', 'id_profesor', 'id_estudiante');
}
}
