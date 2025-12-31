<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class roles extends Model
{

protected $table = 'roles';
protected $primaryKey = 'id';


  public function permisos()
    {
        return $this->belongsToMany(permisos::class, 'roles_permisos', 'id_rol', 'id_permiso');
    }

}
