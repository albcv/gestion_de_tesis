<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechaEntregaFundamentacion extends Model
{
    use HasFactory;

    protected $table = 'fecha_entrega_fundamentacion';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'fecha_entrega',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];
}