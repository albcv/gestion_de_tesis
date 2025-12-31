<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpinionTutorFundamentacion extends Model
{
    protected $table = 'opinion_tutor_fundamentacion';
    protected $primaryKey = 'id';
    
    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_fundamentacion',
        'id_profesor',
        'opinion'
    ];
    
    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_fundamentacion' => 'integer',
        'id_profesor' => 'integer',
    ];
    

    public function fundamentacion()
    {
        return $this->belongsTo(fundamentaciones::class, 'id_fundamentacion', 'id_fundamentacion');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }
}