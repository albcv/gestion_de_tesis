@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/consultas/estudiantesAtrasadosFundamentación.css">
<link rel="stylesheet" href="css/consultas/buscarEstudiante.css">

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
                <th>Nro</th>
                <th>Nombre</th>
                <th>Apellido1</th>
                <th>Apellido2</th>
                <th>Sexo</th>
                <th>Fecha de ingreso</th>
                <th>Año</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                @if($estudiante->grupo)
                    {{ $estudiante->grupo->número }}
                @else
                    <span style="color: #000;">Grupo no encontrado</span>
                @endif
            </td>
            <td>
                @if($estudiante->modalidad)
                    {{ $estudiante->modalidad->Nombre_modalidad }}
                @else
                    <span style="color: #000;">Modalidad no encontrada</span>
                @endif
            </td>
                <td>{{ $estudiante->CI_estudiante }}</td>
                <td>{{ $estudiante->número }}</td>
                <td>{{ $estudiante->Nombre_estudiante }}</td>
                <td>{{ $estudiante->Apellido1 }}</td>
                <td>{{ $estudiante->Apellido2 }}</td>
                <td>{{ $estudiante->sexo }}</td>
                <td>{{ $estudiante->Fecha_ingreso }}</td>
                <td>{{ $estudiante->year_academico }}</td>
            </tr>
        </tbody>
    </table>

   <!-- Botón para nueva búsqueda -->
    <div style="margin-top: 40px; position: absolute;top: 140px;left:600px;">
        <a id="nueva_búsqueda" href="{{ route('buscarEstudiante') }}" style="color: #000; background-color: #0f0; font-size: 23px;padding: 20px">
            Nueva Búsqueda
        </a>

    </div>

    <script>

    document.getElementById('campo_ci').style.display='none'
    document.getElementById('aceptar').style.display='none'

    </script>


@endif





@endsection
