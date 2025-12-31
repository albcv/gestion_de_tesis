<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class permisos extends Model
{

protected $table = 'permisos';
protected $primaryKey = 'id';

public function roles()
    {
        return $this->belongsToMany(roles::class, 'roles_permisos', 'id_rol', 'id_permiso');
    }

}
