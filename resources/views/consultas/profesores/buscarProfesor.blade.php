@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/consultas/estudiantesAtrasadosFundamentación.css">
<link rel="stylesheet" href="css/consultas/buscarEstudiante.css">

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
                    <span style="color: #999;">Departamento no encontrado</span>
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
    <div style="margin-top: 40px; position: absolute;top: 140px;left:600px;">
        <a id="nueva_búsqueda" href="{{ route('buscarProfesor') }}" style="color: #000; background-color: #0f0; font-size: 23px;padding: 20px">
            Nueva Búsqueda
        </a>

    </div>

    <script>

    document.getElementById('campo_ci').style.display='none'
    document.getElementById('aceptar').style.display='none'

    </script>


@endif





@endsection
