<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class recomendaciones_fundamentacion extends Model
{
    protected $table = 'recomendaciones_fundamentacion';
    protected $primaryKey = 'id_recomendaciones_fundamentacion';

    protected $fillable = [
        'id_fundamentacion',
        'recomendacion',
        'documento_revision',  
    ];

    public function fundamentacion()
    {
        return $this->belongsTo(fundamentaciones::class, 'id_fundamentacion');
    }
}