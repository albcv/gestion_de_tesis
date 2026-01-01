@extends('layouts.app')

@section('content')

@vite(['resources/css/formulario.css'])
@vite(['resources/css/crud.css'])
@vite(['resources/css/gestionar/gestionarUsuarios/gestionarUsuarios.css'])
@vite(['resources/css/gestionar/gestionarUsuarios/crearUsuario.css'])
@vite(['resources/js/gestionarUsuarios/crearUsuario.js'])
@vite(['resources/js/gestionarUsuarios/validarUsuario.js'])



<h1>Crear Usuario</h1>

<form action="/agregarUsuario" method="post" id="formulario_usuario">

@csrf

<div class="campo" id="campo_nombre_usuario">
    <label for="name">Usuario</label>
    <input type="text" id="name" name="name" class="atributo" required autocomplete="off">
</div>

<div class="campo" id="campo_email">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" required class="atributo" autocomplete="off">
</div>

<div class="select" id="campo_rol">
    <label for="rol">Rol</label>
    <select name="rol" id="rol" class="atributo" required>
        <option value=""></option>
        @foreach ($roles as $rol)
            <option value="{{ $rol->id }}">{{$rol->rol}}</option>
        @endforeach
    </select>
</div>

<div class="campo" id="campo_password">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" class="atributo" required autocomplete="off">
</div>

<!-- DATOS DE ESTUDIANTE (oculta inicialmente) -->
<div  id="datos_estudiante" style="display: none;" class="datos">
    <h3>Datos del Estudiante</h3>

    <div class="select" id="campo_id_carrera">
        <label for="id_carrera">Carrera</label>
        <select id="id_carrera" name="id_carrera" class="atributo">
            <option value=""></option>
            @foreach($carreras as $carrera)
                <option value="{{ $carrera->id }}">{{ $carrera->Nombre_carrera }}</option>
            @endforeach
        </select>
    </div>
    

    <div class="select" id="campo_id_modalidad">
        <label for="id_modalidad">Modalidad</label>
        <select id="id_modalidad" name="id_modalidad" class="atributo">
            <option value=""></option>
            @foreach($modalidades as $modalidad)
                <option value="{{ $modalidad->idModalidad }}">{{ $modalidad->Nombre_modalidad }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="campo" id="campo_ci_estudiante" class="carnet">
        <label for="ci_estudiante">Carnet de Identidad</label>
        <input type="number" id="ci_estudiante" name="ci_estudiante" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_nombre_estudiante" class="nombre_persona">
        <label for="nombre_estudiante">Nombre</label>
        <input type="text" id="nombre_estudiante" name="nombre_estudiante" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_apellido1_estudiante" class="apellido1">
        <label for="apellido1_estudiante">Primer Apellido</label>
        <input type="text" id="apellido1_estudiante" name="apellido1_estudiante" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_apellido2_estudiante" class="apellido2">
        <label for="apellido2_estudiante">Segundo Apellido</label>
        <input type="text" id="apellido2_estudiante" name="apellido2_estudiante" class="atributo" autocomplete="off">
    </div>

    <div class="select" id="campo_id_grupo">
        <label for="id_grupo">Grupo</label>
        <select id="id_grupo" name="id_grupo" class="atributo">
            <option value=""></option>
            @foreach($grupos as $grupo)
                <option value="{{ $grupo->id }}">{{ $grupo->número }}</option>
            @endforeach
        </select>
    </div>

    <div class="campo" id="campo_numero_estudiante">
        <label for="numero_estudiante">Número del Estudiante</label>
        <input type="number" id="numero_estudiante" name="numero_estudiante" class="atributo" autocomplete="off">
    </div>

    <div class="select" id="campo_sexo_estudiante">
        <label for="sexo_estudiante">Sexo</label>
        <select name="sexo_estudiante" id="sexo_estudiante" class="atributo">
            <option value=""></option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select>
    </div>

    <div class="campo" id="campo_fecha_ingreso">
        <label for="fecha_ingreso">Fecha de Ingreso</label>
        <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="atributo" autocomplete="off">
    </div>

    <div class="select" id="campo_año_académico">
        <label for="año_académico">Año Académico</label>
        <select name="año_académico" id="año_académico" class="atributo">
            <option value=""></option>
            @for ($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}">{{ $i }}</option>
            @endfor
        </select>
    </div>
</div>

<!-- DATOS DE PROFESOR (oculta inicialmente) -->
<div id="datos_profesor" style="display: none;" class="datos">
    <h3>Datos del Profesor</h3>

    <div class="select" id="campo_id_departamento">
        <label for="id_departamento">Departamento</label>
        <select id="id_departamento" name="id_departamento" class="atributo">
            <option value=""></option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento->idDepartamento }}">{{ $departamento->Nombre_departamento }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="campo" id="campo_ci_profesor" class="carnet">
        <label for="ci_profesor">Carnet de Identidad</label>
        <input type="number" id="ci_profesor" name="ci_profesor" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_nombre_profesor" class="nombre_persona">
        <label for="nombre_profesor">Nombre</label>
        <input type="text" id="nombre_profesor" name="nombre_profesor" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_apellido1_profesor" class="apellido1">
        <label for="apellido1_profesor">Primer Apellido</label>
        <input type="text" id="apellido1_profesor" name="apellido1_profesor" class="atributo" autocomplete="off">
    </div>

    <div class="campo" id="campo_apellido2_profesor" class="apellido2">
        <label for="apellido2_profesor">Segundo Apellido</label>
        <input type="text" id="apellido2_profesor" name="apellido2_profesor" class="atributo" autocomplete="off">
    </div>

    <div class="select" id="campo_categoría_docente">
        <label for="categoría_docente">Categoría Docente</label>
        <select id="categoría_docente" name="categoría_docente" class="atributo">
            <option value=""></option>
            <option value="Profesor Titular">Profesor Titular</option>
            <option value="Profesor Instructor">Profesor Instructor</option>
            <option value="Profesor Auxiliar">Profesor Auxiliar</option>
        </select>
    </div>

    <div class="select" id="campo_categoría_científica">
        <label for="categoría_científica">Categoría Científica</label>
        <select id="categoría_científica" name="categoría_científica" class="atributo">
            <option value=""></option>
            <option value="Licenciado">Licenciado</option>
            <option value="Ingeniero">Ingeniero</option>
            <option value="Máster en Ciencias">Máster en Ciencias</option>
            <option value="Doctor en Ciencias">Doctor en Ciencias</option>
        </select>
    </div>
</div>

<input type="submit" value="Aceptar" id="crear_usuario">

</form>

@endsection