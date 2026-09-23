<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\facultadController;
use App\Http\Controllers\carreraController;
use App\Http\Controllers\modalidadController;
use App\Http\Controllers\estudianteController;
use App\Http\Controllers\profesorController;
use App\Http\Controllers\cortesController;
use App\Http\Controllers\NoConformidadesController;
use App\Http\Controllers\cortesNoConformidadesController;
use App\Http\Controllers\cortesProfesorController;
use App\Http\Controllers\tesisController;
use App\Http\Controllers\departamentoController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\fundamentacionesController;
use App\Http\Controllers\fundamentacionesAprobadasController;
use App\Http\Controllers\recomendacionesFundamentacionController;
use App\Http\Controllers\cortesAprobadosController;
use App\Http\Controllers\tutorEstudianteController;
use App\Http\Controllers\gruposController;
use App\Http\Controllers\rolesController;
use App\Http\Controllers\permisosController;
use App\Http\Controllers\ProfesorFundamentaciónController;
use App\Http\Controllers\estadisticasController;
use App\Http\Controllers\CambiarContraseñaController;
use Illuminate\Support\Facades\Route;
use App\Models\User;


Route::get('/', function(){

    $usuarios = User::all();

    if(count($usuarios)==0){
        return redirect(route('registrarAdmin'));
    }

    return view('login');

});

//Registrar administrador

Route::get('/registrar-admin', [UserController::class, 'showAdminRegistrationForm'])->name('registrarAdmin');
Route::post('/registrar-admin', [UserController::class, 'registerFirstAdmin'])->name('registrarAdmin.post');

// Autenticación de usuario
Route::view('/login', 'login')->name('login');
Route::view('/inicio', 'inicio')->name('inicio');
Route::post('/inicioSesion', [loginController::class, 'login'])->name('inicioSesion');
Route::post('/logout', [loginController::class, 'logout'])->name('logout');


// Gestionar Usuarios
Route::get('/gestionarUsuarios',            [UserController::class, 'mostrar'])->name('gestionarUsuarios');
Route::get('/crearUsuario',                 [UserController::class, 'crearUsuario'])->name('crearUsuario');
Route::post('/agregarUsuario',              [UserController::class, 'agregar'])->name('agregarUsuario');
Route::get('/editarUsuario/{id}',           [UserController::class, 'editar'])->name('editarUsuario');
Route::post('/actualizarUsuario',           [UserController::class, 'actualizar'])->name('actualizarUsuario');
Route::get('/verUsuario/{id}',              [UserController::class, 'ver'])->name('verUsuario');
Route::post('/eliminarUsuario',             [UserController::class, 'eliminar'])->name('eliminarUsuario');
Route::post('/eliminarVariosUsuarios',      [UserController::class, 'eliminarVarios'])->name('eliminarVariosUsuarios');
Route::get('/exportarUsuariosCsv',          [UserController::class, 'exportarCsv'])->name('exportarUsuariosCsv');



Route::view('/gestionar', 'gestionar.gestionar')->name('gestionar');


//Facultad
Route::get('/gestionarFacultad', [facultadController::class, 'mostrar'])->name('gestionarFacultad');
Route::post('/agregarFacultad', [facultadController::class, 'agregar'])->name('agregarFacultad');
Route::post('/eliminarFacultad', [facultadController::class, 'eliminar'])->name('eliminarFacultad');
Route::post('/eliminarVariasFacultades', [facultadController::class, 'eliminarVarios'])->name('eliminarVariasFacultades');
Route::post('/modificarFacultad', [facultadController::class, 'modificar'])->name('modificarFacultad');
Route::post('/vaciarFacultad', [facultadController::class, 'vaciar'])->name('vaciarFacultad');
Route::get('/exportarFacultadesCsv',     [facultadController::class, 'exportarCsv'])->name('exportarFacultadesCsv');


// Carrera
Route::get('/gestionarCarrera',         [carreraController::class, 'mostrar'])->name('gestionarCarrera');
Route::get('/agregarCarrera',           [carreraController::class, 'mostrarAgregar'])->name('agregarCarrera');
Route::post('/agregarCarrera',          [carreraController::class, 'agregar'])->name('agregarCarrera_post');
Route::get('/editarCarrera/{id}',       [carreraController::class, 'mostrarEditar'])->name('editarCarrera');
Route::post('/modificarCarrera',        [carreraController::class, 'modificar'])->name('modificarCarrera');
Route::get('/verCarrera/{id}',          [carreraController::class, 'mostrarDetalles'])->name('verCarrera');
Route::post('/eliminarCarrera',         [carreraController::class, 'eliminar'])->name('eliminarCarrera');
Route::post('/eliminarVariasCarreras',  [carreraController::class, 'eliminarVarios'])->name('eliminarVariasCarreras');
Route::get('/exportarCarrerasCsv',      [carreraController::class, 'exportarCsv'])->name('exportarCarrerasCsv');


// Modalidad
Route::get('/gestionarModalidad',       [modalidadController::class, 'mostrar'])->name('gestionarModalidad');
Route::post('/agregarModalidad',        [modalidadController::class, 'agregar'])->name('agregarModalidad');
Route::post('/eliminarModalidad',       [modalidadController::class, 'eliminar'])->name('eliminarModalidad');
Route::post('/eliminarVariasModalidades', [modalidadController::class, 'eliminarVarios'])->name('eliminarVariasModalidades');
Route::post('/modificarModalidad',      [modalidadController::class, 'modificar'])->name('modificarModalidad');
Route::post('/vaciarModalidad',         [modalidadController::class, 'vaciar'])->name('vaciarModalidad');
Route::get('/exportarModalidadesCsv',   [modalidadController::class, 'exportarCsv'])->name('exportarModalidadesCsv');

//Grupo
Route::get('/gestionarGrupos',         [gruposController::class, 'mostrar'])->name('gestionarGrupos');
Route::post('/agregarGrupo',           [gruposController::class, 'agregar'])->name('agregarGrupo');
Route::post('/eliminarGrupo',          [gruposController::class, 'eliminar'])->name('eliminarGrupo');
Route::post('/eliminarVariosGrupos',   [gruposController::class, 'eliminarVarios'])->name('eliminarVariosGrupos');
Route::post('/modificarGrupo',         [gruposController::class, 'modificar'])->name('modificarGrupo');
Route::post('/vaciarGrupo',            [gruposController::class, 'vaciar'])->name('vaciarGrupo');
Route::get('/exportarGruposCsv',       [gruposController::class, 'exportarCsv'])->name('exportarGruposCsv');


//Estudiante
Route::get('/gestionarEstudiante', [estudianteController::class, 'mostrar'])->name('gestionarEstudiante');
Route::post('/agregarEstudiante', [estudianteController::class, 'agregar']);
Route::post('/eliminarEstudiante', [estudianteController::class, 'eliminar']);
Route::post('/modificarEstudiante', [estudianteController::class, 'modificar']);
Route::post('/vaciarEstudiante', [estudianteController::class, 'vaciar']);


// Tesis
Route::get('/gestionarTesis',           [TesisController::class, 'mostrar'])->name('gestionarTesis');
Route::get('/crearTesis',               [TesisController::class, 'crearTesis'])->name('crearTesis');
Route::post('/agregarTesis',            [TesisController::class, 'agregar'])->name('agregarTesis');
Route::get('/editarTesis/{id}',         [TesisController::class, 'editar'])->name('editarTesis');
Route::post('/modificarTesis',          [TesisController::class, 'modificar'])->name('modificarTesis');
Route::get('/verTesis/{id}',            [TesisController::class, 'ver'])->name('verTesis');
Route::post('/eliminarTesis',           [TesisController::class, 'eliminar'])->name('eliminarTesis');
Route::post('/eliminarVariasTesis',     [TesisController::class, 'eliminarVarios'])->name('eliminarVariasTesis');
Route::get('/exportarTesisCsv',         [TesisController::class, 'exportarCsv'])->name('exportarTesisCsv');


// Cortes
Route::get('/gestionarCortes',              [cortesController::class, 'mostrar'])->name('gestionarCortes');
Route::get('/crearCorte',                   [cortesController::class, 'crear'])->name('crearCorte');
Route::post('/agregarCorte',                [cortesController::class, 'agregar'])->name('agregarCorte');
Route::get('/editarCorte/{id}',             [cortesController::class, 'editar'])->name('editarCorte');
Route::post('/modificarCorte',              [cortesController::class, 'modificar'])->name('modificarCorte');
Route::get('/cortes/ver/{id}',              [cortesController::class, 'ver'])->name('verCorte');
Route::post('/eliminarCorte',               [cortesController::class, 'eliminar'])->name('eliminarCorte');
Route::post('/eliminarVariosCortes',        [cortesController::class, 'eliminarVarios'])->name('eliminarVariosCortes');
Route::delete('/eliminar-version-corte/{id}',[cortesController::class, 'eliminarVersion'])->name('eliminar-version-corte');
Route::get('/exportarCortesCsv',            [cortesController::class, 'exportarCsv'])->name('exportarCortesCsv');
Route::post('/aprobarCorte',                [cortesController::class, 'aprobarCorte'])->name('aprobarCorte');
Route::post('/desaprobarCorte',             [cortesController::class, 'desaprobarCorte'])->name('desaprobarCorte');
Route::post('/revertirCorte',               [cortesController::class, 'revertirCorte'])->name('revertirCorte');
Route::get('/ver-documento-corte/{id}',     [cortesController::class, 'verDocumento'])->name('ver-documento');
Route::get('/ver-documento-version-corte/{id}', [cortesController::class, 'verDocumentoVersion'])->name('ver-documento-version-corte');



//Cortes aprobados
Route::get('/gestionarCortesAprobados', [cortesAprobadosController::class, 'mostrar'])->name('gestionarCortesAprobados');
Route::post('/agregarCorteAprobado', [cortesAprobadosController::class, 'agregar']);
Route::post('/eliminarCorteAprobado', [cortesAprobadosController::class, 'eliminar']);
Route::post('/modificarCorteAprobado', [cortesAprobadosController::class, 'modificar']);
Route::post('/vaciarCortesAprobados', [cortesAprobadosController::class, 'vaciar']);


// No Conformidades
Route::get('/gestionarNoConformidades',         [NoConformidadesController::class, 'mostrar'])->name('gestionarNoConformidades');
Route::post('/agregarNoConformidades',          [NoConformidadesController::class, 'agregar'])->name('agregarNoConformidades');
Route::post('/eliminarNoConformidades',         [NoConformidadesController::class, 'eliminar'])->name('eliminarNoConformidades');
Route::post('/eliminarVariasNoConformidades',   [NoConformidadesController::class, 'eliminarVarios'])->name('eliminarVariasNoConformidades');
Route::post('/modificarNoConformidades',        [NoConformidadesController::class, 'modificar'])->name('modificarNoConformidades');
Route::post('/vaciarNoConformidades',           [NoConformidadesController::class, 'vaciar'])->name('vaciarNoConformidades');
Route::get('/exportarNoConformidadesCsv',       [NoConformidadesController::class, 'exportarCsv'])->name('exportarNoConformidadesCsv');


// NoConformidadesCorte
Route::get('/agregarNoConformidadCorte/{id_corte}', [cortesNoConformidadesController::class, 'crear'])->name('agregarNoConformidadCorte');
Route::post('/agregarNoConformidadCorteExistente', [cortesNoConformidadesController::class, 'agregarExistente'])->name('agregarNoConformidadCorteExistente');
Route::post('/crearYVincularNoConformidadCorte', [cortesNoConformidadesController::class, 'crearYVincular'])->name('crearYVincularNoConformidadCorte');
Route::get('/editarNoConformidadCorte/{id_corte}/{id_nc}', [cortesNoConformidadesController::class, 'editar'])->name('editarNoConformidadCorte');
Route::post('/actualizarNoConformidadCorte', [cortesNoConformidadesController::class, 'actualizarRelacion'])->name('actualizarNoConformidadCorte');
Route::post('/desvincularNoConformidadCorte', [cortesNoConformidadesController::class, 'desvincular'])->name('desvincularNoConformidadCorte');


// Profesor
Route::get('/gestionarProfesor', [profesorController::class, 'mostrar'])->name('gestionarProfesor');
Route::post('/agregarProfesor', [profesorController::class, 'agregar']);
Route::post('/eliminarProfesor', [profesorController::class, 'eliminar']);
Route::post('/modificarProfesor', [profesorController::class, 'modificar']);
Route::post('/vaciarProfesor', [profesorController::class, 'vaciar']);



// Tutores-estudiantes
Route::get('/asignarTutor/{id_estudiante}', [tutorEstudianteController::class, 'mostrarAsignarTutor'])->name('asignarTutor');
Route::post('/agregarTutorEstudiante', [tutorEstudianteController::class, 'agregar'])->name('agregarTutorEstudiante');
Route::post('/eliminarTutorEstudiante', [tutorEstudianteController::class, 'eliminar'])->name('eliminarTutorEstudiante');


// Departamento
Route::get('/gestionarDepartamento',       [departamentoController::class, 'mostrar'])->name('gestionarDepartamento');
Route::post('/agregarDepartamento',        [departamentoController::class, 'agregar'])->name('agregarDepartamento');
Route::post('/eliminarDepartamento',       [departamentoController::class, 'eliminar'])->name('eliminarDepartamento');
Route::post('/eliminarVariosDepartamentos',[departamentoController::class, 'eliminarVarios'])->name('eliminarVariosDepartamentos');
Route::post('/modificarDepartamento',      [departamentoController::class, 'modificar'])->name('modificarDepartamento');
Route::post('/vaciarDepartamento',         [departamentoController::class, 'vaciar'])->name('vaciarDepartamento');
Route::get('/exportarDepartamentosCsv',    [departamentoController::class, 'exportarCsv'])->name('exportarDepartamentosCsv');



// Vincular profesor a un corte
Route::get('/vincularProfesorCorte/{id}', [cortesProfesorController::class, 'mostrarVincular'])->name('vincularProfesorCorte');
Route::post('/vincularProfesorCorte', [cortesProfesorController::class, 'vincular'])
->name('vincularProfesorCorte.post');
Route::post('/desvincularProfesorCorte', [cortesProfesorController::class, 'desvincular']) ->name('desvincularProfesorCorte');



// Fundamentación
Route::get('/gestionarFundamentaciones',              [fundamentacionesController::class, 'mostrar'])->name('gestionarFundamentaciones');
Route::get('/crearFundamentación',                    [fundamentacionesController::class, 'crear'])->name('crearFundamentación');
Route::post('/agregarFundamentación',                 [fundamentacionesController::class, 'agregar'])->name('agregarFundamentación');
Route::get('/editarFundamentación/{id}',              [fundamentacionesController::class, 'editar'])->name('editarFundamentación');
Route::post('/modificarFundamentación',               [fundamentacionesController::class, 'modificar'])->name('modificarFundamentación');
Route::get('/verFundamentación/{id}',                 [fundamentacionesController::class, 'ver'])->name('verFundamentación');
Route::post('/eliminarFundamentación',                [fundamentacionesController::class, 'eliminar'])->name('eliminarFundamentación');
Route::post('/eliminarVariasFundamentaciones',        [fundamentacionesController::class, 'eliminarVarios'])->name('eliminarVariasFundamentaciones');
Route::delete('/eliminar-version-fundamentacion/{id}',[fundamentacionesController::class, 'eliminarVersion'])->name('eliminar-version-fundamentacion');
Route::get('/exportarFundamentacionesCsv',            [fundamentacionesController::class, 'exportarCsv'])->name('exportarFundamentacionesCsv');
Route::post('/aprobarFundamentación',                 [fundamentacionesController::class, 'aprobar'])->name('aprobarFundamentación');
Route::post('/desaprobarFundamentación',              [fundamentacionesController::class, 'desaprobar'])->name('desaprobarFundamentación');
Route::post('/revertirFundamentación',                [fundamentacionesController::class, 'revertir'])->name('revertirFundamentación');
Route::get('/ver-documento/{id}',                     [fundamentacionesController::class, 'verDocumento'])->name('ver-documento');
Route::get('/ver-documento-version/{id}',             [fundamentacionesController::class, 'verDocumentoVersion'])->name('ver-documento-version');
Route::get('/fundamentaciones-aprobadas',             [fundamentacionesController::class, 'fundamentacionesAprobadas'])->name('fundamentacionesAprobadas');


// Fundamentaciones aprobadas
Route::get('/gestionarFundamentacionesAprobadas', [fundamentacionesAprobadasController::class, 'mostrar'])->name('gestionarFundamentacionesAprobadas');
Route::post('/agregarFundamentaciónAprobada', [fundamentacionesAprobadasController::class, 'agregar']);
Route::post('/eliminarFundamentaciónAprobada', [fundamentacionesAprobadasController::class, 'eliminar']);
Route::post('/modificarFundamentaciónAprobada', [fundamentacionesAprobadasController::class, 'modificar']);
Route::post('/vaciarFundamentacionesAprobadas', [fundamentacionesAprobadasController::class, 'vaciar']);


//Recomendaciones de fundamentaciones
Route::get('/agregarRecomendacionFundamentacion/{id_fundamentacion}', [recomendacionesFundamentacionController::class, 'crear'])->name('agregarRecomendacionFundamentacion');
Route::post('/agregarRecomendacionFundamentacion', [recomendacionesFundamentacionController::class, 'agregar'])->name('agregarRecomendacionFundamentacion.store');
Route::get('/editarRecomendacionFundamentacion/{id}', [recomendacionesFundamentacionController::class, 'editar'])->name('editarRecomendacionFundamentacion');
Route::post('/modificarRecomendacionFundamentacion', [recomendacionesFundamentacionController::class, 'modificar'])->name('modificarRecomendacionFundamentacion');
Route::post('/eliminarRecomendacionFundamentacion', [recomendacionesFundamentacionController::class, 'eliminar'])->name('eliminarRecomendacionFundamentacion');



// Vincular profesor a una fundamentación
Route::get('/vincularProfesorFundamentación/{id}', [ProfesorFundamentaciónController::class, 'mostrarVincular'])->name('vincularProfesorFundamentación');
Route::post('/vincularProfesorFundamentación', [ProfesorFundamentaciónController::class, 'vincular'])->name('vincularProfesorFundamentación.post');
Route::post('/desvincularProfesorFundamentación', [ProfesorFundamentaciónController::class, 'desvincular'])->name('desvincularProfesorFundamentación');
    


// Roles
Route::get('/gestionarRoles',          [rolesController::class, 'mostrar'])->name('gestionarRoles');
Route::post('/agregarRol',             [rolesController::class, 'agregar'])->name('agregarRol');
Route::post('/eliminarRol',            [rolesController::class, 'eliminar'])->name('eliminarRol');
Route::post('/eliminarVariosRoles',    [rolesController::class, 'eliminarVarios'])->name('eliminarVariosRoles');
Route::post('/modificarRol',           [rolesController::class, 'modificar'])->name('modificarRol');
Route::post('/vaciarRol',              [rolesController::class, 'vaciar'])->name('vaciarRol');
Route::get('/exportarRolesCsv',        [rolesController::class, 'exportarCsv'])->name('exportarRolesCsv');
Route::get('/obtenerPermisosRol/{id}', [rolesController::class, 'obtenerPermisosRol'])->name('obtenerPermisosRol');


// Permisos
Route::get('/gestionarPermisos',         [permisosController::class, 'mostrar'])->name('gestionarPermisos');
Route::post('/agregarPermiso',           [permisosController::class, 'agregar'])->name('agregarPermiso');
Route::post('/eliminarPermiso',          [permisosController::class, 'eliminar'])->name('eliminarPermiso');
Route::post('/eliminarVariosPermisos',   [permisosController::class, 'eliminarVarios'])->name('eliminarVariosPermisos');
Route::post('/modificarPermiso',         [permisosController::class, 'modificar'])->name('modificarPermiso');
Route::post('/vaciarPermiso',            [permisosController::class, 'vaciar'])->name('vaciarPermiso');
Route::get('/exportarPermisosCsv',       [permisosController::class, 'exportarCsv'])->name('exportarPermisosCsv');



// Para descargar documento
Route::get('/ver-documento/{id}', [cortesController::class, 'verDocumento'])->name('ver-documento');
Route::get('/ver-fundamentacion-documento/{id}', [fundamentacionesController::class, 'verDocumento'])->name('ver-fundamentacion-documento');




// Perfil de usuario
Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');
Route::get('/cambiarContraseña', [CambiarContraseñaController::class, 'mostrarFormulario'])
        ->name('cambiarContraseña');
Route::post('/cambiarContraseñaProcesar', [CambiarContraseñaController::class, 'cambiarContraseña'])
        ->name('cambiarContraseña.procesar');


// Consultas
Route::view('/consultas', 'consultas.consultas')->name('consultas');

//Estudiantes 
Route::view('/estudiantes', 'consultas.estudiantes')->name('estudiantes');

Route::view('/buscarEstudiante', 'consultas.estudiantes.buscarEstudiante')->name('buscarEstudiante');
Route::post('/mostrar_estudiante', [estudianteController::class, 'buscarEstudiante'])->name('mostrar_estudiante');

Route::get('/estudiantesCursoDiurno', [estudianteController::class, 'estudiantesCursoDiurno'])->name('estudiantesCursoDiurno');

Route::get('/estudiantesCursoEncuentro', [estudianteController::class, 'estudiantesCursoEncuentro'])->name('estudiantesCursoEncuentro');

Route::get('/estudiantes-facultad', [estudianteController::class, 'estudiantesFacultad'])->name('estudiantesFacultad');

Route::get('/estudiantes_sin_tutor', [estudianteController::class, 'estudiantes_sin_tutor'])->name('estudiantes_sin_tutor');

Route::get('/estudiantesAtrasadosFundamentación', [estudianteController::class, 'estudiantesAtrasadosFundamentación'])->name('estudiantesAtrasadosFundamentación');



//Profesores
Route::view('/profesores', 'consultas.profesores')->name('profesores');

Route::view('/buscarProfesor', 'consultas.profesores.buscarProfesor')->name('buscarProfesor');
Route::post('/mostrar_profesor', [profesorController::class, 'buscarProfesor'])->name('mostrar_profesor');

Route::get('/profesoresDepartamento', [profesorController::class, 'profesoresDepartamento'])->name('profesoresDepartamento');

Route::get('/profesoresNoTutores', [profesorController::class, 'profesoresNoTutores'])->name('profesoresNoTutores');



Route::get('/profesoresDoctores', [profesorController::class, 'profesoresDoctores'])->name('profesoresDoctores');

Route::get('/profesoresMáster', [profesorController::class, 'profesoresMáster'])->name('profesoresMáster');



//Ver estadísticas en página de inicio
Route::get('/estadisticas', [estadisticasController::class, 'obtenerEstadisticas'])->name('estadisticas');



//Roles estudiante y profesor


// Rutas para estudiantes

    // Fundamentaciones
    Route::get('/subirFundamentación', [App\Http\Controllers\Estudiante\SubirFundamentacionController::class, 'index'])->name('subirFundamentación');
    Route::post('/fundamentacion/subir-version', [App\Http\Controllers\Estudiante\SubirFundamentacionController::class, 'subirVersion'])->name('subirVersionFundamentación');
  
    
    // Cortes
    Route::get('/subirCorte', [App\Http\Controllers\Estudiante\SubirCorteController::class, 'index'])->name('subirCorte');
    Route::post('/cortes/subir-version/{numeroCorte}', [App\Http\Controllers\Estudiante\SubirCorteController::class, 'subirVersion'])->name('subirVersionCorte');
    


// Rutas para profesores

    // Revisar Fundamentación
Route::get('/revisarFundamentación', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'index'])
    ->name('revisarFundamentación');

Route::get('/profesor/revisar-fundamentacion/{id}', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'show'])
    ->name('revisarFundamentaciónEstudiante');

Route::post('/profesor/fundamentacion/aprobar', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'aprobar'])
    ->name('fundamentacion.aprobar');

Route::post('/profesor/fundamentacion/desaprobar', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'desaprobar'])
    ->name('fundamentacion.desaprobar');

Route::post('/profesor/fundamentacion/revertir', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'revertir'])
    ->name('fundamentacion.revertir');

Route::post('/profesor/fundamentacion/guardar-recomendacion', [App\Http\Controllers\Profesor\RevisarFundamentacionController::class, 'guardarRecomendacion'])
    ->name('fundamentacion.guardarRecomendacion');

    
    // Revisa Corte
Route::get('/revisarCorte', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'index'])
    ->name('revisarCorte');

Route::get('/profesor/revisar-corte/{id}', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'show'])
    ->name('revisarCorteEstudiante');

Route::post('/profesor/corte/aprobar', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'aprobar'])
    ->name('corte.aprobar');

Route::post('/profesor/corte/desaprobar', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'desaprobar'])
    ->name('corte.desaprobar');

Route::post('/profesor/corte/revertir', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'revertir'])
    ->name('corte.revertir');

Route::post('/profesor/corte/agregar-no-conformidad', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'agregarNoConformidad'])
    ->name('corte.agregarNoConformidad');

Route::post('/profesor/corte/crear-nueva-no-conformidad', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'crearNuevaNoConformidad'])
    ->name('corte.crearNuevaNoConformidad');

Route::delete('/profesor/corte/eliminar-no-conformidad', [App\Http\Controllers\Profesor\RevisarCorteController::class, 'eliminarNoConformidad'])
    ->name('corte.eliminarNoConformidad');



    
    // Estudiantes Tutorados
Route::get('estudiantesTutorados', [App\Http\Controllers\Profesor\EstudianteTutoradoController::class, 'index'])
    ->name('estudiantesTutorados');

Route::get('/profesor/estudiante-tutorado/{id}', [App\Http\Controllers\Profesor\EstudianteTutoradoController::class, 'show'])
    ->name('revisarEstudianteTutorado');

Route::post('/profesor/tutor/guardar-opinion-fundamentacion', [App\Http\Controllers\Profesor\EstudianteTutoradoController::class, 'guardarOpinionFundamentacion'])
    ->name('tutor.guardarOpinionFundamentacion');

Route::post('/profesor/tutor/guardar-opinion-corte', [App\Http\Controllers\Profesor\EstudianteTutoradoController::class, 'guardarOpinionCorte'])
    ->name('tutor.guardarOpinionCorte');




    // Rutas para administración de fechas

    // Gestión de fechas de entrega
    Route::get('/fechaEntrega', [App\Http\Controllers\fechaEntregaController::class, 'index'])
        ->name('fechaEntrega');
    
    Route::post('/fechas/fundamentacion', [App\Http\Controllers\fechaEntregaController::class, 'actualizarFundamentacion'])
        ->name('fechas.fundamentacion.actualizar');
    
    Route::post('/fechas/corte/{numeroCorte}', [App\Http\Controllers\fechaEntregaController::class, 'actualizarCorte'])
        ->name('fechas.corte.actualizar');
    
    
    
    
    Route::delete('/fechas/reiniciar', [App\Http\Controllers\fechaEntregaController::class, 'reiniciarFechas'])
        ->name('fechas.reiniciar');
