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
use App\Http\Controllers\FundamentaciónController;
use App\Http\Controllers\fundamentacionesAprobadasController;
use App\Http\Controllers\recomendacionesFundamentacionController;
use App\Http\Controllers\cortesAprobadosController;
use App\Http\Controllers\tutorEstudianteController;
use App\Http\Controllers\gruposController;
use App\Http\Controllers\rolesController;
use App\Http\Controllers\permisosController;
use App\Http\Controllers\ProfesorFundamentaciónController;
use App\Http\Controllers\estadisticasController;
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

// Rutas de autenticación de usuario
Route::view('/login', 'login')->name('login');
Route::view('/inicio', 'inicio')->name('inicio');
Route::post('/inicioSesion', [loginController::class, 'login'])->name('inicioSesion');
Route::get('/logout', [loginController::class, 'logout'])->name('logout');


//Gestionar usuarios
Route::get('/gestionarUsuarios', [UserController::class, 'mostrar'])->name('gestionarUsuarios');
Route::get('/crearUsuario', [UserController::class, 'crearUsuario'])->name('crearUsuario');
Route::get('/verUsuario/{id}', [UserController::class, 'ver'])->name('verUsuario');
Route::post('/agregarUsuario', [UserController::class, 'agregar'])->name('agregarUsuario');
Route::get('/editarUsuario/{id}', [UserController::class, 'editar'])->name('editarUsuario');
Route::post('/actualizarUsuario', [UserController::class, 'actualizar'])->name('actualizarUsuario');
Route::post('/eliminarUsuario', [UserController::class, 'eliminar']);
Route::post('/vaciarUsuarios', [UserController::class, 'vaciar']);


Route::view('/gestionar', 'gestionar.gestionar')->name('gestionar');


Route::get('/gestionarFacultad', [facultadController::class, 'mostrar'])->name('gestionarFacultad');
Route::post('/agregarFacultad', [facultadController::class, 'agregar'])->name('agregarFacultad');
Route::post('/eliminarFacultad', [facultadController::class, 'eliminar'])->name('eliminarFacultad');
Route::post('/modificarFacultad', [facultadController::class, 'modificar'])->name('modificarFacultad');
Route::post('/vaciarFacultad', [facultadController::class, 'vaciar'])->name('vaciarFacultad');


// Rutas para gestión de carreras
Route::get('/gestionarCarrera', [carreraController::class, 'mostrar'])->name('gestionarCarrera');
Route::get('/carreras/agregar', [carreraController::class, 'mostrarAgregar'])->name('agregarCarrera');
Route::get('/editarCarrera/{id}', [carreraController::class, 'mostrarEditar'])->name('editarCarrera');
Route::get('/verCarrera/{id}', [carreraController::class, 'mostrarDetalles'])->name('verCarrera');
Route::post('/agregarCarrera', [carreraController::class, 'agregar'])->name('agregarCarrera_post');
Route::post('/eliminarCarrera', [carreraController::class, 'eliminar'])->name('eliminarCarrera');
Route::post('/modificarCarrera', [carreraController::class, 'modificar'])->name('modificarCarrera');



Route::get('/gestionarModalidad', [modalidadController::class, 'mostrar'])->name('gestionarModalidad');
Route::post('/agregarModalidad', [modalidadController::class, 'agregar']);
Route::post('/eliminarModalidad', [modalidadController::class, 'eliminar']);
Route::post('/modificarModalidad', [modalidadController::class, 'modificar']);
Route::post('/vaciarModalidad', [modalidadController::class, 'vaciar']);


Route::get('/gestionarGrupos', [gruposController::class, 'mostrar'])->name('gestionarGrupos');
Route::post('/agregarGrupo', [gruposController::class, 'agregar']);
Route::post('/eliminarGrupo', [gruposController::class, 'eliminar']);
Route::post('/modificarGrupo', [gruposController::class, 'modificar']);
Route::post('/vaciarGrupos', [gruposController::class, 'vaciar']);


Route::get('/gestionarEstudiante', [estudianteController::class, 'mostrar'])->name('gestionarEstudiante');
Route::post('/agregarEstudiante', [estudianteController::class, 'agregar']);
Route::post('/eliminarEstudiante', [estudianteController::class, 'eliminar']);
Route::post('/modificarEstudiante', [estudianteController::class, 'modificar']);
Route::post('/vaciarEstudiante', [estudianteController::class, 'vaciar']);


// Rutas para gestionar tesis
Route::get('/gestionarTesis', [TesisController::class, 'mostrar'])->name('gestionarTesis');
Route::get('/crearTesis', [TesisController::class, 'crearTesis'])->name('crearTesis');
Route::post('/agregarTesis', [TesisController::class, 'agregar'])->name('agregarTesis');
Route::get('/verTesis/{id}', [TesisController::class, 'ver'])->name('verTesis');
Route::get('/editarTesis/{id}', [TesisController::class, 'editar'])->name('editarTesis');
Route::post('/modificarTesis', [TesisController::class, 'modificar'])->name('modificarTesis');
Route::post('/eliminarTesis', [TesisController::class, 'eliminar'])->name('eliminarTesis');
Route::post('/vaciarTesis', [TesisController::class, 'vaciar'])->name('vaciarTesis');

// Rutas para gestionar cortes
Route::get('/gestionarCortes', [cortesController::class, 'mostrar'])->name('gestionarCortes');
Route::get('/crearCorte', [cortesController::class, 'crear'])->name('crearCorte');
Route::post('/agregarCorte', [cortesController::class, 'agregar'])->name('agregarCorte');
Route::get('/cortes/ver/{id}', [cortesController::class, 'ver'])->name('verCorte');
Route::get('/editarCorte/{id}', [cortesController::class, 'editar'])->name('editarCorte');
Route::post('/eliminarCorte', [cortesController::class, 'eliminar'])->name('eliminarCorte');
Route::post('/modificarCorte', [cortesController::class, 'modificar'])->name('modificarCorte');
Route::post('/vaciarCortes', [cortesController::class, 'vaciar'])->name('vaciarCortes');
Route::post('/aprobarCorte', [cortesController::class, 'aprobarCorte'])->name('aprobarCorte');
Route::post('/desaprobarCorte', [cortesController::class, 'desaprobarCorte'])->name('desaprobarCorte');
Route::post('/revertirCorte', [cortesController::class, 'revertirCorte'])->name('revertirCorte');

// Rutas para versiones de corte
Route::delete('/version-corte/{id}', [cortesController::class, 'eliminarVersion'])->name('eliminar-version-corte');
Route::get('/version-corte/{id}/descargar', [cortesController::class, 'verDocumentoVersion'])->name('ver-documento-version-corte');


Route::get('/gestionarCortesAprobados', [cortesAprobadosController::class, 'mostrar'])->name('gestionarCortesAprobados');
Route::post('/agregarCorteAprobado', [cortesAprobadosController::class, 'agregar']);
Route::post('/eliminarCorteAprobado', [cortesAprobadosController::class, 'eliminar']);
Route::post('/modificarCorteAprobado', [cortesAprobadosController::class, 'modificar']);
Route::post('/vaciarCortesAprobados', [cortesAprobadosController::class, 'vaciar']);



Route::get('/gestionarNoConformidades', [NoConformidadesController::class, 'mostrar'])->name('gestionarNoConformidades');
Route::post('/agregarNoConformidades', [NoConformidadesController::class, 'agregar']);
Route::post('/eliminarNoConformidades', [NoConformidadesController::class, 'eliminar']);
Route::post('/modificarNoConformidades', [NoConformidadesController::class, 'modificar']);
Route::post('/vaciarNoConformidades', [NoConformidadesController::class, 'vaciar']);



Route::get('/agregarNoConformidadCorte/{id_corte}', [cortesNoConformidadesController::class, 'crear'])->name('agregarNoConformidadCorte');
Route::post('/agregarNoConformidadCorteExistente', [cortesNoConformidadesController::class, 'agregarExistente'])->name('agregarNoConformidadCorteExistente');
Route::post('/crearYVincularNoConformidadCorte', [cortesNoConformidadesController::class, 'crearYVincular'])->name('crearYVincularNoConformidadCorte');
Route::get('/editarNoConformidadCorte/{id_corte}/{id_nc}', [cortesNoConformidadesController::class, 'editar'])->name('editarNoConformidadCorte');
Route::post('/actualizarNoConformidadCorte', [cortesNoConformidadesController::class, 'actualizarRelacion'])->name('actualizarNoConformidadCorte');
Route::post('/desvincularNoConformidadCorte', [cortesNoConformidadesController::class, 'desvincular'])->name('desvincularNoConformidadCorte');



Route::get('/gestionarProfesor', [profesorController::class, 'mostrar'])->name('gestionarProfesor');
Route::post('/agregarProfesor', [profesorController::class, 'agregar']);
Route::post('/eliminarProfesor', [profesorController::class, 'eliminar']);
Route::post('/modificarProfesor', [profesorController::class, 'modificar']);
Route::post('/vaciarProfesor', [profesorController::class, 'vaciar']);



// Rutas para gestión de tutores-estudiantes
Route::get('/asignarTutor/{id_estudiante}', [tutorEstudianteController::class, 'mostrarAsignarTutor'])->name('asignarTutor');
Route::post('/agregarTutorEstudiante', [tutorEstudianteController::class, 'agregar'])->name('agregarTutorEstudiante');
Route::post('/eliminarTutorEstudiante', [tutorEstudianteController::class, 'eliminar'])->name('eliminarTutorEstudiante');




Route::get('/gestionarDepartamento', [departamentoController::class, 'mostrar'])->name('gestionarDepartamento');
Route::post('/agregarDepartamento', [departamentoController::class, 'agregar']);
Route::post('/eliminarDepartamento', [departamentoController::class, 'eliminar']);
Route::post('/modificarDepartamento', [departamentoController::class, 'modificar']);
Route::post('/vaciarDepartamento', [departamentoController::class, 'vaciar']);



// Vincular profesor a un corte
Route::get('/vincularProfesorCorte/{id}', [cortesProfesorController::class, 'mostrarVincular'])->name('vincularProfesorCorte');
Route::post('/vincularProfesorCorte', [cortesProfesorController::class, 'vincular'])
->name('vincularProfesorCorte.post');
Route::post('/desvincularProfesorCorte', [cortesProfesorController::class, 'desvincular']) ->name('desvincularProfesorCorte');



// Rutas para Fundamentación
Route::get('/gestionarFundamentaciones', [FundamentaciónController::class, 'mostrar'])->name('gestionarFundamentaciones');
Route::get('/crearFundamentación', [FundamentaciónController::class, 'crear'])->name('crearFundamentación');
Route::get('/verFundamentación/{id}', [FundamentaciónController::class, 'ver'])->name('verFundamentación');
Route::get('/editarFundamentación/{id}', [FundamentaciónController::class, 'editar'])->name('editarFundamentación');
Route::post('/agregarFundamentación', [FundamentaciónController::class, 'agregar'])->name('agregarFundamentación');
Route::post('/eliminarFundamentación', [FundamentaciónController::class, 'eliminar'])->name('eliminarFundamentación');
Route::post('/modificarFundamentación', [FundamentaciónController::class, 'modificar'])->name('modificarFundamentación');
Route::post('/vaciarFundamentaciones', [FundamentaciónController::class, 'vaciar']);
Route::post('/aprobarFundamentación', [FundamentaciónController::class, 'aprobar'])->name('aprobarFundamentación');
Route::post('/desaprobarFundamentación', [FundamentaciónController::class, 'desaprobar'])->name('desaprobarFundamentación');
Route::post('/revertirFundamentación', [FundamentaciónController::class, 'revertir'])->name('revertirFundamentación');

// Rutas para versiones de fundamentación
Route::delete('/version-fundamentacion/{id}', [FundamentaciónController::class, 'eliminarVersion'])->name('eliminar-version-fundamentacion');
Route::get('/version-fundamentacion/{id}/descargar', [FundamentaciónController::class, 'verDocumentoVersion'])->name('ver-documento-version');


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
    



Route::get('/gestionarRoles', [rolesController::class, 'mostrar'])->name('gestionarRoles');
Route::post('/agregarRol', [rolesController::class, 'agregar']);
Route::post('/eliminarRol', [rolesController::class, 'eliminar']);
Route::post('/modificarRol', [rolesController::class, 'modificar']);
Route::post('/vaciarRoles', [rolesController::class, 'vaciar']);
Route::get('/obtenerPermisosRol/{id}', [rolesController::class, 'obtenerPermisosRol'])->name('obtenerPermisosRol');


Route::get('/gestionarPermisos', [permisosController::class, 'mostrar'])->name('gestionarPermisos');
Route::post('/agregarPermiso', [permisosController::class, 'agregar']);
Route::post('/eliminarPermiso', [permisosController::class, 'eliminar']);
Route::post('/modificarPermiso', [permisosController::class, 'modificar']);
Route::post('/vaciarPermisos', [permisosController::class, 'vaciar']);



// Para descargar documento
Route::get('/ver-documento/{id}', [cortesController::class, 'verDocumento'])->name('ver-documento');
Route::get('/ver-fundamentacion-documento/{id}', [FundamentaciónController::class, 'verDocumento'])->name('ver-fundamentacion-documento');




// Perfil de usuario
Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');


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
