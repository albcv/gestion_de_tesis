<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\roles;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\grupos;
use App\Models\Modalidad;
use App\Models\Departamento;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Modelos
    protected $modelo;
    protected $modeloRol;
    protected $modeloEstudiante;
    protected $modeloProfesor;
    protected $modeloGrupo;
    protected $modeloModalidad;
    protected $modeloDepartamento;
    protected $modeloCarrera;

    // Tablas
    protected $tablaUsuario;
    protected $tablaRol;
    protected $tablaEstudiante;
    protected $tablaProfesor;
    protected $tablaGrupo;
    protected $tablaModalidad;
    protected $tablaDepartamento;
    protected $tablaCarrera;

    // Columnas ID
    protected $columnaIdUsuario;
    protected $columnaIdRol;
    protected $columnaIdEstudiante;
    protected $columnaIdProfesor;
    protected $columnaIdGrupo;
    protected $columnaIdModalidad;
    protected $columnaIdDepartamento;
    protected $columnaIdCarrera;

    // Nombres de columnas
    protected $columnaName;
    protected $columnaEmail;
    protected $columnaRol;
    protected $columnaPassword;

    // IDs de roles
    protected $rolEstudianteId;
    protected $rolProfesorId;
    protected $rolAdministradorId;

    // Rutas
    protected $rutaVistaPrincipal = 'gestionarUsuarios';

    // Nombres de roles
    const ROL_ADMINISTRADOR = 'Administrador';
    const ROL_PROFESOR = 'Profesor';
    const ROL_ESTUDIANTE = 'Estudiante';

    public function __construct()
    {
        $this->modelo = User::class;
        $this->modeloRol = roles::class;
        $this->modeloEstudiante = Estudiante::class;
        $this->modeloProfesor = Profesor::class;
        $this->modeloGrupo = grupos::class;
        $this->modeloModalidad = Modalidad::class;
        $this->modeloDepartamento = Departamento::class;
        $this->modeloCarrera = Carrera::class;

        $instanciaUsuario = new $this->modelo;
        $instanciaRol = new $this->modeloRol;
        $instanciaEstudiante = new $this->modeloEstudiante;
        $instanciaProfesor = new $this->modeloProfesor;
        $instanciaGrupo = new $this->modeloGrupo;
        $instanciaModalidad = new $this->modeloModalidad;
        $instanciaDepartamento = new $this->modeloDepartamento;
        $instanciaCarrera = new $this->modeloCarrera;

        $this->tablaUsuario = $instanciaUsuario->getTable();
        $this->tablaRol = $instanciaRol->getTable();
        $this->tablaEstudiante = $instanciaEstudiante->getTable();
        $this->tablaProfesor = $instanciaProfesor->getTable();
        $this->tablaGrupo = $instanciaGrupo->getTable();
        $this->tablaModalidad = $instanciaModalidad->getTable();
        $this->tablaDepartamento = $instanciaDepartamento->getTable();
        $this->tablaCarrera = $instanciaCarrera->getTable();

        $this->columnaIdUsuario = $instanciaUsuario->getKeyName();
        $this->columnaIdRol = $instanciaRol->getKeyName();
        $this->columnaIdEstudiante = $instanciaEstudiante->getKeyName();
        $this->columnaIdProfesor = $instanciaProfesor->getKeyName();
        $this->columnaIdGrupo = $instanciaGrupo->getKeyName();
        $this->columnaIdModalidad = $instanciaModalidad->getKeyName();
        $this->columnaIdDepartamento = $instanciaDepartamento->getKeyName();
        $this->columnaIdCarrera = $instanciaCarrera->getKeyName();

        $this->columnaName = 'name';
        $this->columnaEmail = 'email';
        $this->columnaRol = 'id_rol';
        $this->columnaPassword = 'password';

        $this->obtenerIdsDeRoles();
    }

    private function obtenerIdsDeRoles(): void
    {
        $rolAdmin = $this->modeloRol::where('rol', self::ROL_ADMINISTRADOR)->first();
        $this->rolAdministradorId = $rolAdmin ? $rolAdmin->id : null;

        $rolProfesor = $this->modeloRol::where('rol', self::ROL_PROFESOR)->first();
        $this->rolProfesorId = $rolProfesor ? $rolProfesor->id : null;

        $rolEstudiante = $this->modeloRol::where('rol', self::ROL_ESTUDIANTE)->first();
        $this->rolEstudianteId = $rolEstudiante ? $rolEstudiante->id : null;
    }

    /**
     * Listado, formulario de creación/edición o detalles según query param.
     *
     *  /gestionarUsuarios                       → listado
     *  /gestionarUsuarios?accion=crear          → formulario crear
     *  /gestionarUsuarios?accion=editar&id=X    → formulario editar
     *  /gestionarUsuarios?accion=detalles&id=X  → detalles
     */
    public function mostrar(Request $request)
    {
        $accion = $request->query('accion');
        $id     = $request->query('id');

        if ($accion === 'crear') {
            return $this->crearUsuario();
        }

        if ($accion === 'editar' && $id) {
            return $this->editar($id);
        }

        if ($accion === 'detalles' && $id) {
            return $this->ver($id);
        }

        // ---------- Listado ----------
        try {
            $buscar = $request->input('buscar');
            $filtroRol = $request->input('filtro_rol');
            $porPagina = $request->input('por_pagina', 10);

            $query = $this->modelo::with(['rol', 'estudiante', 'profesor']);

            if ($buscar) {
                $query->where(function ($q) use ($buscar) {
                    $q->where($this->columnaName, 'LIKE', "%{$buscar}%")
                        ->orWhere($this->columnaEmail, 'LIKE', "%{$buscar}%")
                        ->orWhereHas('estudiante', function ($q) use ($buscar) {
                            $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                                ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                        })
                        ->orWhereHas('profesor', function ($q) use ($buscar) {
                            $q->where('Nombre_profesor', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                                ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                                ->orWhere('CI_profesor', 'LIKE', "%{$buscar}%");
                        });
                });
            }

            if ($filtroRol) {
                if (strtolower($filtroRol) === strtolower(self::ROL_ESTUDIANTE)) {
                    $query->where($this->columnaRol, $this->rolEstudianteId);
                } elseif (strtolower($filtroRol) === strtolower(self::ROL_PROFESOR)) {
                    $query->where($this->columnaRol, $this->rolProfesorId);
                } elseif (strtolower($filtroRol) === strtolower(self::ROL_ADMINISTRADOR)) {
                    $query->where($this->columnaRol, $this->rolAdministradorId);
                } else {
                    if (is_numeric($filtroRol)) {
                        $query->where($this->columnaRol, $filtroRol);
                    } else {
                        $rol = $this->modeloRol::where('rol', 'LIKE', "%{$filtroRol}%")->first();
                        if ($rol) {
                            $query->where($this->columnaRol, $rol->id);
                        }
                    }
                }
            }

            $usuarios = $query->paginate($porPagina)->appends($request->query());

            $roles = $this->modeloRol::all();

            return view('gestionar.usuario.index', compact('usuarios', 'roles'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar la lista de usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Formulario de creación.
     */
    public function crearUsuario()
    {
        try {
            $roles = $this->modeloRol::all();
            $grupos = $this->modeloGrupo::all();
            $modalidades = $this->modeloModalidad::all();
            $departamentos = $this->modeloDepartamento::all();
            $carreras = $this->modeloCarrera::all();

            return view('gestionar.usuario.formulario', compact(
                'roles', 'grupos', 'modalidades', 'departamentos', 'carreras'
            ));
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }

    /**
     * Agrega un nuevo usuario.
     * Si el botón pulsado es "continuar", vuelve al formulario de creación.
     */
    public function agregar(Request $request)
    {
        $urlFormCrear = route($this->rutaVistaPrincipal, ['accion' => 'crear']);
        $modoContinuar = $request->input('accion') === 'continuar';

        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:3', 'max:40', 'unique:' . $this->tablaUsuario . ',' . $this->columnaName],
                'email' => ['required', 'email', 'max:255', 'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail],
                'password' => ['required', 'string', 'min:6', 'max:255'],
                'rol' => ['required', 'exists:' . $this->tablaRol . ',' . $this->columnaIdRol],
            ], [
                'name.required' => 'El nombre de usuario es obligatorio',
                'name.min' => 'El nombre de usuario debe tener al menos 3 caracteres',
                'name.max' => 'El nombre de usuario no puede exceder los 40 caracteres',
                'name.unique' => 'Este nombre de usuario ya está registrado',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'El correo electrónico debe ser válido',
                'email.max' => 'El correo electrónico no puede exceder los 255 caracteres',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                'password.max' => 'La contraseña no puede exceder los 255 caracteres',
                'rol.required' => 'El rol es obligatorio',
                'rol.exists' => 'El rol seleccionado no existe',
            ]);

            if ($validator->fails()) {
                return redirect($urlFormCrear)->withErrors($validator)->withInput();
            }

            $user = new $this->modelo();
            $user->{$this->columnaName} = $request->name;
            $user->{$this->columnaEmail} = $request->email;
            $user->{$this->columnaRol} = $request->rol;
            $user->{$this->columnaPassword} = Hash::make($request->password);
            $user->save();

            if ($request->rol == $this->rolEstudianteId) {
                $this->agregarEstudiante($request, $user);
            } elseif ($request->rol == $this->rolProfesorId) {
                $this->agregarProfesor($request, $user);
            }

            DB::commit();

            if ($modoContinuar) {
                return redirect($urlFormCrear)
                    ->with('success', 'Usuario creado correctamente. Puede seguir agregando.');
            }

            return redirect()->route($this->rutaVistaPrincipal);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect($urlFormCrear)->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect($urlFormCrear)
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function agregarEstudiante($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'ci_estudiante' => [
                'required', 'digits:11', 'unique:' . $this->tablaEstudiante . ',CI_estudiante',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = (int) substr($fecha, 2, 2);
                    $day = (int) substr($fecha, 4, 2);

                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    $diasPorMes = [1=>31,2=>29,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31];
                    if ($day < 1 || $day > $diasPorMes[$month]) {
                        $fail('Los dígitos 5-6 del CI deben representar un día válido para el mes.');
                    }
                }
            ],
            'nombre_estudiante' => 'required|string|min:3|max:40',
            'apellido1_estudiante' => 'required|string|min:3|max:40',
            'apellido2_estudiante' => 'required|string|min:3|max:40',
            'numero_estudiante' => 'required|integer',
            'sexo_estudiante' => 'required|in:Masculino,Femenino',
            'fecha_ingreso' => 'required|date',
            'año_académico' => 'required|integer|min:1|max:6',
            'id_grupo' => 'required|exists:' . $this->tablaGrupo . ',' . $this->columnaIdGrupo,
            'id_modalidad' => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdModalidad,
            'id_carrera' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
        ], [
            'ci_estudiante.required' => 'El carnet de identidad es obligatorio',
            'ci_estudiante.digits' => 'El carnet de identidad debe tener exactamente 11 dígitos',
            'ci_estudiante.unique' => 'Este carnet de identidad ya está registrado',
            'nombre_estudiante.required' => 'El nombre es obligatorio',
            'nombre_estudiante.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre_estudiante.max' => 'El nombre no puede exceder los 40 caracteres',
            'apellido1_estudiante.required' => 'El primer apellido es obligatorio',
            'apellido1_estudiante.min' => 'El primer apellido debe tener al menos 3 caracteres',
            'apellido1_estudiante.max' => 'El primer apellido no puede exceder los 40 caracteres',
            'apellido2_estudiante.required' => 'El segundo apellido es obligatorio',
            'apellido2_estudiante.min' => 'El segundo apellido debe tener al menos 3 caracteres',
            'apellido2_estudiante.max' => 'El segundo apellido no puede exceder los 40 caracteres',
            'numero_estudiante.required' => 'El número es obligatorio',
            'numero_estudiante.integer' => 'El número debe ser un valor numérico',
            'sexo_estudiante.required' => 'El sexo es obligatorio',
            'sexo_estudiante.in' => 'El sexo debe ser Masculino o Femenino',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.date' => 'La fecha de ingreso debe ser una fecha válida',
            'año_académico.required' => 'El año académico es obligatorio',
            'año_académico.integer' => 'El año académico debe ser un número',
            'año_académico.min' => 'El año académico debe ser al menos 1',
            'año_académico.max' => 'El año académico no puede exceder 6',
            'id_grupo.required' => 'El grupo es obligatorio',
            'id_grupo.exists' => 'El grupo seleccionado no existe',
            'id_modalidad.required' => 'La modalidad es obligatoria',
            'id_modalidad.exists' => 'La modalidad seleccionada no existe',
            'id_carrera.required' => 'La carrera es obligatoria',
            'id_carrera.exists' => 'La carrera seleccionada no existe',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $estudiante = new $this->modeloEstudiante();
        $estudiante->id_usuario = $user->id;
        $estudiante->CI_estudiante = $request->ci_estudiante;
        $estudiante->Nombre_estudiante = $request->nombre_estudiante;
        $estudiante->Apellido1 = $request->apellido1_estudiante;
        $estudiante->Apellido2 = $request->apellido2_estudiante;
        $estudiante->número = $request->numero_estudiante;
        $estudiante->sexo = $request->sexo_estudiante;
        $estudiante->Fecha_ingreso = $request->fecha_ingreso;
        $estudiante->year_academico = $request->año_académico;
        $estudiante->id_grupo = $request->id_grupo;
        $estudiante->id_modalidad = $request->id_modalidad;
        $estudiante->id_carrera = $request->id_carrera;
        $estudiante->save();
    }

    private function agregarProfesor($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'ci_profesor' => [
                'required', 'digits:11', 'unique:' . $this->tablaProfesor . ',CI_profesor',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = (int) substr($fecha, 2, 2);
                    $day = (int) substr($fecha, 4, 2);

                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    $diasPorMes = [1=>31,2=>29,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31];
                    if ($day < 1 || $day > $diasPorMes[$month]) {
                        $fail('Los dígitos 5-6 del CI deben representar un día válido para el mes.');
                    }
                }
            ],
            'nombre_profesor' => 'required|string|min:3|max:40',
            'apellido1_profesor' => 'required|string|min:3|max:40',
            'apellido2_profesor' => 'required|string|min:3|max:40',
            'id_departamento' => 'required|exists:' . $this->tablaDepartamento . ',' . $this->columnaIdDepartamento,
            'categoría_docente' => 'required|string|in:Profesor Titular,Profesor Instructor,Profesor Auxiliar',
            'categoría_científica' => 'required|string|in:Licenciado,Ingeniero,Máster en Ciencias,Doctor en Ciencias',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        $profesor = new $this->modeloProfesor();
        $profesor->id_usuario = $user->id;
        $profesor->CI_profesor = $request->ci_profesor;
        $profesor->Nombre_profesor = $request->nombre_profesor;
        $profesor->Apellido1 = $request->apellido1_profesor;
        $profesor->Apellido2 = $request->apellido2_profesor;
        $profesor->id_departamento = $request->id_departamento;
        $profesor->Categoria_docente = $request->categoría_docente;
        $profesor->Categoria_cientifica = $request->categoría_científica;
        $profesor->save();
    }

    public function ver($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario,
            ]);

            if ($validator->fails()) {
                return redirect()->route($this->rutaVistaPrincipal)->with('error', 'El usuario no existe');
            }

            $usuario = $this->modelo::with([
                'rol',
                'estudiante' => fn($q) => $q->with(['grupo', 'modalidad', 'carrera']),
                'profesor' => fn($q) => $q->with(['departamento']),
            ])->findOrFail($id);

            return view('gestionar.usuario.detalles', compact('usuario'));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar los datos del usuario: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario,
            ]);

            if ($validator->fails()) {
                return redirect()->route($this->rutaVistaPrincipal)->with('error', 'El usuario no existe');
            }

            $usuario = $this->modelo::with([
                'rol',
                'estudiante' => fn($q) => $q->with(['grupo', 'modalidad', 'carrera']),
                'profesor' => fn($q) => $q->with(['departamento']),
            ])->findOrFail($id);

            $roles = $this->modeloRol::all();
            $grupos = $this->modeloGrupo::all();
            $modalidades = $this->modeloModalidad::all();
            $departamentos = $this->modeloDepartamento::all();
            $carreras = $this->modeloCarrera::all();

            return view('gestionar.usuario.formulario', compact(
                'usuario', 'roles', 'grupos', 'modalidades', 'departamentos', 'carreras'
            ));

        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar el formulario de edición: ' . $e->getMessage());
        }
    }

    public function actualizar(Request $request)
    {
        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario,
                'name' => ['required', 'string', 'min:3', 'max:40',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaName . ',' . $request->id . ',' . $this->columnaIdUsuario],
                'email' => ['required', 'email', 'max:255',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail . ',' . $request->id . ',' . $this->columnaIdUsuario],
                'rol' => 'required|exists:' . $this->tablaRol . ',' . $this->columnaIdRol,
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route($this->rutaVistaPrincipal, ['accion' => 'editar', 'id' => $request->id])
                    ->withErrors($validator)
                    ->withInput();
            }

            if ($request->filled('password')) {
                $passwordValidator = Validator::make($request->all(), [
                    'password' => 'string|min:6|max:255'
                ]);
                if ($passwordValidator->fails()) {
                    return redirect()
                        ->route($this->rutaVistaPrincipal, ['accion' => 'editar', 'id' => $request->id])
                        ->withErrors($passwordValidator)
                        ->withInput();
                }
            }

            $user = $this->modelo::findOrFail($request->id);

            $user->{$this->columnaName} = $request->name;
            $user->{$this->columnaEmail} = $request->email;
            $user->{$this->columnaRol} = $request->rol;

            if ($request->filled('password')) {
                $user->{$this->columnaPassword} = Hash::make($request->password);
            }

            $user->save();

            if ($request->rol == $this->rolEstudianteId) {
                $this->actualizarEstudiante($request, $user);
            } elseif ($request->rol == $this->rolProfesorId) {
                $this->actualizarProfesor($request, $user);
            } else {
                if ($user->estudiante) $user->estudiante->delete();
                if ($user->profesor)   $user->profesor->delete();
            }

            DB::commit();

            return redirect()->route($this->rutaVistaPrincipal);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVistaPrincipal, ['accion' => 'editar', 'id' => $request->id])
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()
                ->route($this->rutaVistaPrincipal, ['accion' => 'editar', 'id' => $request->id])
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function actualizarEstudiante($request, $user)
    {
        $estudianteId = $user->estudiante ? $user->estudiante->id : null;

        $validator = Validator::make($request->all(), [
            'ci_estudiante' => [
                'required', 'digits:11',
                'unique:' . $this->tablaEstudiante . ',CI_estudiante,' . $estudianteId . ',id',
            ],
            'nombre_estudiante' => 'required|string|min:3|max:40',
            'apellido1_estudiante' => 'required|string|min:3|max:40',
            'apellido2_estudiante' => 'required|string|min:3|max:40',
            'numero_estudiante' => 'required|integer',
            'sexo_estudiante' => 'required|in:Masculino,Femenino',
            'fecha_ingreso' => 'required|date',
            'año_académico' => 'required|integer|min:1|max:6',
            'id_grupo' => 'required|exists:' . $this->tablaGrupo . ',' . $this->columnaIdGrupo,
            'id_modalidad' => 'required|exists:' . $this->tablaModalidad . ',' . $this->columnaIdModalidad,
            'id_carrera' => 'required|exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera,
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        if ($user->profesor) $user->profesor->delete();

        if ($user->estudiante) {
            $estudiante = $user->estudiante;
        } else {
            $estudiante = new $this->modeloEstudiante();
            $estudiante->id_usuario = $user->id;
        }

        $estudiante->CI_estudiante = $request->ci_estudiante;
        $estudiante->Nombre_estudiante = $request->nombre_estudiante;
        $estudiante->Apellido1 = $request->apellido1_estudiante;
        $estudiante->Apellido2 = $request->apellido2_estudiante;
        $estudiante->número = $request->numero_estudiante;
        $estudiante->sexo = $request->sexo_estudiante;
        $estudiante->Fecha_ingreso = $request->fecha_ingreso;
        $estudiante->year_academico = $request->año_académico;
        $estudiante->id_grupo = $request->id_grupo;
        $estudiante->id_modalidad = $request->id_modalidad;
        $estudiante->id_carrera = $request->id_carrera;
        $estudiante->save();
    }

    private function actualizarProfesor($request, $user)
    {
        $profesorId = $user->profesor ? $user->profesor->id : null;

        $validator = Validator::make($request->all(), [
            'ci_profesor' => [
                'required', 'digits:11',
                'unique:' . $this->tablaProfesor . ',CI_profesor,' . $profesorId . ',id',
            ],
            'nombre_profesor' => 'required|string|min:3|max:40',
            'apellido1_profesor' => 'required|string|min:3|max:40',
            'apellido2_profesor' => 'required|string|min:3|max:40',
            'id_departamento' => 'required|exists:' . $this->tablaDepartamento . ',' . $this->columnaIdDepartamento,
            'categoría_docente' => 'required|string|in:Profesor Titular,Profesor Instructor,Profesor Auxiliar',
            'categoría_científica' => 'required|string|in:Licenciado,Ingeniero,Máster en Ciencias,Doctor en Ciencias',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        if ($user->estudiante) $user->estudiante->delete();

        if ($user->profesor) {
            $profesor = $user->profesor;
        } else {
            $profesor = new $this->modeloProfesor();
            $profesor->id_usuario = $user->id;
        }

        $profesor->CI_profesor = $request->ci_profesor;
        $profesor->Nombre_profesor = $request->nombre_profesor;
        $profesor->Apellido1 = $request->apellido1_profesor;
        $profesor->Apellido2 = $request->apellido2_profesor;
        $profesor->id_departamento = $request->id_departamento;
        $profesor->Categoria_docente = $request->categoría_docente;
        $profesor->Categoria_cientifica = $request->categoría_científica;
        $profesor->save();
    }

    public function eliminar(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario,
            ]);

            if ($validator->fails()) {
                return redirect()->route($this->rutaVistaPrincipal)
                    ->with('error', 'El usuario no existe o ya ha sido eliminado');
            }

            $user = $this->modelo::find($request->id);
            if (!$user) {
                return redirect()->route($this->rutaVistaPrincipal)
                    ->with('error', 'El usuario no existe');
            }

            // No permitir eliminarse a sí mismo
            if (Auth::id() === $user->id) {
                return redirect()->route($this->rutaVistaPrincipal)
                    ->with('error', 'No puede eliminar su propio usuario');
            }

            if ($user->estudiante) $user->estudiante->delete();
            if ($user->profesor)   $user->profesor->delete();

            $user->delete();

            DB::commit();
            return redirect()->route($this->rutaVistaPrincipal);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Elimina varios usuarios a la vez (recibe ids[]).
     */
    public function eliminarVarios(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Debe seleccionar al menos un usuario para eliminar');
        }

        $ids = array_filter(array_map('intval', $ids), fn($id) => $id > 0);

        if (count($ids) === 0) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Los IDs enviados no son válidos');
        }

        // Evitar eliminar el propio usuario
        if (in_array(Auth::id(), $ids)) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'No puede eliminar su propio usuario');
        }

        DB::beginTransaction();
        try {
            $usuarios = $this->modelo::whereIn($this->columnaIdUsuario, $ids)->get();

            foreach ($usuarios as $u) {
                if ($u->estudiante) $u->estudiante->delete();
                if ($u->profesor)   $u->profesor->delete();
                $u->delete();
            }

            DB::commit();
            return redirect()->route($this->rutaVistaPrincipal);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al eliminar los usuarios: ' . $e->getMessage());
        }
    }

    /**
     * Exporta los usuarios a CSV.
     */
    public function exportarCsv()
    {
        $usuarios = $this->modelo::with(['rol', 'estudiante', 'profesor'])
            ->orderBy($this->columnaName)
            ->get();

        $nombreArchivo = 'usuarios_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($usuarios) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Usuario', 'Email', 'Rol', 'Nombre completo'], ';');

            foreach ($usuarios as $u) {
                $nombreCompleto = '—';
                if ($u->estudiante) {
                    $nombreCompleto = trim($u->estudiante->Nombre_estudiante . ' ' .
                        $u->estudiante->Apellido1 . ' ' .
                        $u->estudiante->Apellido2);
                } elseif ($u->profesor) {
                    $nombreCompleto = trim($u->profesor->Nombre_profesor . ' ' .
                        $u->profesor->Apellido1 . ' ' .
                        $u->profesor->Apellido2);
                }

                fputcsv($out, [
                    $u->{$this->columnaName},
                    $u->{$this->columnaEmail},
                    $u->rol->rol ?? 'Sin rol',
                    $nombreCompleto,
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function perfil()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')
                    ->with('error', 'Debe iniciar sesión para ver su perfil');
            }
            return view('perfil', compact('user'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar el perfil: ' . $e->getMessage());
        }
    }

    public function showAdminRegistrationForm()
    {
        if ($this->modelo::count() > 0) {
            return redirect()->route('login')
                ->with('error', 'El sistema ya tiene usuarios registrados. Use el login normal.');
        }
        return view('registrarAdmin');
    }

    private function setupInitialSystem(): void
    {
        if ($this->modelo::count() > 0) return;

        DB::beginTransaction();
        try {
            $adminRoleId = DB::table('roles')->insertGetId([
                'rol' => 'Administrador',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permisos = [
                ['permiso' => 'gestionarFacultad'], ['permiso' => 'gestionarCarrera'],
                ['permiso' => 'gestionarModalidad'], ['permiso' => 'gestionarGrupos'],
                ['permiso' => 'gestionarDepartamento'], ['permiso' => 'gestionarTesis'],
                ['permiso' => 'gestionarCortes'], ['permiso' => 'gestionarNoConformidades'],
                ['permiso' => 'subirCorte'], ['permiso' => 'revisarCorte'],
                ['permiso' => 'revisarFundamentación'], ['permiso' => 'gestionarUsuarios'],
                ['permiso' => 'gestionarRoles'], ['permiso' => 'gestionarPermisos'],
                ['permiso' => 'inicio'], ['permiso' => 'consultas'],
                ['permiso' => 'estudiantes'], ['permiso' => 'profesores'],
                ['permiso' => 'buscarEstudiante'], ['permiso' => 'estudiantes_sin_tutor'],
                ['permiso' => 'estudiantesAtrasadosFundamentación'], ['permiso' => 'estudiantesCursoDiurno'],
                ['permiso' => 'estudiantesCursoEncuentro'], ['permiso' => 'estudiantesFacultad'],
                ['permiso' => 'buscarProfesor'], ['permiso' => 'profesoresDepartamento'],
                ['permiso' => 'profesoresDoctores'], ['permiso' => 'profesoresMáster'],
                ['permiso' => 'profesoresNoTutores'], ['permiso' => 'mostrar_estudiante'],
                ['permiso' => 'mostrar_profesor'], ['permiso' => 'crearUsuario'],
                ['permiso' => 'perfil'], ['permiso' => 'verUsuario'],
                ['permiso' => 'editarUsuario'], ['permiso' => 'crearFundamentación'],
                ['permiso' => 'editarFundamentación'], ['permiso' => 'crearCorte'],
                ['permiso' => 'editarCorte'], ['permiso' => 'verCorte'],
                ['permiso' => 'verFundamentación'], ['permiso' => 'agregarRecomendacionFundamentacion'],
                ['permiso' => 'editarRecomendacionFundamentacion'], ['permiso' => 'agregarNoConformidadCorte'],
                ['permiso' => 'editarNoConformidadCorte'], ['permiso' => 'vincularProfesorCorte'],
                ['permiso' => 'vincularProfesorFundamentación'], ['permiso' => 'asignarTutor'],
                ['permiso' => 'agregarCarrera'], ['permiso' => 'verCarrera'],
                ['permiso' => 'editarCarrera'], ['permiso' => 'crearTesis'],
                ['permiso' => 'editarTesis'], ['permiso' => 'verTesis'],
                ['permiso' => 'gestionarFundamentaciones'], ['permiso' => 'subirFundamentación'],
                ['permiso' => 'fechaEntrega'], ['permiso' => 'revisarFundamentaciónEstudiante'],
                ['permiso' => 'revisarCorteEstudiante'], ['permiso' => 'estudiantesTutorados'],
                ['permiso' => 'revisarEstudianteTutorado'],
            ];

            $permisoIds = [];
            $id = 1;
            foreach ($permisos as $permiso) {
                $existing = DB::table('permisos')->where('permiso', $permiso['permiso'])->first();
                if ($existing) {
                    $permisoIds[] = $existing->id;
                } else {
                    DB::table('permisos')->insert([
                        'id' => $id,
                        'permiso' => $permiso['permiso'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $permisoIds[] = $id;
                    $id++;
                }
            }

            $rolesPermisosData = [];
            foreach ($permisoIds as $permisoId) {
                $exists = DB::table('roles_permisos')
                    ->where('id_rol', $adminRoleId)
                    ->where('id_permiso', $permisoId)
                    ->exists();
                if (!$exists) {
                    $rolesPermisosData[] = [
                        'id_rol' => $adminRoleId,
                        'id_permiso' => $permisoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($rolesPermisosData)) {
                DB::table('roles_permisos')->insert($rolesPermisosData);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error al configurar el sistema: ' . $e->getMessage());
        }
    }

    public function registerFirstAdmin(Request $request)
    {
        if ($this->modelo::count() > 0) {
            return redirect()->route('login')
                ->with('error', 'Ya existe un usuario administrador. Use el login normal.');
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:40', 'unique:' . $this->tablaUsuario . ',' . $this->columnaName],
            'email' => ['required', 'email', 'max:255', 'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $this->setupInitialSystem();
            $adminRole = DB::table('roles')->where('rol', 'Administrador')->first();

            if (!$adminRole) {
                throw new \Exception('No se pudo crear el rol Administrador');
            }

            $user = new $this->modelo();
            $user->{$this->columnaName} = $request->name;
            $user->{$this->columnaEmail} = $request->email;
            $user->{$this->columnaRol} = $adminRole->id;
            $user->{$this->columnaPassword} = Hash::make($request->password);
            $user->save();

            DB::commit();

            return redirect()->route('login')
                ->with('success', '¡Administrador creado exitosamente! Ahora puede iniciar sesión.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error al crear el administrador: ' . $e->getMessage())
                ->withInput();
        }
    }
}