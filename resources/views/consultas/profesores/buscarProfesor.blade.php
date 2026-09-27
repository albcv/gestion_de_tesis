@extends('layouts.app')

@section('content')

@vite(['resources/css/consultas/ejecutar_consultas.css'])

<!-- =====================================================
     BOTÓN VOLVER AL MENÚ DE PROFESORES
     ===================================================== -->
<div class="nav-volver">
    <a href="/profesores" class="btn-volver-consulta">
        ← Volver a Consultas de Profesores
    </a>
</div>

<form action="{{ route('mostrar_profesor') }}" id="formulario_buscar_profesor" method="post">
    @csrf

    <div class="campo" id="campo_ci">
        <label for="ci">Carnet de identidad</label>
        <input type="text" id="ci" name="ci" required>
    </div>

    <input type="submit" value="Aceptar" id="aceptar">
</form>

@if(isset($profesor))
    <table>
        <thead>
            <tr>
                <th>Departamento</th>
                <th>CI</th>
                <th>Nombre</th>
                <th>Apellido1</th>
                <th>Apellido2</th>
                <th>Categoría docente</th>
                <th>Categoría científica</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    @if($profesor->departamento)
                        {{ $profesor->departamento->Nombre_departamento }}
                    @else
                        <span class="no-data">Departamento no encontrado</span>
                    @endif
                </td>
                <td>{{ $profesor->CI_profesor }}</td>
                <td>{{ $profesor->Nombre_profesor }}</td>
                <td>{{ $profesor->Apellido1 }}</td>
                <td>{{ $profesor->Apellido2 }}</td>
                <td>{{ $profesor->Categoria_docente }}</td>
                <td>{{ $profesor->Categoria_cientifica }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Botón para nueva búsqueda -->
    <div class="nav-nueva-busqueda">
        <a href="{{ route('buscarProfesor') }}" class="btn-nueva-busqueda">
            🔍 Nueva Búsqueda
        </a>
    </div>

    <script>
        document.getElementById('campo_ci').style.display = 'none';
        document.getElementById('aceptar').style.display = 'none';
    </script>
@endif

@endsection