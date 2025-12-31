<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    public function rol()
    {
        return $this->belongsTo(roles::class, 'id_rol'); 
    }

    public function tienePermiso($nombrePermiso)
    {
        if ($this->rol && $this->rol->permisos) {
            return $this->rol->permisos->contains('permiso', $nombrePermiso);
        }
        return false;
    }

    public function tieneAlgunPermiso($permisos)
    {
        if (!is_array($permisos)) {
            $permisos = [$permisos];
        }

        foreach ($permisos as $permiso) {
            if ($this->tienePermiso($permiso)) {
                return true;
            }
        }
        return false;
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_usuario');
    }
    
    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'id_usuario');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'id_rol', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}