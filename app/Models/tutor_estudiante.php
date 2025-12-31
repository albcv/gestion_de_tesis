<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tutor_estudiante extends Model
{
    protected $table = 'tutor_estudiante';
    protected $primaryKey = 'id';


public function profesor()
{
    return $this->belongsTo(Profesor::class, 'id_profesor');
}

public function estudiante()
{
    return $this->belongsTo(Estudiante::class, 'id_estudiante');
}
    
}
