<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpinionTutorCorte extends Model
{
    protected $table = 'opinion_tutor_corte';
    protected $primaryKey = 'id';
    
    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_corte',
        'id_profesor',
        'opinion'
    ];
    
    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_corte' => 'integer',
        'id_profesor' => 'integer',
    ];
    
    
    public function corte()
    {
        return $this->belongsTo(Cortes_de_tesis::class, 'id_corte', 'idCortes_de_tesis');
    }
    

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }
}