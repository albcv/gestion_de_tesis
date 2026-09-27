@extends('layouts.app')

@section('content')

@vite(['resources/css/consultas/ejecutar_consultas.css'])

@php
    // Configuración de la vista según el tipo
    $config = [
        'sin_tutor' => [
            'titulo'      => 'Estudiantes Sin Tutor',
            'descripcion' => 'Seleccione una carrera para ver los estudiantes que no tienen tutor asignado.',
            'filtro'      => 'carrera+year',
            'url'         => '/estudiantes_sin_tutor',
            'msg_vacio'   => 'No hay estudiantes sin tutor con los filtros aplicados.',
        ],
        'atrasados_fundamentacion' => [
            'titulo'      => 'Estudiantes Atrasados en Fundamentación',
            'descripcion' => 'Seleccione una carrera para ver los estudiantes que no han subido su fundamentación.',
            'filtro'      => 'carrera',
            'url'         => '/estudiantesAtrasadosFundamentación',
            'msg_vacio'   => 'No hay estudiantes atrasados en la fundamentación con los filtros aplicados.',
        ],
        'curso_diurno' => [
            'titulo'      => 'Estudiantes del Curso Diurno',
            'descripcion' => 'Seleccione una carrera para ver los estudiantes del curso regular diurno.',
            'filtro'      => 'carrera',
            'url'         => '/estudiantesCursoDiurno',
            'msg_vacio'   => 'No hay estudiantes del curso diurno con los filtros aplicados.',
        ],
        'curso_encuentro' => [
            'titulo'      => 'Estudiantes del Curso por Encuentro',
            'descripcion' => 'Seleccione una carrera para ver los estudiantes del curso por encuentro.',
            'filtro'      => 'carrera',
            'url'         => '/estudiantesCursoEncuentro',
            'msg_vacio'   => 'No hay estudiantes del curso por encuentro con los filtros aplicados.',
        ],
        'facultad' => [
            'titulo'      => 'Estudiantes por Facultad',
            'descripcion' => 'Seleccione una facultad para ver los estudiantes.',
            'filtro'      => 'facultad',
            'url'         => '/estudiantes-facultad',
            'msg_vacio'   => 'No se encontraron estudiantes para la facultad seleccionada.',
        ],
    ];

    $c = $config[$tipo] ?? $config['sin_tutor'];
@endphp

<!-- =====================================================
     BOTÓN VOLVER AL MENÚ DE ESTUDIANTES
     ===================================================== -->
<div class="nav-volver">
    <a href="/estudiantes" class="btn-volver-consulta">
        ← Volver a Consultas de Estudiantes
    </a>
</div>

<!-- =====================================================
     FORMULARIO DE FILTROS
     ===================================================== -->
<form action="{{ $c['url'] }}" method="get" class="form-consultas">
    @csrf

    @if ($c['filtro'] === 'facultad')

        {{-- Filtro por facultad --}}
        <div class="campo" id="campo_facultad">
            <label for="facultad">Facultad</label>
            <select id="facultad" name="facultad" class="atributo" required>
                <option value="">-- Seleccione una facultad --</option>
                @foreach($facultades as $facultad)
                    <option value="{{ $facultad->idFacultad }}"
                        {{ request('facultad') == $facultad->idFacultad ? 'selected' : '' }}>
                        {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                    </option>
                @endforeach
            </select>
        </div>

        <input type="submit" value="Aceptar" id="aceptar">

    @else

        {{-- Filtro por carrera (+ año opcional) --}}
        <div class="form-filtros">
            <div class="campo" id="campo_carrera">
                <label for="carrera">Carrera</label>
                <select id="carrera" name="carrera" class="atributo" required>
                    <option value="">-- Seleccione una carrera --</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->id }}"
                            {{ request('carrera') == $carrera->id ? 'selected' : '' }}>
                            {{ $carrera->Nombre_carrera }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($c['filtro'] === 'carrera+year')
                <div class="campo" id="campo_ano">
                    <label for="year_academico">Año Académico</label>
                    <select id="year_academico" name="year_academico" class="atributo">
                        <option value="">-- Todos los años --</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}"
                                {{ request('year_academico') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="campo-boton">
                <input type="submit" value="Buscar" id="aceptar">
                @if($hasFilter)
                    <a href="{{ $c['url'] }}" class="btn-limpiar">Limpiar Filtros</a>
                @endif
            </div>
        </div>

    @endif
</form>

<!-- =====================================================
     RESULTADOS
     ===================================================== -->
@if ($estudiantes->count() > 0)

    <div class="resultado-info">
        <h3>{{ $c['titulo'] }}</h3>

        <div class="estadisticas">
            <p><strong>Total encontrados:</strong> {{ $estudiantes->count() }}</p>

            @if($tipo === 'sin_tutor' && $carreraSeleccionada)
                <p><strong>Carrera:</strong> {{ $carreraSeleccionada->Nombre_carrera }}</p>
                @if(request('year_academico'))
                    <p><strong>Año académico:</strong> {{ request('year_academico') }}</p>
                @endif
            @elseif($tipo === 'facultad' && $facultadSeleccionada)
                <p><strong>Facultad:</strong> {{ $facultadSeleccionada->Nombre_facultad }}</p>
            @elseif($carreraSeleccionada)
                <p><strong>Carrera:</strong> {{ $carreraSeleccionada->Nombre_carrera }}</p>
            @endif
        </div>

        {{-- Botón Exportar CSV --}}
        <div class="acciones-exportar">
            <a href="{{ route('exportarConsultaCsv', array_merge(request()->query(), ['tipo' => $tipo])) }}"
               class="btn-exportar-consulta">
                📄 Exportar a CSV
            </a>
        </div>
    </div>

    <div class="table-container">
        <div class="total-registros">
            <span>Mostrando {{ $estudiantes->count() }} estudiantes</span>
        </div>

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
                    @if($tipo === 'sin_tutor')
                        <th class="acciones-col">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($estudiantes as $estudiante)
                <tr>
                    <td>
                        @if($estudiante->grupo)
                            {{ $estudiante->grupo->número }}
                        @else
                            <span class="no-data">No asignado</span>
                        @endif
                    </td>
                    <td>
                        @if($estudiante->modalidad)
                            {{ $estudiante->modalidad->Nombre_modalidad }}
                        @else
                            <span class="no-data">No asignada</span>
                        @endif
                    </td>
                    <td>{{ $estudiante->CI_estudiante }}</td>
                    <td>{{ $estudiante->Nombre_estudiante }}</td>
                    <td>{{ $estudiante->Apellido1 }}</td>
                    <td>{{ $estudiante->Apellido2 }}</td>
                    <td>{{ $estudiante->sexo }}</td>
                    <td>
                        <span class="badge-ano">{{ $estudiante->year_academico }}</span>
                    </td>
                    @if($tipo === 'sin_tutor')
                        <td class="acciones-cell">
                            <div class="acciones-container">
                                <a href="{{ route('asignarTutor', $estudiante->id) }}"
                                   class="btn-asignar-tutor"
                                   title="Asignar tutor a este estudiante">
                                    <span class="btn-icon">👨‍🏫</span>
                                    <span class="btn-text">Asignar Tutor</span>
                                </a>
                            </div>
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@elseif ($hasFilter)

    {{-- Sin resultados pero se aplicó algún filtro --}}
    <div class="no-resultados">
        <div class="no-resultados-icon">📋</div>
        <h3>No se encontraron estudiantes</h3>
        <p>{{ $c['msg_vacio'] }}</p>
        <p>Intente cambiar los criterios de búsqueda.</p>
    </div>

@else

    {{-- Estado inicial: sin filtros aplicados --}}
    <div class="instrucciones">
        <h3>📊 {{ $c['titulo'] }}</h3>
        <p>{{ $c['descripcion'] }}</p>
        @if($c['filtro'] === 'carrera+year')
            <p>Puede filtrar por año académico para obtener resultados más específicos.</p>
        @endif
    </div>

@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectCarrera = document.getElementById('carrera');
    if (selectCarrera) {
        selectCarrera.addEventListener('change', function() {
            if (this.value) {
                console.log('Carrera seleccionada:', this.value);
            }
        });
    }

    if (window.innerWidth <= 768) {
        const table = document.querySelector('.table-container table');
        if (table) table.classList.add('table-mobile');
    }
});
</script>

@endsection