<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cortes_aprobados extends Model
{
    protected $table = 'cortes_aprobados';
    protected $primaryKey = 'id_cortes_aprobados';

    protected $fillable = [
        'id_corte'
    ];
    
    // Relación con el corte
    public function corte()
    {
        return $this->belongsTo(Cortes_de_tesis::class, 'id_corte', 'idCortes_de_tesis');
    }
}
