@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarTesis/verTesis.css') }}">



<div class="detalle-container">
    <h1>Detalles de la Tesis</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarTesis" class="btn-volver">← Volver a la lista</a>
        <a href="/editarTesis/{{ $tesis->id }}" class="btn-editar">✏️ Editar Tesis</a>
    </div>
    
    <!-- Información básica de la tesis -->
    <div class="seccion-datos">
        <h2>Información de la Tesis</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>ID de la Tesis:</label>
                <span>{{ $tesis->id }}</span>
            </div>
            <div class="campo-dato">
                <label>Nombre del Trabajo:</label>
                <span class="nombre-tesis">{{ $tesis->Nombre_trabajo }}</span>
            </div>
            <div class="campo-dato">
                <label>Fecha de Creación:</label>
                <span>{{ $tesis->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Última Actualización:</label>
                <span>{{ $tesis->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Información del estudiante -->
    @if($tesis->estudiante)
    <div class="seccion-datos">
        <h2>Información del Estudiante</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Nombre Completo:</label>
                <span>
                    {{ $tesis->estudiante->Nombre_estudiante }} 
                    {{ $tesis->estudiante->Apellido1 }}
                    {{ $tesis->estudiante->Apellido2 }}
                </span>
            </div>
            <div class="campo-dato">
                <label>CI del Estudiante:</label>
                <span>{{ $tesis->estudiante->CI_estudiante }}</span>
            </div>
            <div class="campo-dato">
                <label>Sexo:</label>
                <span>{{ $tesis->estudiante->sexo }}</span>
            </div>
            <div class="campo-dato">
                <label>Fecha de Ingreso:</label>
                <span>{{ \Carbon\Carbon::parse($tesis->estudiante->Fecha_ingreso)->format('d/m/Y') }}</span>
            </div>
            @if($tesis->estudiante->grupo)
                <div class="campo-dato">
                    <label>Grupo:</label>
                    <span>{{ $tesis->estudiante->grupo->número }}</span>
                </div>
            @endif
            @if($tesis->estudiante->modalidad)
                <div class="campo-dato">
                    <label>Modalidad:</label>
                    <span>{{ $tesis->estudiante->modalidad->Nombre_modalidad }}</span>
                </div>
            @endif
            @if($tesis->estudiante->carrera)
                <div class="campo-dato">
                    <label>Carrera:</label>
                    <span>{{ $tesis->estudiante->carrera->Nombre_carrera }}</span>
                </div>
            @endif
            @if($tesis->estudiante->carrera && $tesis->estudiante->carrera->facultad)
                <div class="campo-dato">
                    <label>Facultad:</label>
                    <span>{{ $tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                </div>
            @endif
            
            <!-- Tutor del estudiante -->
            @if($tutor && $tutor->profesor)
                <div class="campo-dato">
                    <label>Tutor:</label>
                    <span>
                        {{ $tutor->profesor->Nombre_profesor }} 
                        {{ $tutor->profesor->Apellido1 }}
                        {{ $tutor->profesor->Apellido2 }}
                    </span>
                </div>
            @else
                <div class="campo-dato">
                    <label>Tutor:</label>
                    <span style="color: #6c757d; font-style: italic;">No asignado</span>
                </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Información de la fundamentación -->
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Fundamentación de la Tesis</h2>
            
            <div class="acciones-fundamentacion">
                @if($tesis->fundamentacion)
                    <a href="/editarFundamentación/{{ $tesis->fundamentacion->id_fundamentacion }}" 
                       class="btn-modificar-fund" title="Modificar fundamentación">
                        ✏️ Gestionar Versiones
                    </a>
                    <button type="button" class="btn-eliminar-fund" 
                            onclick="eliminarFundamentacion({{ $tesis->fundamentacion->id_fundamentacion }})"
                            title="Eliminar fundamentación">
                        🗑️ Eliminar
                    </button>
                @else
                    <a href="/crearFundamentación?tesis_id={{ $tesis->id }}" 
                       class="btn-agregar-fund" title="Agregar fundamentación">
                        ➕ Agregar Fundamentación
                    </a>
                @endif
            </div>
        </div>
        
        @if($tesis->fundamentacion)
            <div class="fundamentacion-info">
                <div class="datos-grid">
                    <div class="campo-dato">
                        <label>ID Fundamentación:</label>
                        <span>{{ $tesis->fundamentacion->id_fundamentacion }}</span>
                    </div>
                    
                    <div class="campo-dato">
                        <label>Estado de la Fundamentación:</label>
                        @if($tesis->fundamentacion->aprobada)
                            <span class="badge-estado estado-aprobado">Aprobada</span>
                        @elseif($tesis->fundamentacion->desaprobada)
                            <span class="badge-estado estado-desaprobado">Desaprobada</span>
                        @else
                            <span class="badge-estado estado-pendiente">Pendiente</span>
                        @endif
                    </div>
                    
                    <div class="campo-dato">
                        <label>Fecha de Creación:</label>
                        <span>{{ $tesis->fundamentacion->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                    
                    <div class="campo-dato">
                        <label>Total de Versiones:</label>
                        <span>{{ $tesis->fundamentacion->versiones->count() }}</span>
                    </div>
                    
                    @if($tesis->fundamentacion->recomendacion && !empty($tesis->fundamentacion->recomendacion->recomendacion))
                        <div class="campo-dato full-width">
                            <label>Recomendación:</label>
                            <div class="recomendacion-texto">
                                {{ $tesis->fundamentacion->recomendacion->recomendacion }}
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Botones para aprobar/desaprobar/revertir fundamentación -->
                @if($tesis->fundamentacion)
                <div class="botones-estado-fund">
                    <form action="{{ route('aprobarFundamentación') }}" method="POST" class="form-estado">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-estado btn-aprobar" 
                                @if($tesis->fundamentacion->aprobada) disabled @endif
                                title="Aprobar fundamentación">
                            ✅ Aprobar
                        </button>
                    </form>
                    
                    <form action="{{ route('desaprobarFundamentación') }}" method="POST" class="form-estado">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-estado btn-desaprobar"
                                @if($tesis->fundamentacion->desaprobada) disabled @endif
                                title="Desaprobar fundamentación">
                            ❌ Desaprobar
                        </button>
                    </form>
                    
                    <form action="{{ route('revertirFundamentación') }}" method="POST" class="form-estado">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-estado btn-revertir"
                                @if(!$tesis->fundamentacion->aprobada && !$tesis->fundamentacion->desaprobada) disabled @endif
                                title="Revertir a pendiente">
                            ↩️ Revertir
                        </button>
                    </form>
                </div>
                @endif
            </div>
            
            <!-- Versiones de la Fundamentación -->
            @if($tesis->fundamentacion->versiones && $tesis->fundamentacion->versiones->count() > 0)
                <div class="seccion-versiones">
                    <h3>Versiones de la Fundamentación</h3>
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
                                @foreach($tesis->fundamentacion->versiones as $version)
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
                                                {{ Str::limit($version->descripcion, 50) }}
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
                                                
                                                @if(!$tesis->fundamentacion->aprobada && !$tesis->fundamentacion->desaprobada && $tesis->fundamentacion->versiones->count() > 1)
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
                                <span class="numero">{{ $tesis->fundamentacion->versiones->count() }}</span>
                                <span class="texto">Versiones</span>
                            </div>
                            <div class="estadistica">
                                <span class="numero">{{ number_format($tesis->fundamentacion->versiones->sum('tamanio') / 1048576, 2) }}</span>
                                <span class="texto">MB Total</span>
                            </div>
                            <div class="estadistica">
                                <span class="numero">{{ $tesis->fundamentacion->versiones->first()->created_at->format('d/m/Y') }}</span>
                                <span class="texto">Última actualización</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="sin-fundamentacion">
                <div class="icono-sin-fund">📝</div>
                <h3>No hay fundamentación</h3>
                <p>Esta tesis no tiene una fundamentación asociada.</p>
                <a href="/crearFundamentación?tesis_id={{ $tesis->id }}" class="btn-agregar-fund-empty">
                    ➕ Crear Fundamentación
                </a>
            </div>
        @endif
    </div>
    
    <!-- Cortes de tesis (solo si la fundamentación está aprobada) -->
    @if($tesis->fundamentacion && $tesis->fundamentacion->aprobada)
    <div class="seccion-datos">
        <div class="seccion-header">
            <h2>Cortes de Tesis</h2>
            
            <a href="{{ route('crearCorte', ['tesis_id' => $tesis->id]) }}" class="btn-agregar-corte">
                ➕ Agregar Corte
            </a>
        </div>
        
        @if($tesis->cortes && $tesis->cortes->count() > 0)
            <div class="tabla-cortes">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Número de Corte</th>
                            <th>Estado</th>
                            <th>Versiones</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tesis->cortes as $corte)
                            <tr>
                                <td>{{ $corte->idCortes_de_tesis }}</td>
                                <td>
                                    <strong>Corte #{{ $corte->Numero_corte }}</strong>
                                </td>
                                <td>
                                    @if($corte->aprobado)
                                        <span class="badge-estado estado-aprobado">Aprobado</span>
                                    @elseif($corte->desaprobado)
                                        <span class="badge-estado estado-desaprobado">Desaprobado</span>
                                    @else
                                        <span class="badge-estado estado-pendiente">Pendiente</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="tabla-cortes-versiones">
                                        @if($corte->versiones && $corte->versiones->count() > 0)
                                            @foreach($corte->versiones as $version)
                                                <div class="corte-version-item">
                                                    <div class="corte-version-header">
                                                        <span class="corte-version-title">Versión {{ $version->version_numero }}</span>
                                                        <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                                           class="btn-descargar-version" title="Descargar">
                                                            📥
                                                        </a>
                                                    </div>
                                                    <div class="corte-version-body">
                                                        @if($version->Enlace_Github)
                                                            <div>🔗 <a href="{{ $version->Enlace_Github }}" target="_blank" class="enlace-github">GitHub</a></div>
                                                        @endif
                                                        @if($version->descripcion)
                                                            <div>{{ Str::limit($version->descripcion, 100) }}</div>
                                                        @endif
                                                        <div><small>{{ $version->created_at->format('d/m/Y H:i') }}</small></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Sin versiones</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    {{ $corte->created_at->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="acciones-corte">
                                        <a href="{{ route('verCorte', $corte->idCortes_de_tesis) }}" 
                                           class="btn-ver-corte" title="Ver detalles">
                                            👁️
                                        </a>
                                        
                                        @if(!$corte->aprobado && !$corte->desaprobado)
                                            <a href="{{ route('editarCorte', $corte->idCortes_de_tesis) }}" 
                                               class="btn-editar-corte" title="Editar corte">
                                                ✏️
                                            </a>
                                            <button type="button" class="btn-eliminar-corte" 
                                                    onclick="eliminarCorte({{ $corte->idCortes_de_tesis }})"
                                                    title="Eliminar corte">
                                                🗑️
                                            </button>
                                        @endif
                                        
                                        <!-- Botones para aprobar/desaprobar/revertir corte -->
                                        <form action="{{ route('aprobarCorte') }}" method="POST" class="form-accion-corte">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                                            <button type="submit" class="btn-accion-corte btn-aprobar-corte"
                                                    @if($corte->aprobado) disabled @endif
                                                    title="Aprobar corte">
                                                ✅
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('desaprobarCorte') }}" method="POST" class="form-accion-corte">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                                            <button type="submit" class="btn-accion-corte btn-desaprobar-corte"
                                                    @if($corte->desaprobado) disabled @endif
                                                    title="Desaprobar corte">
                                                ❌
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('revertirCorte') }}" method="POST" class="form-accion-corte">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                                            <button type="submit" class="btn-accion-corte btn-revertir-corte"
                                                    @if(!$corte->aprobado && !$corte->desaprobado) disabled @endif
                                                    title="Revertir a pendiente">
                                                ↩️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Estadísticas de cortes -->
            <div class="estadisticas-cortes">
                <div class="estadistica-item">
                    <span class="numero">{{ $tesis->cortes->count() }}</span>
                    <span class="texto">Total de Cortes</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero">{{ $tesis->cortes->filter(function($corte) { return $corte->aprobado !== null; })->count() }}</span>
                    <span class="texto">Aprobados</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero">{{ $tesis->cortes->filter(function($corte) { return $corte->desaprobado !== null; })->count() }}</span>
                    <span class="texto">Desaprobados</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero">{{ $tesis->cortes->filter(function($corte) { return $corte->aprobado === null && $corte->desaprobado === null; })->count() }}</span>
                    <span class="texto">Pendientes</span>
                </div>
                <div class="estadistica-item">
                    <span class="numero">{{ $tesis->cortes->sum(function($corte) { return $corte->versiones->count(); }) }}</span>
                    <span class="texto">Total Versiones</span>
                </div>
            </div>
        @else
            <div class="sin-cortes">
                <div class="icono-sin-corte">📄</div>
                <h3>No hay cortes de tesis</h3>
                <p>Esta tesis no tiene cortes registrados.</p>
                <a href="{{ route('crearCorte', ['tesis_id' => $tesis->id]) }}" class="btn-agregar-corte-empty">
                    ➕ Crear Primer Corte
                </a>
            </div>
        @endif
    </div>
    @elseif($tesis->fundamentacion && !$tesis->fundamentacion->aprobada)
    <div class="seccion-datos">
        <h2>Cortes de Tesis</h2>
        <div class="alerta-fundamentacion-pendiente">
            <div class="icono-alerta">⚠️</div>
            <h3>Fundamentación no aprobada</h3>
            <p>No se pueden crear cortes de tesis hasta que la fundamentación sea aprobada.</p>
            <div class="estado-actual">
                Estado actual: 
                @if($tesis->fundamentacion->desaprobada)
                    <span class="badge-estado estado-desaprobado">Desaprobada</span>
                @else
                    <span class="badge-estado estado-pendiente">Pendiente de aprobación</span>
                @endif
            </div>
        </div>
    </div>
    @endif
    
    <!-- Acciones -->
    <div class="seccion-acciones">
        <h2>Acciones</h2>
        <div class="botones-accion">
            <a href="/editarTesis/{{ $tesis->id }}" class="btn-editar-tesis">
                ✏️ Editar Tesis
            </a>
            
            <button type="button" class="btn-eliminar-tesis" onclick="confirmarEliminacion()">
                🗑️ Eliminar Tesis
            </button>
            
            @if(!$tesis->fundamentacion)
                <a href="/crearFundamentación?tesis_id={{ $tesis->id }}" class="btn-crear-fund">
                    📝 Crear Fundamentación
                </a>
            @endif
            
            @if($tesis->fundamentacion && $tesis->fundamentacion->aprobada)
                <a href="{{ route('crearCorte', ['tesis_id' => $tesis->id]) }}" class="btn-crear-corte">
                    📄 Crear Corte
                </a>
            @endif
        </div>
    </div>
    
    <!-- Botones inferiores -->
    <div class="botones-inferiores">
        <a href="/gestionarTesis" class="btn-volver">← Volver a la lista</a>
    </div>
</div>

<!-- Formularios ocultos para acciones -->
<form id="formEliminarTesis" method="POST" action="/eliminarTesis" style="display: none;">
    @csrf
    <input type="hidden" name="id" value="{{ $tesis->id }}">
</form>

@if($tesis->fundamentacion)
<form id="formEliminarFundamentacion" method="POST" action="/eliminarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
</form>
@endif

<form id="formEliminarCorte" method="POST" action="/eliminarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminarCorte">
</form>

<!-- Formularios ocultos para aprobar/desaprobar/revertir fundamentación -->
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

<script>
// Función para confirmar eliminación de tesis
function confirmarEliminacion() {
    if (confirm('¿Está seguro de que desea eliminar esta tesis?')) {
        document.getElementById('formEliminarTesis').submit();
    }
}

// Función para eliminar fundamentación
function eliminarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta fundamentación?')) {
        document.getElementById('formEliminarFundamentacion').submit();
    }
}

// Función para eliminar corte
function eliminarCorte(id) {
    if (confirm('¿Está seguro de que desea eliminar este corte de tesis?')) {
        document.getElementById('inputIdEliminarCorte').value = id;
        document.getElementById('formEliminarCorte').submit();
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
</script>

@endsection