@extends('layouts.app')

@section('content')

@vite(['resources/css/consultas/ejecutar_consultas.css'])

<!-- =====================================================
     BOTÓN VOLVER AL MENÚ DE ESTUDIANTES
     ===================================================== -->
<div class="nav-volver">
    <a href="/estudiantes" class="btn-volver-consulta">
        ← Volver a Consultas de Estudiantes
    </a>
</div>

<form action="{{ route('mostrar_estudiante') }}" id="formulario_buscar_estudiante" method="post">
    @csrf

    <div class="campo" id="campo_ci">
        <label for="ci">Carnet de identidad</label>
        <input type="text" id="ci" name="ci" required>
    </div>

    <input type="submit" value="Aceptar" id="aceptar">
</form>

@if(isset($estudiante))
    <table>
        <thead>
            <tr>
                <th>Grupo</th>
                <th>Modalidad</th>
                <th>CI</th>
                <th>Nombre</th>
                <th>Apellido1</th>
                <th>Apellido2</th>
                <th>Sexo</th>
                <th>Año</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($estudiante->grupo)
                        {{ $estudiante->grupo->número }}
                    @else
                        <span class="no-data">Grupo no encontrado</span>
                    @endif
                </td>
                <td>
                    @if($estudiante->modalidad)
                        {{ $estudiante->modalidad->Nombre_modalidad }}
                    @else
                        <span class="no-data">Modalidad no encontrada</span>
                    @endif
                </td>
                <td>{{ $estudiante->CI_estudiante }}</td>
                <td>{{ $estudiante->Nombre_estudiante }}</td>
                <td>{{ $estudiante->Apellido1 }}</td>
                <td>{{ $estudiante->Apellido2 }}</td>
                <td>{{ $estudiante->sexo }}</td>
                <td>{{ $estudiante->year_academico }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Botón para nueva búsqueda -->
    <div class="nav-nueva-busqueda">
        <a href="{{ route('buscarEstudiante') }}" class="btn-nueva-busqueda">
            🔍 Nueva Búsqueda
        </a>
    </div>

    <script>
        document.getElementById('campo_ci').style.display = 'none';
        document.getElementById('aceptar').style.display = 'none';
    </script>
@endif

@endsection