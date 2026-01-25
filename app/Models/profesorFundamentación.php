<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class profesorFundamentación extends Model
{
    protected $table = 'profesor_fundamentacion';
    protected $primaryKey = 'id';
    
    
    protected $fillable = [
        'id_profesor',
        'id_fundamentacion'
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }


    public function fundamentacion()
    {
        return $this->belongsTo(fundamentaciones::class, 'id_fundamentacion');
    }
}