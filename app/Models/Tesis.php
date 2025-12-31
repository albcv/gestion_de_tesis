<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tesis extends Model
{
    protected $table = 'tesis';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_estudiante',
        'Nombre_trabajo'
    ];

    public function estudiante()
    {
      return $this->belongsTo(Estudiante::class, 'id_estudiante');
    }

    public function fundamentacion()
{
return $this->hasOne(fundamentaciones::class, 'id_tesis');
}

public function cortes()
{
return $this->hasMany(Cortes_de_tesis::class, 'id_tesis');
}

}
