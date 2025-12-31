@extends('layouts.app')

@section('content')


<link rel="stylesheet" href="css/consultas/estudiantesSinTutor.css">

<form action="/estudiantes_sin_tutor" id="formulario_estudiantes_sin_tutor" method="get">
    @csrf

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

        <div class="campo" id="campo_ano">
            <label for="year_academico">Año Académico</label>
            <select id="year_academico" name="year_academico" class="atributo">
                <option value="">-- Todos los años --</option>
                @php
                    // Obtener años únicos de estudiantes (si ya hay estudiantes cargados)
                    $years = [];
                    if(isset($estudiantes) && $estudiantes->count() > 0) {
                        $years = $estudiantes->pluck('year_academico')->unique()->sort()->toArray();
                    } elseif(isset($carreraSeleccionada)) {
                        // Obtener años de la carrera seleccionada
                        $years = \App\Models\Estudiante::where('id_carrera', $carreraSeleccionada->id)
                            ->distinct()
                            ->pluck('year_academico')
                            ->sort()
                            ->toArray();
                    } else {
                        // Años por defecto - del 1 al 6 (años académicos)
                        $years = range(1, 6);
                    }
                @endphp
                
                @foreach($years as $year)
                    <option value="{{ $year }}" 
                        {{ request('year_academico') == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="campo-boton">
            <input type="submit" value="Buscar" id="aceptar">
            @if(request()->has('carrera'))
                <a href="/estudiantes_sin_tutor" class="btn-limpiar">Limpiar Filtros</a>
            @endif
        </div>
    </div>
</form>

@if(isset($estudiantes))
    @if ($estudiantes->count() > 0)
        <div class="resultado-info">
            <h3>Estudiantes Sin Tutor</h3>
            <div class="estadisticas">
                <p><strong>Total encontrados:</strong> {{ $estudiantes->count() }}</p>
                @if(request('carrera') && isset($carreraSeleccionada))
                    <p><strong>Carrera:</strong> {{ $carreraSeleccionada->Nombre_carrera }}</p>
                @endif
                @if(request('year_academico'))
                    <p><strong>Año académico:</strong> {{ request('year_academico') }}</p>
                @endif
            </div>
        </div>
        
        <div class="table-container">
            <div class="total-registros">
                <span>Mostrando {{ $estudiantes->count() }} estudiantes</span>
            </div>
            
            <table id="tabla-estudiantes">
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
                        <th class="acciones-col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($estudiantes as $estudiante)
                    <tr id="estudiante-{{ $estudiante->id }}">
                        <td data-label="Grupo">
                            @if($estudiante->grupo)
                                {{ $estudiante->grupo->número }}
                            @else
                                <span class="no-data">No asignado</span>
                            @endif
                        </td>
                        <td data-label="Modalidad">
                            @if($estudiante->modalidad)
                                {{ $estudiante->modalidad->Nombre_modalidad }}
                            @else
                                <span class="no-data">No asignada</span>
                            @endif
                        </td>
                        <td data-label="CI">{{ $estudiante->CI_estudiante }}</td>
                        <td data-label="Nro">{{ $estudiante->número }}</td>
                        <td data-label="Nombre">{{ $estudiante->Nombre_estudiante }}</td>
                        <td data-label="Apellido1">{{ $estudiante->Apellido1 }}</td>
                        <td data-label="Apellido2">{{ $estudiante->Apellido2 }}</td>
                        <td data-label="Sexo">{{ $estudiante->sexo }}</td>
                        <td data-label="Fecha de ingreso">{{ date('d/m/Y', strtotime($estudiante->Fecha_ingreso)) }}</td>
                        <td data-label="Año">
                            <span class="badge-ano">
                                {{ $estudiante->year_academico }}
                            </span>
                        </td>
                        <td data-label="Acciones" class="acciones-cell">
                            <div class="acciones-container">
                                <a href="{{ route('asignarTutor', $estudiante->id) }}" 
                                   class="btn-asignar-tutor" 
                                   title="Asignar tutor a este estudiante">
                                    <span class="btn-icon">👨‍🏫</span>
                                    <span class="btn-text">Asignar Tutor</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
       <div class="no-resultados">
           <div class="no-resultados-icon">📋</div>
           <h3>No se encontraron estudiantes</h3>
           <p>No hay estudiantes sin tutor con los filtros aplicados.</p>
           <p>Intente cambiar los criterios de búsqueda.</p>
       </div>
    @endif
@else
    <div class="instrucciones">
        <h3>📊 Consulta de Estudiantes Sin Tutor</h3>
        <p>Seleccione una carrera para ver los estudiantes que no tienen tutor asignado.</p>
        <p>Puede filtrar por año académico para obtener resultados más específicos.</p>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectCarrera = document.getElementById('carrera');
    const selectAno = document.getElementById('year_academico');
    

    if (selectCarrera) {
        selectCarrera.addEventListener('change', function() {
            if (this.value) {
                console.log('Carrera seleccionada:', this.value);
            }
        });
    }

    // Mejorar la experiencia en móviles
    if (window.innerWidth <= 768) {
        const table = document.getElementById('tabla-estudiantes');
        if (table) {
            table.classList.add('table-mobile');
        }
    }
});
</script>

@endsection