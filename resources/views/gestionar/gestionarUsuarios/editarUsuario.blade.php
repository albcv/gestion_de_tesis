@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarUsuarios/gestionarUsuarios.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarUsuarios/crearUsuario.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarUsuarios/editarUsuario.css') }}">
<script src="{{ asset('js/gestionarUsuarios/editarUsuario.js') }}"></script>
<script src="{{ asset('js/gestionarUsuarios/validarUsuario.js') }}"></script>

<h1 id="editar">Editar Usuario</h1>

<form action="/actualizarUsuario" method="post" id="formulario_usuario">
@csrf
<input type="hidden" name="id" value="{{ $usuario->id }}">

<!-- Campos básicos del usuario -->
<div class="campo" id="campo_nombre_usuario">
    <label for="name">Nombre de Usuario</label>
    <input type="text" id="name" name="name" class="atributo" required autocomplete="off" value="{{ old('name', $usuario->name) }}">
</div>

<div class="campo" id="campo_email">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required class="atributo" autocomplete="off" value="{{ old('email', $usuario->email) }}">
</div>

<div class="select" id="campo_rol">
    <label for="rol">Rol</label>
    <select name="rol" id="rol" class="atributo" required>
        <option value=""></option>
        @foreach ($roles as $rol)
            <option value="{{ $rol->id }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                {{ $rol->rol }}
            </option>
        @endforeach
    </select>
</div>

<div class="campo" id="campo_password">
    <label for="password">Contraseña</label>
    <input type="password" id="password" name="password" class="atributo" autocomplete="off" placeholder="Dejar en blanco para no cambiar">
</div>

<!-- DATOS DE ESTUDIANTE -->
<div id="datos_estudiante" style="display: none;" class="datos">
    <h3>Datos del Estudiante</h3>

    
    <div class="select" id="campo_id_modalidad">
        <label for="id_modalidad">Modalidad</label>
        <select id="id_modalidad" name="id_modalidad" class="atributo">
            <option value=""></option>
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad->idModalidad }}"
                    {{ ($usuario->estudiante && $usuario->estudiante->id_modalidad == $modalidad->idModalidad) ? 'selected' : '' }}>
                    {{ $modalidad->Nombre_modalidad }}
                </option>
            @endforeach
        </select>
    </div>
    
    <!-- NUEVO: Select para carrera -->
    <div class="select" id="campo_id_carrera">
        <label for="id_carrera">Carrera</label>
        <select id="id_carrera" name="id_carrera" class="atributo">
            <option value=""></option>
            @foreach($carreras as $carrera)
                <option value="{{ $carrera->id }}"
                    {{ ($usuario->estudiante && $usuario->estudiante->id_carrera == $carrera->id) ? 'selected' : '' }}>
                    {{ $carrera->Nombre_carrera }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="campo" id="campo_ci_estudiante">
        <label for="ci_estudiante">Carnet de Identidad</label>
        <input type="number" id="ci_estudiante" name="ci_estudiante" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->CI_estudiante : '' }}">
    </div>

    <div class="campo" id="campo_nombre_estudiante">
        <label for="nombre_estudiante">Nombre</label>
        <input type="text" id="nombre_estudiante" name="nombre_estudiante" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->Nombre_estudiante : '' }}">
    </div>

    <div class="campo" id="campo_apellido1_estudiante">
        <label for="apellido1_estudiante">Primer Apellido</label>
        <input type="text" id="apellido1_estudiante" name="apellido1_estudiante" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->Apellido1 : '' }}">
    </div>

    <div class="campo" id="campo_apellido2_estudiante">
        <label for="apellido2_estudiante">Segundo Apellido</label>
        <input type="text" id="apellido2_estudiante" name="apellido2_estudiante" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->Apellido2 : '' }}">
    </div>

    <div class="select" id="campo_id_grupo">
        <label for="id_grupo">Grupo</label>
        <select id="id_grupo" name="id_grupo" class="atributo">
            <option value=""></option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}" 
                    {{ ($usuario->estudiante && $usuario->estudiante->id_grupo == $grupo->id) ? 'selected' : '' }}>
                    {{ $grupo->número }}
                </option>
            @endforeach
        </select>
    </div>


    <div class="campo" id="campo_numero_estudiante">
        <label for="numero_estudiante">Número del Estudiante</label>
        <input type="number" id="numero_estudiante" name="numero_estudiante" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->número : '' }}">
    </div>

    <div class="select" id="campo_sexo_estudiante">
        <label for="sexo_estudiante">Sexo</label>
        <select name="sexo_estudiante" id="sexo_estudiante" class="atributo">
            <option value=""></option>
            <option value="Masculino" {{ ($usuario->estudiante && $usuario->estudiante->sexo == 'Masculino') ? 'selected' : '' }}>
                Masculino
            </option>
            <option value="Femenino" {{ ($usuario->estudiante && $usuario->estudiante->sexo == 'Femenino') ? 'selected' : '' }}>
                Femenino
            </option>
        </select>
    </div>

    <div class="campo" id="campo_fecha_ingreso">
        <label for="fecha_ingreso">Fecha de Ingreso</label>
        <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="atributo" autocomplete="off" 
            value="{{ $usuario->estudiante ? $usuario->estudiante->Fecha_ingreso : '' }}">
    </div>

    <div class="select" id="campo_año_académico">
        <label for="año_académico">Año Académico</label>
        <select name="año_académico" id="año_académico" class="atributo">
            <option value=""></option>
            @for ($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}" {{ ($usuario->estudiante && $usuario->estudiante->year_academico == $i) ? 'selected' : '' }}>
                    {{ $i }}
                </option>
            @endfor
        </select>
    </div>
</div>

<!-- DATOS DE PROFESOR -->
<div id="datos_profesor" style="display: none;" class="datos">
    <h3>Datos del Profesor</h3>

    <div class="select" id="campo_id_departamento">
        <label for="id_departamento">Departamento</label>
        <select id="id_departamento" name="id_departamento" class="atributo">
            <option value=""></option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento->idDepartamento }}"
                    {{ ($usuario->profesor && $usuario->profesor->id_departamento == $departamento->idDepartamento) ? 'selected' : '' }}>
                    {{ $departamento->Nombre_departamento }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="campo" id="campo_ci_profesor">
        <label for="ci_profesor">Carnet de Identidad</label>
        <input type="number" id="ci_profesor" name="ci_profesor" class="atributo" autocomplete="off" 
            value="{{ $usuario->profesor ? $usuario->profesor->CI_profesor : '' }}">
    </div>

    <div class="campo" id="campo_nombre_profesor">
        <label for="nombre_profesor">Nombre</label>
        <input type="text" id="nombre_profesor" name="nombre_profesor" class="atributo" autocomplete="off" 
            value="{{ $usuario->profesor ? $usuario->profesor->Nombre_profesor : '' }}">
    </div>

    <div class="campo" id="campo_apellido1_profesor">
        <label for="apellido1_profesor">Primer Apellido</label>
        <input type="text" id="apellido1_profesor" name="apellido1_profesor" class="atributo" autocomplete="off" 
            value="{{ $usuario->profesor ? $usuario->profesor->Apellido1 : '' }}">
    </div>

    <div class="campo" id="campo_apellido2_profesor">
        <label for="apellido2_profesor">Segundo Apellido</label>
        <input type="text" id="apellido2_profesor" name="apellido2_profesor" class="atributo" autocomplete="off" 
            value="{{ $usuario->profesor ? $usuario->profesor->Apellido2 : '' }}">
    </div>

    <div class="select" id="campo_categoría_docente">
        <label for="categoría_docente">Categoría Docente</label>
        <select id="categoría_docente" name="categoría_docente" class="atributo">
            <option value=""></option>
            <option value="Profesor Titular" {{ ($usuario->profesor && $usuario->profesor->Categoria_docente == 'Profesor Titular') ? 'selected' : '' }}>
                Profesor Titular
            </option>
            <option value="Profesor Instructor" {{ ($usuario->profesor && $usuario->profesor->Categoria_docente == 'Profesor Instructor') ? 'selected' : '' }}>
                Profesor Instructor
            </option>
            <option value="Profesor Auxiliar" {{ ($usuario->profesor && $usuario->profesor->Categoria_docente == 'Profesor Auxiliar') ? 'selected' : '' }}>
                Profesor Auxiliar
            </option>
        </select>
    </div>

    <div class="select" id="campo_categoría_científica">
        <label for="categoría_científica">Categoría Científica</label>
        <select id="categoría_científica" name="categoría_científica" class="atributo">
            <option value=""></option>
            <option value="Licenciado" {{ ($usuario->profesor && $usuario->profesor->Categoria_cientifica == 'Licenciado') ? 'selected' : '' }}>
                Licenciado
            </option>
            <option value="Ingeniero" {{ ($usuario->profesor && $usuario->profesor->Categoria_cientifica == 'Ingeniero') ? 'selected' : '' }}>
                Ingeniero
            </option>
            <option value="Máster en Ciencias" {{ ($usuario->profesor && $usuario->profesor->Categoria_cientifica == 'Máster en Ciencias') ? 'selected' : '' }}>
                Máster en Ciencias
            </option>
            <option value="Doctor en Ciencias" {{ ($usuario->profesor && $usuario->profesor->Categoria_cientifica == 'Doctor en Ciencias') ? 'selected' : '' }}>
                Doctor en Ciencias
            </option>
        </select>
    </div>
</div>

<!-- Botones de acción -->
<div class="botones-acción">
    <input type="submit" value="Actualizar" id="actualizar_usuario">
    <a href="/gestionarUsuarios" id="btn_cancelar">Cancelar</a>
</div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol');
    const datosEstudiante = document.getElementById('datos_estudiante');
    const datosProfesor = document.getElementById('datos_profesor');
    
    // Función para verificar el rol actual y mostrar la sección correspondiente
    function mostrarSeccionSegunRol() {
        const opcionSeleccionada = rolSelect.options[rolSelect.selectedIndex];
        const nombreRol = opcionSeleccionada.textContent.trim().toLowerCase();
        
        // Ocultar ambas secciones primero
        datosEstudiante.style.display = 'none';
        datosProfesor.style.display = 'none';
        
        // Deshabilitar campos de ambas secciones
        deshabilitarCampos(datosEstudiante);
        deshabilitarCampos(datosProfesor);
        
        // Mostrar la sección correspondiente según el rol
        if (nombreRol.includes('estudiante')) {
            datosEstudiante.style.display = 'block';
            habilitarCampos(datosEstudiante);
        } else if (nombreRol.includes('profesor')) {
            datosProfesor.style.display = 'block';
            habilitarCampos(datosProfesor);
        }
    }
    
    function habilitarCampos(seccion) {
        const campos = seccion.querySelectorAll('input, select');
        campos.forEach(campo => {
            campo.disabled = false;
            campo.required = true;
        });
    }
    
    function deshabilitarCampos(seccion) {
        const campos = seccion.querySelectorAll('input, select');
        campos.forEach(campo => {
            campo.disabled = true;
            campo.required = false;
        });
    }
    
    // Al cargar la página, mostrar la sección según el rol actual del usuario
    mostrarSeccionSegunRol();
    
    // Escuchar cambios en el select de rol
    rolSelect.addEventListener('change', mostrarSeccionSegunRol);
});
</script>

@endsection