@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/consultas/estudiantesAtrasadosFundamentación.css">
<link rel="stylesheet" href="css/consultas/estudiantesFacultad.css">
<link rel="stylesheet" href="css/consultas/profesoresDepartamento.css">

<form action="{{ route('profesoresNoTutores') }}" method="GET">
    @csrf

    <div class="campo" id="campo_id_departamento">
        <label for="id_departamento">Departamento</label>
        <select id="id_departamento" name="id_departamento" class="atributo" required>
            <option value=""></option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento->idDepartamento }}" 
                    {{ (isset($departamentoParam) && $departamentoParam == $departamento->idDepartamento) ? 'selected' : '' }}>
                    {{ $departamento->Nombre_departamento }}
                </option>
            @endforeach
        </select>
    </div>

    <input type="submit" value="Aceptar" id="aceptar">
</form>

@if(isset($profesores))
    @if (count($profesores) != 0)
        <div class="resultado-info">
            <p style="text-align: center;">
                @if(isset($departamentoParam) && $departamentoParam)
                    @php
                        $departamentoNombre = $departamentos->where('idDepartamento', $departamentoParam)->first()->Nombre_departamento ?? 'Departamento no encontrado';
                    @endphp
                    Profesores no tutores del <strong>{{ $departamentoNombre }}</strong>: {{ count($profesores) }}
                @else
                    Total de profesores no tutores encontrados: {{ count($profesores) }}
                @endif
            </p>
        </div>
        
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
                @foreach ($profesores as $profesor)
                <tr id="{{ $profesor->id }}">
                    <td>
                        @if($profesor->departamento)
                            {{ $profesor->departamento->Nombre_departamento }}
                        @else
                            <span style="color: #000;">Departamento no encontrado</span>
                        @endif
                    </td>
                    <td>{{ $profesor->CI_profesor }}</td>
                    <td>{{ $profesor->Nombre_profesor }}</td>
                    <td>{{ $profesor->Apellido1 }}</td>
                    <td>{{ $profesor->Apellido2 }}</td>
                    <td>{{ $profesor->Categoria_docente }}</td>
                    <td>{{ $profesor->Categoria_cientifica }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <script>
            @if(isset($departamentoParam) && $departamentoParam)
                alert('No se encontraron resultados');
            @endif
        </script>
    @endif
@endif

@endsection