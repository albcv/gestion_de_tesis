@extends('layouts.app')

@section('content')

@vite(['resources/css/consultas/ejecutar_consultas.css'])

@php
    // Configuración de la vista según el tipo
    $config = [
        'departamento' => [
            'titulo'      => 'Profesores del Departamento',
            'descripcion' => 'Seleccione un departamento para ver todos sus profesores.',
            'url'         => route('profesoresDepartamento'),
            'msg_vacio'   => 'No hay profesores en ese departamento.',
        ],
        'no_tutores' => [
            'titulo'      => 'Profesores No Tutores',
            'descripcion' => 'Seleccione un departamento para ver los profesores que no tutoran tesis.',
            'url'         => route('profesoresNoTutores'),
            'msg_vacio'   => 'No se encontraron profesores no tutores en ese departamento.',
        ],
        'doctores' => [
            'titulo'      => 'Profesores Doctores en Ciencias',
            'descripcion' => 'Seleccione un departamento para ver los profesores con categoría científica "Doctor en Ciencias".',
            'url'         => route('profesoresDoctores'),
            'msg_vacio'   => 'No se encontraron profesores con categoría "Doctor en Ciencias" en ese departamento.',
        ],
        'master' => [
            'titulo'      => 'Profesores Máster en Ciencias',
            'descripcion' => 'Seleccione un departamento para ver los profesores con categoría científica "Máster en Ciencias".',
            'url'         => route('profesoresMáster'),
            'msg_vacio'   => 'No se encontraron profesores con categoría "Máster en Ciencias" en ese departamento.',
        ],
    ];

    $c = $config[$tipo] ?? $config['departamento'];
@endphp

<!-- =====================================================
     BOTÓN VOLVER AL MENÚ DE PROFESORES
     ===================================================== -->
<div class="nav-volver">
    <a href="/profesores" class="btn-volver-consulta">
        ← Volver a Consultas de Profesores
    </a>
</div>

<!-- =====================================================
     FORMULARIO DE FILTROS
     ===================================================== -->
<form action="{{ $c['url'] }}" method="GET" class="form-consultas">
    @csrf

    <div class="campo" id="campo_id_departamento">
        <label for="id_departamento">Departamento</label>
        <select id="id_departamento" name="id_departamento" class="atributo" required>
            <option value="">-- Seleccione un departamento --</option>
            @foreach($departamentos as $departamento)
                <option value="{{ $departamento->idDepartamento }}"
                    {{ request('id_departamento') == $departamento->idDepartamento ? 'selected' : '' }}>
                    {{ $departamento->Nombre_departamento }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="campo-boton">
        <input type="submit" value="Buscar" id="aceptar">
        @if($hasFilter)
            <a href="{{ $c['url'] }}" class="btn-limpiar">Limpiar Filtros</a>
        @endif
    </div>
</form>

<!-- =====================================================
     RESULTADOS
     ===================================================== -->
@if ($profesores->count() > 0)

    <div class="resultado-info">
        <h3>{{ $c['titulo'] }}</h3>

        <div class="estadisticas">
            <p><strong>Total encontrados:</strong> {{ $profesores->count() }}</p>

            @if($departamentoSeleccionado)
                <p><strong>Departamento:</strong> {{ $departamentoSeleccionado->Nombre_departamento }}</p>
            @endif
        </div>

        {{-- Botón Exportar CSV --}}
        <div class="acciones-exportar">
            <a href="{{ route('exportarConsultaCsvProfesores', array_merge(request()->query(), ['tipo' => $tipo])) }}"
               class="btn-exportar-consulta">
                📄 Exportar a CSV
            </a>
        </div>
    </div>

    <div class="table-container">
        <div class="total-registros">
            <span>Mostrando {{ $profesores->count() }} profesores</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>CI</th>
                    <th>Nombre</th>
                    <th>Apellido1</th>
                    <th>Apellido2</th>
                    <th>Categoría Docente</th>
                    <th>Categoría Científica</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($profesores as $profesor)
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
                @endforeach
            </tbody>
        </table>
    </div>

@elseif ($hasFilter)

    {{-- Sin resultados pero se aplicó algún filtro --}}
    <div class="no-resultados">
        <div class="no-resultados-icon">📋</div>
        <h3>No se encontraron profesores</h3>
        <p>{{ $c['msg_vacio'] }}</p>
        <p>Intente cambiar los criterios de búsqueda.</p>
    </div>

@else

    {{-- Estado inicial: sin filtros aplicados --}}
    <div class="instrucciones">
        <h3>📊 {{ $c['titulo'] }}</h3>
        <p>{{ $c['descripcion'] }}</p>
    </div>

@endif

@endsection