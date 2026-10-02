<?php

namespace App\Http\Controllers;

use App\Models\PosibleUsuario;
use App\Models\User;
use App\Models\roles;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\grupos;
use App\Models\Modalidad;
use App\Models\Departamento;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PosiblesUsuariosController extends Controller
{
    /* ============================================================
       REGISTRO PÚBLICO
       ============================================================ */

    public function mostrarRegistro()
    {
        $grupos        = grupos::orderBy('número')->get();
        $modalidades   = Modalidad::orderBy('Nombre_modalidad')->get();
        $departamentos = Departamento::orderBy('Nombre_departamento')->get();
        $carreras      = Carrera::orderBy('Nombre_carrera')->get();

        return view('registro', compact(
            'grupos', 'modalidades', 'departamentos', 'carreras'
        ));
    }

    public function registrar(Request $request)
    {
        $rolSolicitado = strtolower((string) $request->input('rol_solicitado'));

        if (!in_array($rolSolicitado, ['estudiante', 'profesor'], true)) {
            return redirect()->back()
                ->withErrors(['rol_solicitado' => 'Debe seleccionar si solicita registro como Estudiante o Profesor.'])
                ->withInput();
        }

        $rules = [
            'name'  => ['required', 'string', 'min:3', 'max:100'],
            'email' => [
                'required', 'email', 'max:255',
                'unique:users,email',
                'unique:posibles_usuarios,email',
            ],
            'password' => ['required', 'string', 'min:6', 'confirmed'],

            'ci'             => ['required', 'digits:11'],
            'nombre_persona' => ['required', 'string', 'min:3', 'max:40'],
            'apellido1'      => ['required', 'string', 'min:3', 'max:40'],
            'apellido2'      => ['required', 'string', 'min:3', 'max:40'],
        ];

        if ($rolSolicitado === 'estudiante') {
            $rules += [
                'ci'             => ['required', 'digits:11', 'unique:estudiantes,CI_estudiante', 'unique:posibles_usuarios,ci'],
                'sexo'           => ['required', 'in:Masculino,Femenino'],
                'year_academico' => ['required', 'integer', 'min:1', 'max:6'],
                'id_grupo'       => ['required', 'exists:grupos,id'],
                'id_modalidad'   => ['required', 'exists:modalidades,idModalidad'],
                'id_carrera'     => ['required', 'exists:carreras,id'],
            ];
        } else {
            $rules += [
                'ci'                   => ['required', 'digits:11', 'unique:profesor,CI_profesor', 'unique:posibles_usuarios,ci'],
                'categoria_docente'    => ['required', 'in:Profesor Titular,Profesor Instructor,Profesor Auxiliar'],
                'categoria_cientifica' => ['required', 'in:Licenciado,Ingeniero,Máster en Ciencias,Doctor en Ciencias'],
                'id_departamento'      => ['required', 'exists:departamentos,idDepartamento'],
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'rol_solicitado' => $rolSolicitado,
            'ci'             => $request->ci,
            'nombre_persona' => $request->nombre_persona,
            'apellido1'      => $request->apellido1,
            'apellido2'      => $request->apellido2,
        ];

        if ($rolSolicitado === 'estudiante') {
            $data += [
                'sexo'           => $request->sexo,
                'year_academico' => $request->year_academico,
                'id_grupo'       => $request->id_grupo,
                'id_modalidad'   => $request->id_modalidad,
                'id_carrera'     => $request->id_carrera,
            ];
        } else {
            $data += [
                'categoria_docente'    => $request->categoria_docente,
                'categoria_cientifica' => $request->categoria_cientifica,
                'id_departamento'      => $request->id_departamento,
            ];
        }

        PosibleUsuario::create($data);

        return redirect()->route('login')
            ->with('success', 'Solicitud enviada correctamente. Un administrador revisará tu registro.');
    }

    /* ============================================================
       PANEL DE ADMINISTRACIÓN
       ============================================================ */

    /** Listado de solicitudes. */
    public function index(Request $request)
    {
        try {
            $buscar    = $request->input('buscar');
            $filtroRol = $request->input('filtro_rol');
            $porPagina = $request->input('por_pagina', 10);

            $query = PosibleUsuario::with(['grupo', 'modalidad', 'carrera', 'departamento']);

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where('name', 'LIKE', "%{$buscar}%")
                      ->orWhere('email', 'LIKE', "%{$buscar}%")
                      ->orWhere('ci', 'LIKE', "%{$buscar}%")
                      ->orWhere('nombre_persona', 'LIKE', "%{$buscar}%")
                      ->orWhere('apellido1', 'LIKE', "%{$buscar}%")
                      ->orWhere('apellido2', 'LIKE', "%{$buscar}%");
                });
            }

            if ($filtroRol && in_array($filtroRol, ['estudiante', 'profesor'], true)) {
                $query->where('rol_solicitado', $filtroRol);
            }

            $posibles = $query->orderByDesc('created_at')
                ->paginate($porPagina)
                ->appends($request->query());

            $roles = roles::orderBy('rol')->get();

            return view('gestionar.posibles_usuarios.index',
                compact('posibles', 'roles'));

        } catch (\Exception $e) {
            return redirect()->route('gestionarUsuarios')
                ->with('error', 'Error al cargar las solicitudes: ' . $e->getMessage());
        }
    }

    /** Ver detalle de una solicitud. */
    public function ver($id)
    {
        try {
            $posible = PosibleUsuario::with(['grupo', 'modalidad', 'carrera', 'departamento'])
                ->findOrFail($id);

            $roles = roles::orderBy('rol')->get();

            return view('gestionar.posibles_usuarios.detalles',
                compact('posible', 'roles'));

        } catch (\Exception $e) {
            return redirect()->route('posiblesUsuarios')
                ->with('error', 'Solicitud no encontrada: ' . $e->getMessage());
        }
    }

       /**
     * Acepta una solicitud → crea User + Estudiante/Profesor y borra la solicitud.
     * El rol se asigna automáticamente según `rol_solicitado`.
     */
    public function aceptar($id)
    {
        DB::beginTransaction();

        try {
            $posible = PosibleUsuario::findOrFail($id);

            // ----- 1. Verificar email único -----
            if (User::where('email', $posible->email)->exists()) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Ya existe un usuario con el email ' . $posible->email);
            }

            // ----- 2. Determinar el rol según la solicitud -----
            $nombreRol = $posible->rol_solicitado === 'estudiante'
                ? 'Estudiante'
                : 'Profesor';

            $rol = roles::where('rol', $nombreRol)->first();

            if (!$rol) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', "No se encontró el rol \"{$nombreRol}\" en el sistema. Contacte al administrador.");
            }

            // ----- 3. Crear el User con el rol correspondiente -----
            $user = new User();
            $user->name     = $posible->name;
            $user->email    = $posible->email;
            $user->id_rol   = $rol->id;
            $user->password = $posible->password; // ya viene hasheada
            $user->save();

            // ----- 4. Crear el registro específico según el rol -----
            if ($posible->rol_solicitado === 'estudiante') {
                $estudiante = new Estudiante();
                $estudiante->id_usuario        = $user->id;
                $estudiante->CI_estudiante     = $posible->ci;
                $estudiante->Nombre_estudiante = $posible->nombre_persona;
                $estudiante->Apellido1         = $posible->apellido1;
                $estudiante->Apellido2         = $posible->apellido2;
                $estudiante->sexo              = $posible->sexo;
                $estudiante->year_academico    = $posible->year_academico;
                $estudiante->id_grupo          = $posible->id_grupo;
                $estudiante->id_modalidad      = $posible->id_modalidad;
                $estudiante->id_carrera        = $posible->id_carrera;
                $estudiante->save();
            } else {
                $profesor = new Profesor();
                $profesor->id_usuario           = $user->id;
                $profesor->CI_profesor          = $posible->ci;
                $profesor->Nombre_profesor      = $posible->nombre_persona;
                $profesor->Apellido1            = $posible->apellido1;
                $profesor->Apellido2            = $posible->apellido2;
                $profesor->id_departamento      = $posible->id_departamento;
                $profesor->Categoria_docente    = $posible->categoria_docente;
                $profesor->Categoria_cientifica = $posible->categoria_cientifica;
                $profesor->save();
            }

            // ----- 5. Eliminar la solicitud -----
            $posible->delete();

            DB::commit();

            return redirect()->route('posiblesUsuarios')
                ->with('success', "Solicitud aceptada como {$nombreRol}. El usuario ya puede iniciar sesión.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al aceptar la solicitud: ' . $e->getMessage());
        }
    }

    public function rechazar($id)
    {
        try {
            $posible = PosibleUsuario::findOrFail($id);
            $nombre  = $posible->name;
            $posible->delete();

            return redirect()->route('posiblesUsuarios')
                ->with('success', "Solicitud de \"{$nombre}\" rechazada correctamente");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al rechazar la solicitud: ' . $e->getMessage());
        }
    }

    public function rechazarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route('posiblesUsuarios')
                ->with('error', 'Debe seleccionar al menos una solicitud');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route('posiblesUsuarios')
                ->with('error', 'Los IDs enviados no son válidos');
        }

        DB::beginTransaction();
        try {
            PosibleUsuario::whereIn('id', $ids)->delete();
            DB::commit();

            $total = count($ids);
            return redirect()->route('posiblesUsuarios')
                ->with('success', "Se rechazaron {$total} solicitud(es) correctamente");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('posiblesUsuarios')
                ->with('error', 'Error al rechazar las solicitudes: ' . $e->getMessage());
        }
    }
}