<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class version_fundamentacion extends Model
{
    protected $table = 'version_fundamentacion';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id_fundamentacion',
        'version_numero',
        'nombre_archivo',
        'ruta_documento',
        'tamanio',
        'tipo',
        'descripcion'
    ];

    // Relación con la fundamentación
    public function fundamentacion()
    {
        return $this->belongsTo(fundamentaciones::class, 'id_fundamentacion', 'id_fundamentacion');
    }

    // Obtener el tamaño formateado
    public function getTamanioFormateadoAttribute()
    {
        $bytes = $this->tamanio;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    // Obtener la extensión del archivo
    public function getExtensionAttribute()
    {
        return pathinfo($this->nombre_archivo, PATHINFO_EXTENSION);
    }
}