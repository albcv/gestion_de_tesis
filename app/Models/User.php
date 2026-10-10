<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    /* ============================================================
       RELACIONES
       ============================================================ */

    /**
     * Roles del usuario (muchos a muchos).
     */
    public function roles()
    {
        return $this->belongsToMany(
            roles::class,          // modelo relacionado
            'roles_usuarios',      // tabla pivote
            'id_usuario',          // FK del modelo actual
            'id_rol'               // FK del modelo relacionado
        )->withTimestamps();
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id_usuario');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'id_usuario');
    }

    /* ============================================================
       HELPERS DE ROLES
       ============================================================ */

    /**
     * ¿El usuario tiene el rol indicado? (por nombre, case-insensitive)
     */
    public function tieneRol(string $nombreRol): bool
    {
        return $this->roles()
            ->whereRaw('LOWER(rol) = ?', [strtolower($nombreRol)])
            ->exists();
    }

    /**
     * ¿El usuario tiene alguno de los roles indicados?
     */
    public function tieneAlgunRol(array $roles): bool
    {
        $roles = array_map('strtolower', $roles);

        if (empty($roles)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($roles), '?'));

        return $this->roles()
            ->whereRaw("LOWER(rol) IN ({$placeholders})", $roles)
            ->exists();
    }

    /**
     * Devuelve el primer rol del usuario (útil cuando solo tiene uno).
     */
    public function rolPrincipal()
    {
        return $this->roles()->first();
    }

    /* ============================================================
       HELPERS DE PERMISOS
       (recorren los permisos de TODOS los roles del usuario)
       ============================================================ */

    public function tienePermiso($nombrePermiso): bool
    {
        foreach ($this->roles as $rol) {
            if ($rol->permisos && $rol->permisos->contains('permiso', $nombrePermiso)) {
                return true;
            }
        }
        return false;
    }

    public function tieneAlgunPermiso($permisos): bool
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

    /* ============================================================
       CONFIGURACIÓN
       ============================================================ */

    protected $fillable = [
        'name',
        'email',
        'password',
        // 'id_rol'  ← YA NO se usa
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}