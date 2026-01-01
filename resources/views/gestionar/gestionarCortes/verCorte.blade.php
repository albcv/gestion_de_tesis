@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/gestionar/gestionarCortes/verCorte.css'])


<style>
    /* Estilos para las versiones de corte */
    .tabla-versiones {
        margin-top: 20px;
        overflow-x: auto;
    }
    
    .tabla-versiones table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    
    .tabla-versiones th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    .tabla-versiones td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .tabla-versiones tr:hover {
        background-color: #f8f9fa;
    }
    
    .nombre-archivo {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .badge {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 4px;
        margin-left: 8px;
    }
    
    .bg-primary {
        background-color: #007bff !important;
        color: white;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .text-primary {
        color: #007bff !important;
    }
    
    .text-secondary {
        color: #6c757d !important;
    }
    
    .text-muted {
        color: #6c757d !important;
        font-size: 12px;
    }
    
    .btn-group-version {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    
    .btn-descargar-version,
    .btn-eliminar-version {
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }
    
    .btn-descargar-version {
        background-color: #4CAF50;
        color: white;
    }
    
    .btn-descargar-version:hover {
        background-color: #45a049;
    }
    
    .btn-eliminar-version {
        background-color: #dc3545;
        color: white;
    }
    
    .btn-eliminar-version:hover {
        background-color: #c82333;
    }
    
    .estadisticas-versiones {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }
    
    .estadistica {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px 20px;
    }
    
    .numero {
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }
    
    .texto {
        font-size: 14px;
        color: #6c757d;
        margin-top: 5px;
    }
    
    .enlace-github {
        color: #0366d6;
        text-decoration: none;
        word-break: break-all;
    }
    
    .enlace-github:hover {
        text-decoration: underline;
    }
</style>

<div class="detalle-container">
    <h1>Detalles del Corte de Tesis</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarCortes" class="btn-volver">← Volver a la lista</a>
        
        @if(!$corte->aprobado && !$corte->desaprobado)
            <a href="/editarCorte/{{ $corte->idCortes_de_tesis }}" class="btn-editar">✏️ Editar Corte</a>
        @endif
    </div>
    
    <!-- Información básica del corte -->
    <div class="seccion-datos">
        <h2>Información del Corte</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>ID del Corte:</label>
                <span>{{ $corte->idCortes_de_tesis }}</span>
            </div>
            <div class="campo-dato">
                <label>Número de Corte:</label>
                <span class="badge-corte">Corte {{ $corte->Numero_corte }}</span>
            </div>
            <div class="campo-dato">
                <label>Estado:</label>
                @if($corte->aprobado)
                    <span class="badge-estado estado-aprobado">Aprobado</span>
                @elseif($corte->desaprobado)
                    <span class="badge-estado estado-desaprobado">Desaprobado</span>
                @else
                    <span class="badge-estado estado-pendiente">Pendiente</span>
                @endif
            </div>
            <div class="campo-dato">
                <label>Fecha de Creación:</label>
                <span>{{ $corte->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Última Actualización:</label>
                <span>{{ $corte->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Total de Versiones:</label>
                <span>{{ $corte->versiones->count() }}</span>
            </div>
        </div>
    </div>
    
    <!-- Información de la tesis -->
    @if($corte->tesis)
    <div class="seccion-datos">
        <h2>Información de la Tesis</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Trabajo de Diploma:</label>
                <span>{{ $corte->tesis->Nombre_trabajo }}</span>
            </div>
            
            @if($corte->tesis->estudiante)
                <div class="campo-dato">
                    <label>Estudiante:</label>
                    <span>
                        {{ $corte->tesis->estudiante->Nombre_estudiante }} 
                        {{ $corte->tesis->estudiante->Apellido1 }}
                        {{ $corte->tesis->estudiante->Apellido2 }}
                    </span>
                </div>
                <div class="campo-dato">
                    <label>CI del Estudiante:</label>
                    <span>{{ $corte->tesis->estudiante->CI_estudiante }}</span>
                </div>
                
                <!-- Tutor del estudiante -->
                @php
                    $tutor = App\Models\Profesor::join('tutor_estudiante', 'profesor.id', '=', 'tutor_estudiante.id_profesor')
                        ->where('tutor_estudiante.id_estudiante', $corte->tesis->estudiante->id)
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
                        <span style="color: #666;">No asignado</span>
                    </div>
                @endif
                
                @if($corte->tesis->estudiante->grupo)
                    <div class="campo-dato">
                        <label>Grupo:</label>
                        <span>{{ $corte->tesis->estudiante->grupo->número }}</span>
                    </div>
                @endif
                
                @if($corte->tesis->estudiante->carrera)
                    <div class="campo-dato">
                        <label>Carrera:</label>
                        <span>{{ $corte->tesis->estudiante->carrera->Nombre_carrera }}</span>
                    </div>
                    
                    @if($corte->tesis->estudiante->carrera->facultad)
                        <div class="campo-dato">
                            <label>Facultad:</label>
                            <span>{{ $corte->tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                        </div>
                    @endif
                @endif
            @endif
        </div>
    </div>
    @endif
    
    <!-- Versiones del Corte -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Versiones del Corte</h2>
            <div class="acciones-versiones">
                @if(!$corte->aprobado && !$corte->desaprobado)
                    <a href="/editarCorte/{{ $corte->idCortes_de_tesis }}" class="btn-agregar-version" style="color:#030; border: 2px solid #030; padding: 10px; border-radius: 10px;">
                        ✏️ Gestionar Versiones
                    </a>
                @endif
            </div>
        </div>
        
        @if($corte->versiones && $corte->versiones->count() > 0)
            <div class="tabla-versiones">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Versión</th>
                            <th>Nombre del Archivo</th>
                            <th>Enlace GitHub</th>
                            <th>Tamaño</th>
                            <th>Descripción</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corte->versiones as $version)
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
                                    @if($version->Enlace_Github)
                                        <a href="{{ $version->Enlace_Github }}" 
                                           target="_blank" 
                                           class="enlace-github"
                                           title="{{ $version->Enlace_Github }}">
                                            🔗 Ver en GitHub
                                        </a>
                                    @else
                                        <span class="text-muted">Sin enlace</span>
                                    @endif
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
                                        <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                           class="btn-descargar-version" title="Descargar">
                                            📥
                                        </a>
                                        
                                        @if(!$corte->aprobado && !$corte->desaprobado && $corte->versiones->count() > 1)
                                            <form method="POST" action="{{ route('eliminar-version-corte', $version->id) }}" 
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
                        <span class="numero">{{ $corte->versiones->count() }}</span>
                        <span class="texto">Versiones</span>
                    </div>
                    <div class="estadistica">
                        <span class="numero">{{ number_format($corte->versiones->sum('tamanio') / 1048576, 2) }}</span>
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
                        <p class="mb-0">Para agregar versiones, edite el corte.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- No Conformidades con acciones -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>No Conformidades</h2>
            <a href="{{ route('agregarNoConformidadCorte', $corte->idCortes_de_tesis) }}" class="btn-agregar-nc">
                ➕ Agregar No Conformidad
            </a>
        </div>
        
        @if($corte->noConformidades && $corte->noConformidades->count() > 0)
            <div class="tabla-noconformidades">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Deficiencias Detectadas</th>
                            <th>Fecha de Asociación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corte->noConformidades as $index => $noConformidad)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $noConformidad->Deficiencias_detectadas }}</td>
                                <td>
                                    @if(isset($noConformidad->pivot) && isset($noConformidad->pivot->created_at))
                                        {{ date('d/m/Y', strtotime($noConformidad->pivot->created_at)) }}
                                    @else
                                        <span class="sin-fecha">No especificada</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="acciones-nc">
                                        <a href="{{ route('editarNoConformidadCorte', ['id_corte' => $corte->idCortes_de_tesis, 'id_nc' => $noConformidad->idNoConformidades]) }}" 
                                           class="btn-editar-nc" title="Editar relación">
                                            ✏️
                                        </a>
                                        <form method="POST" action="{{ route('desvincularNoConformidadCorte') }}" 
                                              style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="corte_tesis_id" value="{{ $corte->idCortes_de_tesis }}">
                                            <input type="hidden" name="no_conformidad_id" value="{{ $noConformidad->idNoConformidades }}">
                                            <button type="submit" class="btn-desvincular-nc" 
                                                    onclick="return confirm('¿Está seguro de desvincular esta no conformidad del corte?')"
                                                    title="Desvincular">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Estadísticas -->
            <div class="estadisticas-nc">
                <div class="estadistica-item">
                    <span class="numero">{{ $corte->noConformidades->count() }}</span>
                    <span class="texto">No Conformidades Asociadas</span>
                </div>
            </div>
        @else
            <div class="sin-noconformidades">
                <div class="icono-sin-nc">✓</div>
                <h3>No hay no conformidades asociadas</h3>
                <p>Este corte no tiene deficiencias o problemas reportados.</p>
            </div>
        @endif
    </div>

    <!-- Profesores Oponentes con acciones -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Profesores Oponentes</h2>
            <a href="{{ route('vincularProfesorCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-agregar-profesor">
                ➕ Vincular Profesor
            </a>
        </div>
        
        @if($corte->profesores && $corte->profesores->count() > 0)
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
                        @foreach($corte->profesores as $index => $profesor)
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
                                        <form method="POST" action="{{ route('desvincularProfesorCorte') }}" 
                                              style="display: inline;">
                                            @csrf
                                            <input type="hidden" name="corte_tesis_id" value="{{ $corte->idCortes_de_tesis }}">
                                            <input type="hidden" name="profesor_id" value="{{ $profesor->id }}">
                                            <button type="submit" class="btn-desvincular-profesor" 
                                                    onclick="return confirm('¿Está seguro de desvincular este profesor del corte?')"
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
                    <span class="numero">{{ $corte->profesores->count() }}</span>
                    <span class="texto">Profesores Asociados</span>
                </div>
            </div>
        @else
            <div class="sin-profesores">
                <div class="icono-sin-profesor">👨‍🏫</div>
                <h3>No hay profesores oponentes asociados</h3>
                <p>Este corte no tiene profesores oponentes asignados.</p>
                <a href="{{ route('vincularProfesorCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-agregar-profesor-empty">
                    ➕ Vincular Primer Profesor
                </a>
            </div>
        @endif
    </div>
    
    <!-- Acciones -->
    <div class="seccion-acciones">
        <h2>Acciones</h2>
        <div class="botones-accion">
            @if(!$corte->aprobado && !$corte->desaprobado)
                <button type="button" class="btn-aprobar" onclick="aprobarCorte({{ $corte->idCortes_de_tesis }})">
                    ✅ Aprobar Corte
                </button>
                <button type="button" class="btn-desaprobar" onclick="desaprobarCorte({{ $corte->idCortes_de_tesis }})">
                    ❌ Desaprobar Corte
                </button>
            @else
                <button type="button" class="btn-revertir" onclick="revertirCorte({{ $corte->idCortes_de_tesis }})">
                    ↩️ Revertir a Pendiente
                </button>
            @endif
        </div>
    </div>
    
    <!-- Botones inferiores -->
    <div class="botones-inferiores">
        <a href="/gestionarCortes" class="btn-volver">← Volver a la lista</a>
        
        @if(!$corte->aprobado && !$corte->desaprobado)
            <a href="/editarCorte/{{ $corte->idCortes_de_tesis }}" class="btn-editar">✏️ Editar Corte</a>
        @endif
        
        <!-- Formulario para eliminar -->
        <form id="formEliminar" method="POST" action="/eliminarCorte" style="display: inline;">
            @csrf
            <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
            <button type="button" class="btn-eliminar" onclick="confirmarEliminacion()">
                🗑️ Eliminar Corte
            </button>
        </form>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="formAprobarCorte" method="POST" action="/aprobarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdAprobarCorte">
</form>

<form id="formDesaprobarCorte" method="POST" action="/desaprobarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdDesaprobarCorte">
</form>

<form id="formRevertirCorte" method="POST" action="/revertirCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdRevertirCorte">
</form>

<script>
// Función para confirmar eliminación
function confirmarEliminacion() {
    if (confirm('¿Está seguro de que desea eliminar este corte? Esta acción no se puede deshacer.')) {
        document.getElementById('formEliminar').submit();
    }
}

// Función para aprobar corte
function aprobarCorte(id) {
    if (confirm('¿Está seguro de que desea aprobar este corte?')) {
        document.getElementById('inputIdAprobarCorte').value = id;
        document.getElementById('formAprobarCorte').submit();
    }
}

// Función para desaprobar corte
function desaprobarCorte(id) {
    if (confirm('¿Está seguro de que desea desaprobar este corte?')) {
        document.getElementById('inputIdDesaprobarCorte').value = id;
        document.getElementById('formDesaprobarCorte').submit();
    }
}

// Función para revertir corte
function revertirCorte(id) {
    if (confirm('¿Está seguro de que desea revertir este corte a estado pendiente?')) {
        document.getElementById('inputIdRevertirCorte').value = id;
        document.getElementById('formRevertirCorte').submit();
    }
}
</script>

@endsection