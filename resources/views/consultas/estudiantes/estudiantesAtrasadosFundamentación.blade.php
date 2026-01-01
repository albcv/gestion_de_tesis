@extends('layouts.app')

@section('content')

@vite(['resources/css/formulario.css'])
@vite(['resources/css/consultas/estudiantesAtrasadosFundamentación.css'])
@vite(['resources/css/consultas/estudiantes.css'])

<form action="/estudiantesAtrasadosFundamentación" id="formulario_estudiantes_atrasados" method="get">
    @csrf

    <div class="campo" id="campo_carrera">
        <label for="carrera">Carrera</label>
        <select id="carrera" name="carrera" class="atributo" required>
            <option value=""></option>
            @foreach($carreras as $carrera)
                <option value="{{ $carrera->id }}" 
                    {{ (old('carrera') == $carrera->id || (isset($carreraParam) && $carreraParam == $carrera->id)) ? 'selected' : '' }}>
                    {{ $carrera->Nombre_carrera }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="submit" value="Aceptar" id="aceptar">
</form>

@if(isset($estudiantes))
    @if (count($estudiantes) != 0)
        <div class="resultado-info">
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
        <script>
        
            @if(isset($carreraParam) && $carreraParam)
                alert('No hay estudiantes atrasados en la fundamentación de la tesis en esa carrera');
            @endif
        </script>

    @endif

@endif


@endsection