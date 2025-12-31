@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/consultas/estudiantesAtrasadosFundamentación.css">
<link rel="stylesheet" href="css/consultas/estudiantesFacultad.css">


<form action="{{ route('estudiantesFacultad') }}" id="formulario_estudiantes_facultad" method="GET">
    
    @csrf

    <div class="campo" id="campo_facultad">
        <label for="facultad">Facultad</label>
        <select id="facultad" name="facultad" class="atributo" required>
            <option value=""></option>
            @foreach($facultades as $facultad)
                <option value="{{ $facultad->idFacultad }}" 
                    {{ request('facultad') == $facultad->idFacultad ? 'selected' : '' }}>
                    {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="submit" value="Aceptar" id="aceptar">
</form>

@if(isset($estudiantes))
    @if (count($estudiantes) != 0)
        <div class="resultado-info">
            <h3>Estudiantes de la Facultad</h3>
            <p style="text-align: center;">Total de estudiantes encontrados: {{ count($estudiantes) }}</p>
        </div>
        
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
        @foreach ($estudiantes as $estudiante)
        <tr id="{{ $estudiante->id }}">
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
        @endforeach

            </tbody>
        </table>
    @else
        @if(request()->has('facultad'))
            <script>alert('No se encontraron estudiantes para la facultad seleccionada');</script>
        @endif
    @endif
@else
    @if(!request()->has('facultad'))
        <div class="instrucciones">
            <p>Seleccione una facultad para mostrar los estudiantes.</p>
        </div>
    @endif
@endif



@endsection