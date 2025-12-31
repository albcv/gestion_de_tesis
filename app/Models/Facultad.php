<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultades';
    protected $primaryKey = 'idFacultad';

    public function carreras()
    {
        return $this->hasMany(Carrera::class, 'id_facultad', 'idFacultad');
    }
}