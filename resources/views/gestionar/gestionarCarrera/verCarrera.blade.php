@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarCarrera/verCarrera.css') }}">

<div class="container-detalles">
    <div class="header-detalles">
        <h1>Detalles de la Carrera</h1>
        <div class="acciones-header">
            <a href="{{ route('gestionarCarrera') }}" class="btn btn-volver">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
            <a href="{{ route('editarCarrera', $carrera->id) }}" class="btn btn-editar">
                <i class="fas fa-edit"></i> Editar Carrera
            </a>
        </div>
    </div>
    
    <div class="card-detalles">
        <!-- Información Principal -->
        <div class="seccion-info">
            <div class="info-principal">
                <h2>{{ $carrera->Nombre_carrera }}</h2>
                <div class="facultad-info">
                    <span class="badge-facultad">{{ $carrera->facultad->Siglas }}</span>
                    <h3>{{ $carrera->facultad->Nombre_facultad }}</h3>
                </div>
            </div>
            
            <div class="estadisticas">
                <div class="estadistica-item">
                    <div class="estadistica-icono">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor">{{ $carrera->cantidad_estudiantes }}</span>
                        <span class="estadistica-label">Estudiantes</span>
                    </div>
                </div>
                
                <div class="estadistica-item">
                    <div class="estadistica-icono">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor">{{ count($modalidades_carrera) }}</span>
                        <span class="estadistica-label">Modalidades</span>
                    </div>
                </div>
                
                <div class="estadistica-item">
                    <div class="estadistica-icono">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="estadistica-contenido">
                        <span class="estadistica-valor">
                            @if($carrera->created_at)
                                {{ $carrera->created_at->format('Y') }}
                            @else
                                N/A
                            @endif
                        </span>
                        <span class="estadistica-label">Año de Creación</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modalidades -->
        <div class="seccion-detalle">
            <h3><i class="fas fa-list"></i> Modalidades de Estudio</h3>
            
            @if(count($modalidades_carrera) > 0)
            <div class="table-responsive">
                <table class="tabla-detalle">
                    <thead>
                        <tr>
                            <th>Modalidad</th>
                            <th>Duración</th>
                           
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modalidades_carrera as $modalidad)
                        <tr>
                            <td>
                                <strong>{{ $modalidad->Nombre_modalidad }}</strong>
                            </td>
                            <td>
                                <span class="badge-duracion">
                                    {{ $modalidad->cantidad_years }} año(s)
                                </span>
                            </td>
                        
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="no-datos">
                <i class="fas fa-info-circle"></i>
                <p>No hay modalidades configuradas para esta carrera</p>
            </div>
            @endif
        </div>
        
        <!-- Estudiantes por Año Académico -->
        @if($carrera->cantidad_estudiantes > 0)
        <div class="seccion-detalle">
            <h3><i class="fas fa-user-graduate"></i> Distribución de Estudiantes</h3>
            
            <div class="distribucion-estudiantes">
                @if($carrera->estudiantes_por_ano->count() > 0)
                    @foreach($carrera->estudiantes_por_ano as $ano => $estudiantes)
                    <div class="ano-academico">
                        <div class="ano-header">
                            <h4>Año Académico: {{ $ano }}</h4>
                            <span class="badge-cantidad">{{ count($estudiantes) }} estudiantes</span>
                        </div>
                        
                        @if(count($estudiantes) > 0)
                        <div class="table-responsive">
                            <table class="tabla-estudiantes">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre Completo</th>
                                        <th>CI</th>
                                        <th>Grupo</th>
                                        <th>Modalidad</th>
                                        <th>Fecha Ingreso</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($estudiantes as $index => $estudiante)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            {{ $estudiante->Nombre_estudiante }} 
                                            {{ $estudiante->Apellido1 }} 
                                            {{ $estudiante->Apellido2 }}
                                        </td>
                                        <td>{{ $estudiante->CI_estudiante }}</td>
                                        <td>
                                            @if($estudiante->grupo)
                                                <span class="badge-grupo">
                                                    Grupo {{ $estudiante->grupo->número }}
                                                </span>
                                            @else
                                                <span class="text-muted">Sin grupo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($estudiante->modalidad)
                                                <span class="badge-modalidad">
                                                    {{ $estudiante->modalidad->Nombre_modalidad }}
                                                </span>
                                            @else
                                                <span class="text-muted">Sin modalidad</span>
                                            @endif
                                        </td>
                                        <td>{{ $estudiante->Fecha_ingreso }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    @endforeach
                @else
                <div class="no-datos">
                    <i class="fas fa-info-circle"></i>
                    <p>No hay información de distribución por años académicos</p>
                </div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Información Adicional -->
        <div class="seccion-detalle">
            <h3><i class="fas fa-info-circle"></i> Información Adicional</h3>
            
            <div class="info-adicional">
                <div class="info-item">
                    <span class="info-label">Fecha de Creación:</span>
                    <span class="info-valor">
                        @if($carrera->created_at)
                            {{ $carrera->created_at->format('d/m/Y H:i:s') }}
                        @else
                            No disponible
                        @endif
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Última Actualización:</span>
                    <span class="info-valor">
                        @if($carrera->updated_at)
                            {{ $carrera->updated_at->format('d/m/Y H:i:s') }}
                        @else
                            No disponible
                        @endif
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">ID de la Carrera:</span>
                    <span class="info-valor">{{ $carrera->id }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">ID de la Facultad:</span>
                    <span class="info-valor">{{ $carrera->id_facultad }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para expandir/colapsar secciones
document.querySelectorAll('.ano-header').forEach(header => {
    header.addEventListener('click', function() {
        const content = this.nextElementSibling;
        if (content) {
            content.style.display = content.style.display === 'none' ? 'block' : 'none';
            this.classList.toggle('collapsed');
        }
    });
});
</script>
@endsection