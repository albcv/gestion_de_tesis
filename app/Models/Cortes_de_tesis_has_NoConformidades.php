<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cortes_de_tesis_has_NoConformidades extends Model
{
    protected $table = 'corte_tesis_no_conformidades';
    protected $primaryKey = 'id';

    public function corte()
{
    return $this->belongsTo(Cortes_de_tesis::class, 'corte_tesis_id', 'idCortes_de_tesis');
}

public function noConformidad()
{
    return $this->belongsTo(NoConformidades::class, 'no_conformidad_id');
}

}
