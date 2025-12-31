<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaEntregaCorte extends Model
{
    use HasFactory;

    protected $table = 'fecha_entrega_corte';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'numero_corte',
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];
}