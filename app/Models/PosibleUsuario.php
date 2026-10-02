<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosibleUsuario extends Model
{
    protected $table = 'posibles_usuarios';

    protected $fillable = [
        // Cuenta
        'name',
        'email',
        'password',
        'rol_solicitado',
        // Datos personales
        'ci',
        'nombre_persona',
        'apellido1',
        'apellido2',
        // Estudiante
        'sexo',
        'year_academico',
        'id_grupo',
        'id_modalidad',
        'id_carrera',
        // Profesor
        'categoria_docente',
        'categoria_cientifica',
        'id_departamento',
    ];

    protected $hidden = [
        'password',
    ];

    /* ---------- Relaciones para mostrar datos en la vista admin ---------- */
    public function grupo()
    {
        return $this->belongsTo(grupos::class, 'id_grupo', 'id');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class, 'id_modalidad', 'idModalidad');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'idDepartamento');
    }
}