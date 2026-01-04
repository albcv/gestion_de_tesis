import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [

                //css generales
                'resources/css/app.css',
                'resources/css/crud.css',
                'resources/css/formulario.css',
                'resources/css/inicio.css',
                'resources/css/login.css',
                'resources/css/perfil.css',
                'resources/css/sidebar.css',
                'resources/css/stats.css',

                //css profesor
                'resources/css/profesor/listaCortes.css',
                'resources/css/profesor/listaEstudiantesTutorados.css',
                'resources/css/profesor/listaFundamentaciones.css',
                'resources/css/profesor/revisarCorte.css',
                'resources/css/profesor/revisarEstudianteTutorado.css',
                'resources/css/profesor/revisarFundamentación.css',

                //css estudiante
                'resources/css/estudiante/subirCorte.css',
                'resources/css/estudiante/subirFundamentación.css',

                //css consultas
                'resources/css/consultas/buscarEstudiante.css',
                'resources/css/consultas/consultas.css',
                'resources/css/consultas/consultas2.css',
                'resources/css/consultas/estudiantes.css',
                'resources/css/consultas/estudiantesAtrasadosFundamentación.css',
                'resources/css/consultas/estudiantesFacultad.css',
                'resources/css/consultas/estudiantesSinTutor.css',
                'resources/css/consultas/fundamentacionesAprobadas.css',
                'resources/css/consultas/profesoresDepartamento.css',

                //css gestionar
                'resources/css/gestionar/fechaEntrega.css',
                'resources/css/gestionar/gestionar.css',
                'resources/css/gestionar/gestionarDepartamento.css',
                'resources/css/gestionar/gestionarFacultad.css',
                'resources/css/gestionar/gestionarGrupo.css',
                'resources/css/gestionar/gestionarModalidad.css',
                'resources/css/gestionar/gestionarNoConformidades.css',
                'resources/css/gestionar/gestionarPermisos.css',
                'resources/css/gestionar/gestionarProfesor.css',
                'resources/css/gestionar/gestionarRoles.css',

                //css gestionar carrera
                'resources/css/gestionar/gestionarCarrera/formularioCarrera.css',
                'resources/css/gestionar/gestionarCarrera/gestionarCarrera.css',
                'resources/css/gestionar/gestionarCarrera/verCarrera.css',

                 //css gestionar cortes
                'resources/css/gestionar/gestionarCortes/crearEditarCorte.css',
                'resources/css/gestionar/gestionarCortes/editarCorte.css',
                'resources/css/gestionar/gestionarCortes/gestionarCortes.css',
                'resources/css/gestionar/gestionarCortes/verCorte.css',

                  //css gestionar cortes no conformidades
                'resources/css/gestionar/gestionarCortesNoConformidades/crear.css',
                'resources/css/gestionar/gestionarCortesNoConformidades/editar.css',

                  //css gestionar cortes profesor
                'resources/css/gestionar/gestionarCortesProfesor/vincular.css',

                 //css gestionar fundamentación
                'resources/css/gestionar/gestionarFundamentación/crearFundamentación.css',
                'resources/css/gestionar/gestionarFundamentación/editarFundamentación.css',
                'resources/css/gestionar/gestionarFundamentación/gestionarFundamentaciones.css',
                'resources/css/gestionar/gestionarFundamentación/verFundamentación.css',
                
                 //css gestionar profesor-fundamentación
                'resources/css/gestionar/gestionarProfesorFundamentación/vincularProfesorFundamentación.css',

                 //css gestionar recomendaciones de fundamentación
                'resources/css/gestionar/gestionarRecomendacionesFundamentación/crear.css',
                'resources/css/gestionar/gestionarRecomendacionesFundamentación/editar.css',

                //css gestionar tesis
                'resources/css/gestionar/gestionarTesis/crearTesis.css',
                'resources/css/gestionar/gestionarTesis/editarTesis.css',
                'resources/css/gestionar/gestionarTesis/gestionarTesis.css',
                'resources/css/gestionar/gestionarTesis/verTesis.css',

                //css gestionar tutor-estudiante
                'resources/css/gestionar/gestionarTutorEstudiante/asignarTutor.css',

                //css gestionar usuarios
                'resources/css/gestionar/gestionarUsuarios/crearUsuario.css',
                'resources/css/gestionar/gestionarUsuarios/editarUsuario.css',
                'resources/css/gestionar/gestionarUsuarios/gestionarUsuarios.css',
                'resources/css/gestionar/gestionarUsuarios/verUsuario.css',
                
                
                // === ARCHIVOS JAVASCRIPT  ===
                'resources/js/cancelar.js',
                'resources/js/eliminar.js',
                'resources/js/fila_seleccionada.js',
                'resources/js/modificar.js',
                'resources/js/modificarRol.js',
                'resources/js/vaciar.js',

                 // === Actualizar  ===
                'resources/js/actualizar/actualizarDepartamento.js',
                'resources/js/actualizar/actualizarFacultad.js',
                'resources/js/actualizar/actualizarGrupo.js',
                'resources/js/actualizar/actualizarModalidad.js',
                'resources/js/actualizar/actualizarNoConformidad.js',
                'resources/js/actualizar/actualizarPermiso.js',
                'resources/js/actualizar/actualizarRol.js',

                 // === Agregar  ===
                'resources/js/agregar/agregarDepartamento.js',
                'resources/js/agregar/agregarFacultad.js',
                'resources/js/agregar/agregarGrupo.js',
                'resources/js/agregar/agregarModalidad.js',
                'resources/js/agregar/agregarNoConformidades.js',
                'resources/js/agregar/agregarPermiso.js',
                'resources/js/agregar/agregarRol.js',

                  // === gestionar cortes  ===
                'resources/js/gestionarCortes/validarCorte.js',

                 // === gestionar fundamentaciones  ===
                'resources/js/gestionarFundamentaciones/validarFundamentación.js',

                // === gestionar usuarios  ===
                'resources/js/gestionarUsuarios/crearUsuario.js',
                'resources/js/gestionarUsuarios/editarUsuario.js',
                'resources/js/gestionarUsuarios/eliminarUsuario.js',
                'resources/js/gestionarUsuarios/validarUsuario.js',

                // === validaciones  ===
                'resources/js/validaciones/validarLogin.js',
        
                // Registrar admin
                'resources/css/registrar-admin.css',

                'resources/css/cambiarContraseña.css'
            
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});