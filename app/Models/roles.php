<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class roles extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';

    /* ============================================================
       RELACIONES
       ============================================================ */

    /**
     * Permisos del rol (muchos a muchos).
     */
    public function permisos()
    {
        return $this->belongsToMany(
            permisos::class,
            'roles_permisos',
            'id_rol',
            'id_permiso'
        );
    }

    /**
     * Usuarios que tienen este rol (muchos a muchos).
     */
    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'roles_usuarios',
            'id_rol',        // FK del modelo actual en la pivote
            'id_usuario'     // FK del modelo relacionado
        )->withTimestamps();
    }
}