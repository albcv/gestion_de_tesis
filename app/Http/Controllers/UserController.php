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
    
    // Columnas
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
    
    // IDs de roles (ahora obtenidos por nombre)
    protected $rolEstudianteId;
    protected $rolProfesorId;
    protected $rolAdministradorId;
    
    // Rutas
    protected $rutaVistaPrincipal = 'gestionarUsuarios';
    protected $rutaCrearUsuario = 'crearUsuario';
    
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
        
        // Instanciar modelos para obtener metadatos
        $instanciaUsuario = new $this->modelo;
        $instanciaRol = new $this->modeloRol;
        $instanciaEstudiante = new $this->modeloEstudiante;
        $instanciaProfesor = new $this->modeloProfesor;
        $instanciaGrupo = new $this->modeloGrupo;
        $instanciaModalidad = new $this->modeloModalidad;
        $instanciaDepartamento = new $this->modeloDepartamento;
        $instanciaCarrera = new $this->modeloCarrera;
        
        // Obtener nombres de tablas
        $this->tablaUsuario = $instanciaUsuario->getTable();
        $this->tablaRol = $instanciaRol->getTable();
        $this->tablaEstudiante = $instanciaEstudiante->getTable();
        $this->tablaProfesor = $instanciaProfesor->getTable();
        $this->tablaGrupo = $instanciaGrupo->getTable();
        $this->tablaModalidad = $instanciaModalidad->getTable();
        $this->tablaDepartamento = $instanciaDepartamento->getTable();
        $this->tablaCarrera = $instanciaCarrera->getTable();
        
        // Obtener nombres de columnas clave
        $this->columnaIdUsuario = $instanciaUsuario->getKeyName();
        $this->columnaIdRol = $instanciaRol->getKeyName();
        $this->columnaIdEstudiante = $instanciaEstudiante->getKeyName();
        $this->columnaIdProfesor = $instanciaProfesor->getKeyName();
        $this->columnaIdGrupo = $instanciaGrupo->getKeyName();
        $this->columnaIdModalidad = $instanciaModalidad->getKeyName();
        $this->columnaIdDepartamento = $instanciaDepartamento->getKeyName();
        $this->columnaIdCarrera = $instanciaCarrera->getKeyName();
        
        // Definir nombres de columnas específicas
        $this->columnaName = 'name';
        $this->columnaEmail = 'email';
        $this->columnaRol = 'id_rol';
        $this->columnaPassword = 'password';
        
        // Obtener IDs de roles por nombre
        $this->obtenerIdsDeRoles();
    }
    
    /**
     * Obtener los IDs de los roles por su nombre
     */
    private function obtenerIdsDeRoles(): void
    {
        // Obtener rol de Administrador
        $rolAdmin = $this->modeloRol::where('rol', self::ROL_ADMINISTRADOR)->first();
        $this->rolAdministradorId = $rolAdmin ? $rolAdmin->id : null;
        
        // Obtener rol de Profesor
        $rolProfesor = $this->modeloRol::where('rol', self::ROL_PROFESOR)->first();
        $this->rolProfesorId = $rolProfesor ? $rolProfesor->id : null;
        
        // Obtener rol de Estudiante
        $rolEstudiante = $this->modeloRol::where('rol', self::ROL_ESTUDIANTE)->first();
        $this->rolEstudianteId = $rolEstudiante ? $rolEstudiante->id : null;
        
    
    }

    public function mostrar(Request $request)
    {
        try {
            // Obtener parámetros de búsqueda y filtros
            $buscar = $request->input('buscar');
            $filtroRol = $request->input('filtro_rol');
            $porPagina = $request->input('por_pagina', 10);
            
            // Construir la consulta
            $query = $this->modelo::with(['rol', 'estudiante', 'profesor']);
            
            // Aplicar búsqueda si existe
            if ($buscar) {
                $query->where(function($q) use ($buscar) {
                    $q->where($this->columnaName, 'LIKE', "%{$buscar}%")
                      ->orWhere($this->columnaEmail, 'LIKE', "%{$buscar}%")
                      ->orWhereHas('estudiante', function($q) use ($buscar) {
                          $q->where('Nombre_estudiante', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                            ->orWhere('CI_estudiante', 'LIKE', "%{$buscar}%");
                      })
                      ->orWhereHas('profesor', function($q) use ($buscar) {
                          $q->where('Nombre_profesor', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido1', 'LIKE', "%{$buscar}%")
                            ->orWhere('Apellido2', 'LIKE', "%{$buscar}%")
                            ->orWhere('CI_profesor', 'LIKE', "%{$buscar}%");
                      });
                });
            }
            
            // Aplicar filtro por rol (usando nombres en lugar de IDs)
            if ($filtroRol) {
                if (strtolower($filtroRol) === strtolower(self::ROL_ESTUDIANTE)) {
                    $query->where($this->columnaRol, $this->rolEstudianteId);
                } elseif (strtolower($filtroRol) === strtolower(self::ROL_PROFESOR)) {
                    $query->where($this->columnaRol, $this->rolProfesorId);
                } elseif (strtolower($filtroRol) === strtolower(self::ROL_ADMINISTRADOR)) {
                    $query->where($this->columnaRol, $this->rolAdministradorId);
                } else {
                    // Si es un ID numérico, filtrar directamente
                    if (is_numeric($filtroRol)) {
                        $query->where($this->columnaRol, $filtroRol);
                    } else {
                        // Intentar encontrar el rol por nombre
                        $rol = $this->modeloRol::where('rol', 'LIKE', "%{$filtroRol}%")->first();
                        if ($rol) {
                            $query->where($this->columnaRol, $rol->id);
                        }
                    }
                }
            }
            
            // Obtener los usuarios con paginación
            $usuarios = $query->paginate($porPagina);
            
            // Obtener datos adicionales para la vista
            $roles = $this->modeloRol::all();
            $grupos = $this->modeloGrupo::all();
            $modalidades = $this->modeloModalidad::all();
            $departamentos = $this->modeloDepartamento::all();
            $carreras = $this->modeloCarrera::all();
            
            return view('gestionar.gestionarUsuarios.gestionarUsuarios', compact(
                'usuarios', 
                'roles', 
                'grupos', 
                'modalidades', 
                'departamentos',
                'carreras'
            ));
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cargar la lista de usuarios: ' . $e->getMessage());
        }
    }

    public function crearUsuario()
    {
        try {
            $roles = $this->modeloRol::all();
            $grupos = $this->modeloGrupo::all();
            $modalidades = $this->modeloModalidad::all();
            $departamentos = $this->modeloDepartamento::all();
            $carreras = $this->modeloCarrera::all();

            return view('gestionar.gestionarUsuarios.crearUsuario', compact(
                'roles', 'grupos', 'modalidades', 'departamentos', 'carreras'
            ));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar el formulario de creación: ' . $e->getMessage());
        }
    }
    
    public function agregar(Request $request)
    {
        DB::beginTransaction();
        
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:40',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaName
                ],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail
                ],
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'max:255'
                ],
                'rol' => [
                    'required',
                    'exists:' . $this->tablaRol . ',' . $this->columnaIdRol
                ],
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
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Crear el usuario
            $user = new $this->modelo();
            $user->{$this->columnaName} = $request->name;
            $user->{$this->columnaEmail} = $request->email;
            $user->{$this->columnaRol} = $request->rol;
            $user->{$this->columnaPassword} = Hash::make($request->password);
            $user->save();
            
            // Si el rol es Estudiante
            if ($request->rol == $this->rolEstudianteId) {
                $this->agregarEstudiante($request, $user);
            } 
            // Si el rol es Profesor
            elseif ($request->rol == $this->rolProfesorId) {
                $this->agregarProfesor($request, $user);
            }
            
            DB::commit();
            
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('success', 'Usuario creado exitosamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    private function agregarEstudiante($request, $user)
    {
        $validator = Validator::make($request->all(), [
            'ci_estudiante' => [
                'required',
                'digits:11',
                'unique:' . $this->tablaEstudiante . ',CI_estudiante',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = substr($fecha, 2, 2);
                    $day = substr($fecha, 4, 2);
                    
                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    
                    $diasPorMes = [
                        1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
                        7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31
                    ];
                    
                    if ($day < 1 || $day > $diasPorMes[(int)$month]) {
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
            'id_grupo' => [
                'required',
                'exists:' . $this->tablaGrupo . ',' . $this->columnaIdGrupo
            ],
            'id_modalidad' => [
                'required',
                'exists:' . $this->tablaModalidad . ',' . $this->columnaIdModalidad
            ],
            'id_carrera' => [
                'required',
                'exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera
            ],
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
                'required',
                'digits:11',
                'unique:' . $this->tablaProfesor . ',CI_profesor',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = substr($fecha, 2, 2);
                    $day = substr($fecha, 4, 2);
                    
                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    
                    $diasPorMes = [
                        1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
                        7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31
                    ];
                    
                    if ($day < 1 || $day > $diasPorMes[(int)$month]) {
                        $fail('Los dígitos 5-6 del CI deben representar un día válido para el mes.');
                    }
                }
            ],
            'nombre_profesor' => 'required|string|min:3|max:40',
            'apellido1_profesor' => 'required|string|min:3|max:40',
            'apellido2_profesor' => 'required|string|min:3|max:40',
            'id_departamento' => [
                'required',
                'exists:' . $this->tablaDepartamento . ',' . $this->columnaIdDepartamento
            ],
            'categoría_docente' => 'required|string|in:Profesor Titular,Profesor Instructor,Profesor Auxiliar',
            'categoría_científica' => 'required|string|in:Licenciado,Ingeniero,Máster en Ciencias,Doctor en Ciencias',
        ], [
            'ci_profesor.required' => 'El carnet de identidad es obligatorio',
            'ci_profesor.digits' => 'El carnet de identidad debe tener exactamente 11 dígitos',
            'ci_profesor.unique' => 'Este carnet de identidad ya está registrado',
            'nombre_profesor.required' => 'El nombre es obligatorio',
            'nombre_profesor.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre_profesor.max' => 'El nombre no puede exceder los 40 caracteres',
            'apellido1_profesor.required' => 'El primer apellido es obligatorio',
            'apellido1_profesor.min' => 'El primer apellido debe tener al menos 3 caracteres',
            'apellido1_profesor.max' => 'El primer apellido no puede exceder los 40 caracteres',
            'apellido2_profesor.required' => 'El segundo apellido es obligatorio',
            'apellido2_profesor.min' => 'El segundo apellido debe tener al menos 3 caracteres',
            'apellido2_profesor.max' => 'El segundo apellido no puede exceder los 40 caracteres',
            'id_departamento.required' => 'El departamento es obligatorio',
            'id_departamento.exists' => 'El departamento seleccionado no existe',
            'categoría_docente.in' => 'La categoría docente debe ser una de las opciones disponibles',
            'categoría_científica.in' => 'La categoría científica debe ser una de las opciones disponibles',
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
                'id' => [
                    'required',
                    'exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario
                ],
            ]);
            
            if ($validator->fails()) {
                return redirect()->route($this->rutaVistaPrincipal)
                    ->with('error', 'El usuario no existe');
            }
            
            $usuario = $this->modelo::with([
                'rol',
                'estudiante' => function($query) {
                    $query->with(['grupo', 'modalidad', 'carrera']);
                },
                'profesor' => function($query) {
                    $query->with(['departamento']);
                }
            ])->findOrFail($id);
            
            return view('gestionar.gestionarUsuarios.verUsuario', compact('usuario'));
            
        } catch (\Exception $e) {
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('error', 'Error al cargar los datos del usuario: ' . $e->getMessage());
        }
    }

    public function editar($id)
    {
        try {
            $validator = Validator::make(['id' => $id], [
                'id' => [
                    'required',
                    'exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario
                ],
            ]);
            
            if ($validator->fails()) {
                return redirect()->route($this->rutaVistaPrincipal)
                    ->with('error', 'El usuario no existe');
            }
            
            $usuario = $this->modelo::with([
                'rol',
                'estudiante' => function($query) {
                    $query->with(['grupo', 'modalidad', 'carrera']);
                },
                'profesor' => function($query) {
                    $query->with(['departamento']);
                }
            ])->findOrFail($id);
            
            $roles = $this->modeloRol::all();
            $grupos = $this->modeloGrupo::all();
            $modalidades = $this->modeloModalidad::all();
            $departamentos = $this->modeloDepartamento::all();
            $carreras = $this->modeloCarrera::all();
            
            return view('gestionar.gestionarUsuarios.editarUsuario', compact(
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
                'id' => [
                    'required',
                    'exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario
                ],
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:40',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaName . ',' . $request->id . ',' . $this->columnaIdUsuario
                ],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail . ',' . $request->id . ',' . $this->columnaIdUsuario
                ],
                'rol' => [
                    'required',
                    'exists:' . $this->tablaRol . ',' . $this->columnaIdRol
                ],
            ], [
                'id.required' => 'ID de usuario es requerido',
                'id.exists' => 'El usuario no existe',
                'name.required' => 'El nombre de usuario es obligatorio',
                'name.min' => 'El nombre de usuario debe tener al menos 3 caracteres',
                'name.max' => 'El nombre de usuario no puede exceder los 40 caracteres',
                'name.unique' => 'Este nombre de usuario ya está registrado',
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'El correo electrónico debe ser válido',
                'email.max' => 'El correo electrónico no puede exceder los 255 caracteres',
                'email.unique' => 'Este correo electrónico ya está registrado',
                'rol.required' => 'El rol es obligatorio',
                'rol.exists' => 'El rol seleccionado no existe',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Validar contraseña si se proporciona
            if ($request->filled('password')) {
                $passwordValidator = Validator::make($request->all(), [
                    'password' => 'string|min:6|max:255'
                ], [
                    'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                    'password.max' => 'La contraseña no puede exceder los 255 caracteres',
                ]);
                
                if ($passwordValidator->fails()) {
                    return redirect()->back()
                        ->withErrors($passwordValidator)
                        ->withInput();
                }
            }
            
            // Buscar el usuario
            $user = $this->modelo::findOrFail($request->id);
            
            // Guardar datos antiguos
            $oldData = $user->toArray();
            
            // Actualizar datos básicos del usuario
            $user->{$this->columnaName} = $request->name;
            $user->{$this->columnaEmail} = $request->email;
            $user->{$this->columnaRol} = $request->rol;
            
            // Actualizar contraseña si se proporciona
            if ($request->filled('password')) {
                $user->{$this->columnaPassword} = Hash::make($request->password);
            }
            
            $user->save();
            
            // Si el rol es Estudiante
            if ($request->rol == $this->rolEstudianteId) {
                $this->actualizarEstudiante($request, $user);
            } 
            // Si el rol es Profesor
            elseif ($request->rol == $this->rolProfesorId) {
                $this->actualizarProfesor($request, $user);
            } else {
                // Si cambia a un rol que no es estudiante ni profesor, eliminar perfiles existentes
                if ($user->estudiante) {
                    $user->estudiante->delete();
                }
                if ($user->profesor) {
                    $user->profesor->delete();
                }
            }
            
            DB::commit();
            
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('success', 'Usuario actualizado correctamente');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    private function actualizarEstudiante($request, $user)
    {
        $estudianteId = $user->estudiante ? $user->estudiante->id : null;
        
        $validator = Validator::make($request->all(), [
            'ci_estudiante' => [
                'required',
                'digits:11',
                'unique:' . $this->tablaEstudiante . ',CI_estudiante,' . $estudianteId . ',id',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = substr($fecha, 2, 2);
                    $day = substr($fecha, 4, 2);
                    
                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    
                    $diasPorMes = [
                        1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
                        7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31
                    ];
                    
                    if ($day < 1 || $day > $diasPorMes[(int)$month]) {
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
            'id_grupo' => [
                'required',
                'exists:' . $this->tablaGrupo . ',' . $this->columnaIdGrupo
            ],
            'id_modalidad' => [
                'required',
                'exists:' . $this->tablaModalidad . ',' . $this->columnaIdModalidad
            ],
            'id_carrera' => [
                'required',
                'exists:' . $this->tablaCarrera . ',' . $this->columnaIdCarrera
            ],
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
        
        // Si ya era profesor, eliminar perfil de profesor
        if ($user->profesor) {
            $user->profesor->delete();
        }
        
        // Actualizar o crear estudiante
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
                'required',
                'digits:11',
                'unique:' . $this->tablaProfesor . ',CI_profesor,' . $profesorId . ',id',
                function ($attribute, $value, $fail) {
                    $fecha = substr($value, 0, 6);
                    $month = substr($fecha, 2, 2);
                    $day = substr($fecha, 4, 2);
                    
                    if ($month < 1 || $month > 12) {
                        $fail('Los dígitos 3-4 del CI deben representar un mes válido (01-12).');
                    }
                    
                    $diasPorMes = [
                        1 => 31, 2 => 29, 3 => 31, 4 => 30, 5 => 31, 6 => 30,
                        7 => 31, 8 => 31, 9 => 30, 10 => 31, 11 => 30, 12 => 31
                    ];
                    
                    if ($day < 1 || $day > $diasPorMes[(int)$month]) {
                        $fail('Los dígitos 5-6 del CI deben representar un día válido para el mes.');
                    }
                }
            ],
            'nombre_profesor' => 'required|string|min:3|max:40',
            'apellido1_profesor' => 'required|string|min:3|max:40',
            'apellido2_profesor' => 'required|string|min:3|max:40',
            'id_departamento' => [
                'required',
                'exists:' . $this->tablaDepartamento . ',' . $this->columnaIdDepartamento
            ],
            'categoría_docente' => 'required|string|in:Profesor Titular,Profesor Instructor,Profesor Auxiliar',
            'categoría_científica' => 'required|string|in:Licenciado,Ingeniero,Máster en Ciencias,Doctor en Ciencias',
        ], [
            'ci_profesor.required' => 'El carnet de identidad es obligatorio',
            'ci_profesor.digits' => 'El carnet de identidad debe tener exactamente 11 dígitos',
            'ci_profesor.unique' => 'Este carnet de identidad ya está registrado',
            'nombre_profesor.required' => 'El nombre es obligatorio',
            'nombre_profesor.min' => 'El nombre debe tener al menos 3 caracteres',
            'nombre_profesor.max' => 'El nombre no puede exceder los 40 caracteres',
            'apellido1_profesor.required' => 'El primer apellido es obligatorio',
            'apellido1_profesor.min' => 'El primer apellido debe tener al menos 3 caracteres',
            'apellido1_profesor.max' => 'El primer apellido no puede exceder los 40 caracteres',
            'apellido2_profesor.required' => 'El segundo apellido es obligatorio',
            'apellido2_profesor.min' => 'El segundo apellido debe tener al menos 3 caracteres',
            'apellido2_profesor.max' => 'El segundo apellido no puede exceder los 40 caracteres',
            'id_departamento.required' => 'El departamento es obligatorio',
            'id_departamento.exists' => 'El departamento seleccionado no existe',
            'categoría_docente.in' => 'La categoría docente debe ser una de las opciones disponibles',
            'categoría_científica.in' => 'La categoría científica debe ser una de las opciones disponibles',
        ]);
        
        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }
        
        // Si ya era estudiante, eliminar perfil de estudiante
        if ($user->estudiante) {
            $user->estudiante->delete();
        }
        
        // Actualizar o crear profesor
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
                'id' => [
                    'required',
                    'exists:' . $this->tablaUsuario . ',' . $this->columnaIdUsuario
                ],
            ], [
                'id.required' => 'ID de usuario es requerido',
                'id.exists' => 'El usuario no existe',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->with('error', 'El usuario no existe o ya ha sido eliminado');
            }
            
            $user = $this->modelo::find($request->id);
            
            if (!$user) {
                return redirect()->back()
                    ->with('error', 'El usuario no existe');
            }
            
            // Eliminar registros relacionados
            if ($user->estudiante) {
                $user->estudiante->delete();
            }
            if ($user->profesor) {
                $user->profesor->delete();
            }
            
            // Eliminar usuario
            $user->delete();
            
            DB::commit();
            
            return redirect()->route($this->rutaVistaPrincipal)
                ->with('success', 'Usuario eliminado correctamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
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
    // Verificar si ya existen usuarios
    if ($this->modelo::count() > 0) {
        return redirect()->route('login')
            ->with('error', 'El sistema ya tiene usuarios registrados. Use el login normal.');
    }
    
    return view('auth.registrarAdmin');
}

public function registerFirstAdmin(Request $request)
{
    // Verificar que no haya usuarios existentes
    if ($this->modelo::count() > 0) {
        return redirect()->route('login')
            ->with('error', 'Ya existe un usuario administrador. Use el login normal.');
    }
    
    // Validar datos
    $validator = Validator::make($request->all(), [
        'name' => [
            'required',
            'string',
            'min:3',
            'max:40',
            'unique:' . $this->tablaUsuario . ',' . $this->columnaName
        ],
        'email' => [
            'required',
            'email',
            'max:255',
            'unique:' . $this->tablaUsuario . ',' . $this->columnaEmail
        ],
        'password' => [
            'required',
            'string',
            'min:6',
            'confirmed'
        ],
    ], [
        'name.required' => 'El nombre de usuario es obligatorio',
        'name.min' => 'El nombre debe tener al menos 3 caracteres',
        'name.max' => 'El nombre no puede exceder 40 caracteres',
        'name.unique' => 'Este nombre de usuario ya está registrado',
        'email.required' => 'El correo electrónico es obligatorio',
        'email.email' => 'El correo electrónico debe ser válido',
        'email.max' => 'El correo electrónico no puede exceder 255 caracteres',
        'email.unique' => 'Este correo electrónico ya está registrado',
        'password.required' => 'La contraseña es obligatoria',
        'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        'password.confirmed' => 'Las contraseñas no coinciden',
    ]);
    
    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }
    
    // Asegurar que exista el rol de Administrador
    $this->obtenerIdsDeRoles();
    
    if (!$this->rolAdministradorId) {
        // Si no existe el rol, crear uno básico
        $rol = new $this->modeloRol();
        $rol->rol = self::ROL_ADMINISTRADOR;
        $rol->save();
        $this->rolAdministradorId = $rol->id;
    }
    
    // Crear el usuario administrador
    $user = new $this->modelo();
    $user->{$this->columnaName} = $request->name;
    $user->{$this->columnaEmail} = $request->email;
    $user->{$this->columnaRol} = $this->rolAdministradorId;
    $user->{$this->columnaPassword} = Hash::make($request->password);
    $user->save();
    
    return redirect()->route('login')
        ->with('success', '¡Administrador creado exitosamente! Ahora puede iniciar sesión.');
}


}