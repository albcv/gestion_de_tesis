@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarFundamentación/verFundamentación.css') }}">


<div class="detalle-container">
    <h1>Detalles de la Fundamentación</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarFundamentaciones" class="btn-volver">← Volver a la lista</a>
        
        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
            <a href="/editarFundamentación/{{ $fundamentacion->id_fundamentacion }}" class="btn-editar">✏️ Editar Fundamentación</a>
        @endif
    </div>
    
    <!-- Información básica de la fundamentación -->
    <div class="seccion-datos">
        <h2>Información de la Fundamentación</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>ID de la Fundamentación:</label>
                <span>{{ $fundamentacion->id_fundamentacion }}</span>
            </div>
            <div class="campo-dato">
                <label>Estado:</label>
                @if($fundamentacion->aprobada)
                    <span class="badge-estado estado-aprobado">Aprobada</span>
                @elseif($fundamentacion->desaprobada)
                    <span class="badge-estado estado-desaprobado">Desaprobada</span>
                @else
                    <span class="badge-estado estado-pendiente">Pendiente</span>
                @endif
            </div>
            <div class="campo-dato">
                <label>Fecha de Creación:</label>
                <span>{{ $fundamentacion->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Última Actualización:</label>
                <span>{{ $fundamentacion->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Total de Versiones:</label>
                <span>{{ $fundamentacion->versiones->count() }}</span>
            </div>
        </div>
    </div>
    
    <!-- Información de la tesis -->
    @if($fundamentacion->tesis)
    <div class="seccion-datos">
        <h2>Información de la Tesis</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Trabajo de Diploma:</label>
                <span class="nombre-tesis">{{ $fundamentacion->tesis->Nombre_trabajo }}</span>
            </div>
            
            @if($fundamentacion->tesis->estudiante)
                <div class="campo-dato">
                    <label>Estudiante:</label>
                    <span>
                        {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                        {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                        {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                    </span>
                </div>
                <div class="campo-dato">
                    <label>CI del Estudiante:</label>
                    <span>{{ $fundamentacion->tesis->estudiante->CI_estudiante }}</span>
                </div>
                
                <!-- Tutor del estudiante -->
                @php
                    // Obtener el tutor del estudiante desde la tabla tutor_estudiante
                    $tutor = App\Models\Profesor::join('tutor_estudiante', 'profesor.id', '=', 'tutor_estudiante.id_profesor')
                        ->where('tutor_estudiante.id_estudiante', $fundamentacion->tesis->estudiante->id)
                        ->first();
                @endphp
                
                @if($tutor)
                    <div class="campo-dato">
                        <label>Tutor:</label>
                        <span>
                            {{ $tutor->Nombre_profesor }} 
                            {{ $tutor->Apellido1 }}
                            {{ $tutor->Apellido2 }}
                        </span>
                    </div>
                @else
                    <div class="campo-dato">
                        <label>Tutor:</label>
                        <span style="color: #000;">No asignado</span>
                    </div>
                @endif
                
                @if($fundamentacion->tesis->estudiante->grupo)
                    <div class="campo-dato">
                        <label>Grupo:</label>
                        <span>{{ $fundamentacion->tesis->estudiante->grupo->número }}</span>
                    </div>
                @endif
                
                <!-- Carrera y facultad desde el estudiante -->
                @if($fundamentacion->tesis->estudiante->carrera)
                    <div class="campo-dato">
                        <label>Carrera:</label>
                        <span>{{ $fundamentacion->tesis->estudiante->carrera->Nombre_carrera }}</span>
                    </div>
                    
                    @if($fundamentacion->tesis->estudiante->carrera->facultad)
                        <div class="campo-dato">
                            <label>Facultad:</label>
                            <span>{{ $fundamentacion->tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
    @endif
    
    <!-- Versiones de la Fundamentación -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Versiones de la Fundamentación</h2>
            <div class="acciones-versiones">
                @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                    <a href="/editarFundamentación/{{ $fundamentacion->id_fundamentacion }}" class="btn-agregar-version" style="color:#030; border: 2px solid #030; padding: 10px;  border-radius: 10px;">
                        ✏️ Gestionar Versiones
                    </a>
                @endif
            </div>
        </div>
      
        
        @if($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
            <div class="tabla-versiones">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Versión</th>
                            <th>Nombre del Archivo</th>
                            <th>Tamaño</th>
                            <th>Descripción</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundamentacion->versiones as $version)
                            <tr>
                                <td>
                                    <strong>v{{ $version->version_numero }}</strong>
                                    @if($loop->last)
                                        <span class="badge bg-primary">Actual</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="nombre-archivo">
                                        @if($version->tipo === 'pdf')
                                            <i class="fas fa-file-pdf text-danger"></i>
                                        @elseif(in_array($version->tipo, ['doc', 'docx']))
                                            <i class="fas fa-file-word text-primary"></i>
                                        @else
                                            <i class="fas fa-file text-secondary"></i>
                                        @endif
                                        {{ $version->nombre_archivo }}
                                    </div>
                                    <small class="text-muted">{{ strtoupper($version->tipo) }}</small>
                                </td>
                                <td>
                                    @if($version->tamanio >= 1048576)
                                        {{ number_format($version->tamanio / 1048576, 2) }} MB
                                    @elseif($version->tamanio >= 1024)
                                        {{ number_format($version->tamanio / 1024, 2) }} KB
                                    @else
                                        {{ $version->tamanio }} bytes
                                    @endif
                                </td>
                                <td>
                                    @if($version->descripcion)
                                        {{ $version->descripcion }}
                                    @else
                                        <span class="text-muted">Sin descripción</span>
                                    @endif
                                </td>
                                <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group-version">
                                        <a href="{{ route('ver-documento-version', $version->id) }}" 
                                           class="btn-descargar-version" title="Descargar">
                                            📥
                                        </a>
                                        
                                        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada && $fundamentacion->versiones->count() > 1)
                                            <form method="POST" action="{{ route('eliminar-version-fundamentacion', $version->id) }}" 
                                                  class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta versión?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-eliminar-version" title="Eliminar">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Estadísticas -->
                <div class="estadisticas-versiones">
                    <div class="estadistica">
                        <span class="numero">{{ $fundamentacion->versiones->count() }}</span>
                        <span class="texto">Versiones</span>
                    </div>
                    <div class="estadistica">
                        <span class="numero">{{ number_format($fundamentacion->versiones->sum('tamanio') / 1048576, 2) }}</span>
                        <span class="texto">MB Total</span>
                    </div>
                  
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3"></i>
                    <div>
                        <h5 class="mb-1">No hay versiones disponibles</h5>
                        <p class="mb-0">Para agregar versiones, edite la fundamentación.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Recomendación -->
    <div class="seccion-datos">
        <div class="recomendacion-header">
            <h2>Recomendación</h2>
            
            <div class="acciones-recomendacion">
                @if($fundamentacion->recomendacion)
                    <a href="/editarRecomendacionFundamentacion/{{ $fundamentacion->recomendacion->id_recomendaciones_fundamentacion }}" 
                       class="btn-modificar-rec" title="Modificar recomendación">
                        ✏️ Modificar
                    </a>
                    <button type="button" class="btn-eliminar-rec" 
                            onclick="eliminarRecomendacion({{ $fundamentacion->recomendacion->id_recomendaciones_fundamentacion }})"
                            title="Eliminar recomendación">
                        🗑️ Eliminar
                    </button>
                @else
                    <a href="/agregarRecomendacionFundamentacion/{{ $fundamentacion->id_fundamentacion }}" 
                       class="btn-agregar-rec" title="Agregar recomendación">
                        ➕ Agregar Recomendación
                    </a>
                @endif
            </div>
        </div>
        
        @if($fundamentacion->recomendacion && !empty($fundamentacion->recomendacion->recomendacion))
            <div class="recomendacion-contenido">
                <div class="recomendacion-texto">
                    {{ $fundamentacion->recomendacion->recomendacion }}
                </div>
                <div class="recomendacion-metadata">
                    <span class="fecha-creacion">
                        <strong>Fecha de Creación:</strong> 
                        {{ $fundamentacion->recomendacion->created_at->format('d/m/Y') }}
                    </span>
                    <span class="fecha-actualizacion">
                        <strong>Última Actualización:</strong> 
                        {{ $fundamentacion->recomendacion->updated_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        @else
            <div class="sin-recomendacion">
                <div class="icono-sin-rec">✓</div>
                <h3>No hay recomendación</h3>
                <p>Esta fundamentación no tiene una recomendación asociada.</p>
            </div>
        @endif
    </div>

    <!-- Profesores Vinculados con acciones -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Profesores Oponentes</h2>
            <a href="{{ route('vincularProfesorFundamentación', ['id' => $fundamentacion->id_fundamentacion]) }}" class="btn-agregar-profesor">
                ➕ Vincular Profesor
            </a>
        </div>
        
        @if($fundamentacion->profesores && $fundamentacion->profesores->count() > 0)
            <div class="tabla-profesores">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre del Profesor</th>
                            <th>Departamento</th>
                            <th>Categoría Docente</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundamentacion->profesores as $index => $profesor)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $profesor->Nombre_profesor }}</strong><br>
                                    {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                </td>
                                <td>
                                    @if($profesor->departamento)
                                        {{ $profesor->departamento->Nombre_departamento }}
                                    @else
                                        <span class="sin-dato">No especificado</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="categoria-docente">{{ $profesor->Categoria_docente }}</span>
                                </td>
                                <td>
                                    <div class="acciones-profesor">
                                        <form method="POST" action="{{ route('desvincularProfesorFundamentación') }}" 
                                              style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="fundamentacion_id" value="{{ $fundamentacion->id_fundamentacion }}">
                                            <input type="hidden" name="profesor_id" value="{{ $profesor->id }}">
                                            <button type="submit" class="btn-desvincular-profesor" 
                                                    onclick="return confirm('¿Está seguro de desvincular este profesor de la fundamentación?')"
                                                    title="Desvincular">
                                                🗑️ Desvincular
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Estadísticas de profesores -->
            <div class="estadisticas-profesores">
                <div class="estadistica-item">
                    <span class="numero">{{ $fundamentacion->profesores->count() }}</span>
                    <span class="texto">Profesores Oponentes</span>
                </div>
            </div>
        @else
            <div class="sin-profesores">
                <div class="icono-sin-profesor">👨‍🏫</div>
                <h3>No hay profesores oponentes</h3>
                <p>Esta fundamentación no tiene profesores asignados.</p>
                <a href="{{ route('vincularProfesorFundamentación', ['id' => $fundamentacion->id_fundamentacion]) }}" class="btn-agregar-profesor-empty">
                    ➕ Vincular Primer Profesor
                </a>
            </div>
        @endif
    </div>
    
    <!-- Historial de estados -->
    <div class="seccion-datos">
        <h2>Historial de Estados</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Fecha de Aprobación:</label>
                <span>
                    @if($fundamentacion->aprobada && $fundamentacion->aprobada->created_at)
                        {{ $fundamentacion->aprobada->created_at->format('d/m/Y H:i:s') }}
                    @else
                        <span class="sin-fecha">No aprobada</span>
                    @endif
                </span>
            </div>
            <div class="campo-dato">
                <label>Fecha de Desaprobación:</label>
                <span>
                    @if($fundamentacion->desaprobada && $fundamentacion->desaprobada->created_at)
                        {{ $fundamentacion->desaprobada->created_at->format('d/m/Y H:i:s') }}
                    @else
                        <span class="sin-fecha">No desaprobada</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    
    <!-- Acciones -->
    <div class="seccion-acciones">
        <h2>Acciones</h2>
        <div class="botones-accion">
            @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                <button type="button" class="btn-aprobar" onclick="aprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})">
                    ✅ Aprobar Fundamentación
                </button>
                <button type="button" class="btn-desaprobar" onclick="desaprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})">
                    ❌ Desaprobar Fundamentación
                </button>
            @else
                <button type="button" class="btn-revertir" onclick="revertirFundamentacion({{ $fundamentacion->id_fundamentacion }})">
                    ↩️ Revertir a Pendiente
                </button>
            @endif
        </div>
    </div>
    
    <!-- Botones inferiores -->
    <div class="botones-inferiores">
        <a href="/gestionarFundamentaciones" class="btn-volver">← Volver a la lista</a>
        
        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
            <a href="/editarFundamentación/{{ $fundamentacion->id_fundamentacion }}" class="btn-editar">✏️ Editar Fundamentación</a>
        @endif
        
        <!-- Formulario para eliminar -->
        <form id="formEliminar" method="POST" action="/eliminarFundamentación" style="display: inline;">
            @csrf
            <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
            <button type="button" class="btn-eliminar" onclick="confirmarEliminacion()">
                🗑️ Eliminar Fundamentación
            </button>
        </form>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="formAprobarFundamentacion" method="POST" action="/aprobarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdAprobarFundamentacion">
</form>

<form id="formDesaprobarFundamentacion" method="POST" action="/desaprobarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdDesaprobarFundamentacion">
</form>

<form id="formRevertirFundamentacion" method="POST" action="/revertirFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdRevertirFundamentacion">
</form>

<!-- Formulario oculto para eliminar recomendación -->
<form id="formEliminarRecomendacion" method="POST" action="/eliminarRecomendacionFundamentacion" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminarRecomendacion">
</form>

<script>
// Función para confirmar eliminación
function confirmarEliminacion() {
    if (confirm('¿Está seguro de que desea eliminar esta fundamentación? Esta acción no se puede deshacer.')) {
        document.getElementById('formEliminar').submit();
    }
}

// Función para aprobar fundamentación
function aprobarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea aprobar esta fundamentación?')) {
        document.getElementById('inputIdAprobarFundamentacion').value = id;
        document.getElementById('formAprobarFundamentacion').submit();
    }
}

// Función para desaprobar fundamentación
function desaprobarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea desaprobar esta fundamentación?')) {
        document.getElementById('inputIdDesaprobarFundamentacion').value = id;
        document.getElementById('formDesaprobarFundamentacion').submit();
    }
}

// Función para revertir fundamentación
function revertirFundamentacion(id) {
    if (confirm('¿Está seguro de que desea revertir esta fundamentación a estado pendiente?')) {
        document.getElementById('inputIdRevertirFundamentacion').value = id;
        document.getElementById('formRevertirFundamentacion').submit();
    }
}

// Función para eliminar recomendación
function eliminarRecomendacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta recomendación? Esta acción no se puede deshacer.')) {
        document.getElementById('inputIdEliminarRecomendacion').value = id;
        document.getElementById('formEliminarRecomendacion').submit();
    }
}

// Validación antes de desvincular profesor
document.addEventListener('DOMContentLoaded', function() {
    const formsDesvincular = document.querySelectorAll('.btn-desvincular-profesor');
    formsDesvincular.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('¿Está seguro de desvincular este profesor de la fundamentación?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

@endsection