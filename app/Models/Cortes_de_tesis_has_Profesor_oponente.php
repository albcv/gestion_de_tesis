<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cortes_de_tesis_has_Profesor_oponente extends Model
{
    protected $table = 'corte_tesis_profesor';
    protected $primaryKey = 'id';

    public function corte()
{
    return $this->belongsTo(Cortes_de_tesis::class, 'corte_tesis_id', 'idCortes_de_tesis');
}

public function profesor()
{
    return $this->belongsTo(Profesor::class, 'profesor_id');
}
}
