@extends('layouts.app')

@section('content')
@vite(['resources/css/gestionar/gestionarCarrera/gestionarCarrera.css'])

<div class="container-fluid">
    <div class="main-content">
        <h1>Gestionar Carreras</h1>

        <!-- Filtros -->
        <div class="filtros-container">
            <div class="filtros-header">
                <i class="fas fa-filter"></i>
                <h3>Filtrar Carreras</h3>
            </div>
            <form method="GET" action="{{ route('gestionarCarrera') }}" class="form-filtros">
                <div class="row-filtros">
                    <div class="campo-filtro">
                        <label for="facultad_filtro">
                            <i class="fas fa-university"></i> Facultad:
                        </label>
                        <select id="facultad_filtro" name="facultad_id" class="select-filtro">
                            <option value="">Todas las facultades</option>
                            @foreach($facultadesSelect as $facultad)
                                <option value="{{ $facultad->idFacultad }}" 
                                    {{ request('facultad_id') == $facultad->idFacultad ? 'selected' : '' }}>
                                    {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="campo-filtro">
                        <label for="carrera_filtro">
                            <i class="fas fa-graduation-cap"></i> Nombre Carrera:
                        </label>
                        <input type="text" id="carrera_filtro" name="carrera_nombre" class="input-filtro" 
                               value="{{ request('carrera_nombre') }}" 
                               placeholder="Buscar por nombre...">
                    </div>
                    
                    <div class="botones-filtro">
                        <button type="submit" class="btn btn-filtrar">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('gestionarCarrera') }}" class="btn btn-limpiar">
                            <i class="fas fa-eraser"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Botón para agregar nueva carrera -->
        <div class="acciones-superiores">
            <a href="{{ route('agregarCarrera') }}" class="btn btn-agregar">
                <i class="fas fa-plus-circle"></i> Agregar Nueva Carrera
            </a>
            <div class="contador-carreras">
                <span class="badge-contador">
                    <i class="fas fa-list-alt"></i> 
                    {{ $carreras->count() }} {{ $carreras->count() == 1 ? 'carrera' : 'carreras' }}
                </span>
            </div>
        </div>

        <!-- Tabla de carreras -->
        @if($carreras->count() > 0)
        <div class="card-table">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-table"></i>
                    <h4>Lista de Carreras</h4>
                </div>
            </div>
            <div class="table-responsive">
                <table class="tabla-carreras" id="tablaCarreras">
                    <thead>
                        <tr>
                            <th width="30%">Nombre de la Carrera</th>
                            <th width="25%">Facultad</th>
                            <th width="20%">Modalidades</th>
                            <th width="10%" class="text-center">Estudiantes</th>
                            <th width="15%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($carreras as $carrera)
                        <tr>
                            <td>
                                <div class="carrera-info">
                                    <strong class="carrera-nombre">{{ $carrera->Nombre_carrera }}</strong>
                                    <small class="carrera-fecha">
                                        <i class="far fa-calendar-alt"></i> 
                                        {{ $carrera->created_at ? $carrera->created_at->format('d/m/Y') : 'N/A' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="facultad-info">
                                    <span class="badge-facultad">
                                        {{ $carrera->facultad->Siglas }}
                                    </span>
                                    <div class="facultad-detalle">
                                        <strong>{{ $carrera->facultad->Nombre_facultad }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($carrera->modalidades->count() > 0)
                                    <div class="modalidades-container">
                                        <div class="modalidades-lista">
                                            @foreach($carrera->modalidades as $modalidad)
                                                <span class="badge-modalidad" 
                                                      data-tooltip="Duración: {{ $modalidad->pivot->cantidad_years ?? 'N/A' }} años">
                                                    {{ $modalidad->Nombre_modalidad }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <small class="cantidad-modalidades">
                                            {{ $carrera->modalidades->count() }} {{ $carrera->modalidades->count() == 1 ? 'modalidad' : 'modalidades' }}
                                        </small>
                                    </div>
                                @else
                                    <div class="sin-modalidades">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <span>Sin modalidades</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="estudiantes-info">
                                    <span class="badge-estudiantes {{ $carrera->cantidad_estudiantes > 0 ? 'activa' : 'inactiva' }}">
                                        {{ $carrera->cantidad_estudiantes }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center acciones-td">
                                <div class="btn-group-acciones">
                                    <a href="{{ route('verCarrera', $carrera->id) }}" 
                                       class="btn btn-ver" 
                                       data-tooltip="Ver detalles completos">
                                        <i class="fas fa-eye"></i>
                                        <span class="btn-text">Detalles</span>
                                    </a>
                                    <a href="{{ route('editarCarrera', $carrera->id) }}" 
                                       class="btn btn-editar" 
                                       data-tooltip="Editar información">
                                        <i class="fas fa-edit"></i>
                                        <span class="btn-text">Editar</span>
                                    </a>
                                    <form action="{{ route('eliminarCarrera') }}" method="POST" 
                                          class="form-eliminar" 
                                          onsubmit="return confirmarEliminacion({{ $carrera->id }}, '{{ addslashes($carrera->Nombre_carrera) }}')">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $carrera->id }}">
                                        <button type="submit" class="btn btn-eliminar" 
                                                data-tooltip="Eliminar carrera">
                                            <i class="fas fa-trash"></i>
                                            <span class="btn-text">Eliminar</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="footer-totales">
                            <td colspan="3" class="text-right">
                                <strong>Total de estudiantes:</strong>
                            </td>
                            <td class="text-center">
                                <strong class="total-estudiantes">
                                    {{ $carreras->sum('cantidad_estudiantes') }}
                                </strong>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @else
        <div class="card-empty">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 style="font-size: 40px">No hay resultados</h3>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function confirmarEliminacion(id, nombre) {
    return confirm(`¿Está seguro de eliminar la carrera "${nombre}"?\n\nEsta acción no se puede deshacer.`);
}

// Tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            
            this.tooltipElement = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltipElement) {
                this.tooltipElement.remove();
                this.tooltipElement = null;
            }
        });
    });
    
    // Alternar visibilidad de botones en responsive
    const btnGroup = document.querySelector('.btn-group-acciones');
    if (btnGroup && window.innerWidth < 768) {
        btnGroup.addEventListener('click', function(e) {
            if (e.target.closest('.btn')) {
                this.classList.toggle('expanded');
            }
        });
    }
});
</script>
@endsection