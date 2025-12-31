@extends('layouts.app')

@section('content')


<link rel="stylesheet" href="css/gestionar/gestionarCortes/gestionarCortes.css">


<!-- Botón Crear Corte -->
<a href="/crearCorte" id="crearCorte" class="btn-crear" title="crear"><span id="botón_crear">+</span> Crear Corte</a>

<h1>Gestionar Cortes de Tesis</h1>

<!-- Barra de herramientas: Búsqueda y Filtros -->
<div class="herramientas-cortes">
    <div class="herramientas-contenedor">
        <form method="GET" action="{{ route('gestionarCortes') }}" class="form-herramientas">
            <div class="herramientas-grid">
                <!-- Búsqueda -->
                <div class="herramienta-item">
                    <div class="grupo-busqueda">
                        <input 
                            type="text" 
                            name="buscar" 
                            id="buscar_corte" 
                            placeholder="Buscar tesis o estudiante..." 
                            value="{{ request('buscar') }}"
                            class="input-busqueda"
                        >
                        <button type="submit" class="btn-buscar" id="btn_buscar" title="Buscar">
                            <img src="img/buscar.png" alt="Buscar" id="ícono_buscar">
                        </button>
                        @if(request('buscar'))
                            <a href="{{ route('gestionarCortes') }}" class="btn-limpiar" title="Limpiar búsqueda">×</a>
                        @endif
                    </div>
                </div>

                <!-- Filtro por facultad -->
                <div class="herramienta-item">
                    <label for="filtro_facultad" class="filtro-label">Facultad:</label>
                    <select name="filtro_facultad" id="filtro_facultad" class="select-filtro">
                        <option value="">Todas</option>
                        @foreach($facultades as $facultad)
                            <option value="{{ $facultad->id }}" {{ request('filtro_facultad') == $facultad->id ? 'selected' : '' }}>
                                {{ $facultad->nombre ?? $facultad->Nombre_facultad }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por carrera -->
                <div class="herramienta-item">
                    <label for="filtro_carrera" class="filtro-label">Carrera:</label>
                    <select name="filtro_carrera" id="filtro_carrera" class="select-filtro">
                        <option value="">Todas</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera->id }}" {{ request('filtro_carrera') == $carrera->id ? 'selected' : '' }}>
                                {{ $carrera->nombre ?? $carrera->Nombre_carrera }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro por estado -->
                <div class="herramienta-item">
                    <label for="filtro_estado" class="filtro-label">Estado:</label>
                    <select name="filtro_estado" id="filtro_estado" class="select-filtro">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('filtro_estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="aprobado" {{ request('filtro_estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                        <option value="desaprobado" {{ request('filtro_estado') == 'desaprobado' ? 'selected' : '' }}>Desaprobado</option>
                    </select>
                </div>

                <!-- Filtro por número de corte -->
                <div class="herramienta-item">
                    <label for="filtro_numero_corte" class="filtro-label">N° Corte:</label>
                    <select name="filtro_numero_corte" id="filtro_numero_corte" class="select-filtro">
                        <option value="">Todos</option>
                        <option value="1" {{ request('filtro_numero_corte') == '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ request('filtro_numero_corte') == '2' ? 'selected' : '' }}>2</option>
                        <option value="3" {{ request('filtro_numero_corte') == '3' ? 'selected' : '' }}>3</option>
                        <option value="4" {{ request('filtro_numero_corte') == '4' ? 'selected' : '' }}>4</option>
                    </select>
                </div>

                <!-- Cantidad por página -->
                <div class="herramienta-item">
                    <label for="por_pagina" class="filtro-label">Mostrar:</label>
                    <select name="por_pagina" id="por_pagina" class="select-filtro">
                        <option value="10" {{ request('por_pagina', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('por_pagina', 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('por_pagina', 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Botón de aplicar filtros -->
                <div class="herramienta-item">
                    <button type="submit" class="btn-aplicar">Aplicar</button>
                    @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado') || request('filtro_numero_corte'))
                        <a href="{{ route('gestionarCortes') }}" class="btn-limpiar-todo">Limpiar todo</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Información de resultados -->
<div class="info-resultados">
    <p>
        Mostrando {{ $cortes->firstItem() ?? 0 }} - {{ $cortes->lastItem() ?? 0 }} 
        de {{ $cortes->total() }} cortes
        @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado') || request('filtro_numero_corte'))
            (filtrados)
        @endif
    </p>
</div>

<!-- Formularios ocultos para eliminar, aprobar, desaprobar y revertir -->
<form id="formEliminar" method="POST" action="/eliminarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

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

<!-- Tabla de cortes -->
<table>
    <thead>
        <tr>
            <th>Trabajo de diploma</th>
            <th>Estudiante</th>
            <th>Facultad</th>
            <th>Carrera</th>
            <th>N° Corte</th>
            <th>Versiones</th>
            <th>Estado</th>
            <th>Aprobar/Desaprobar</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($cortes as $corte)
        <tr id="{{ $corte->idCortes_de_tesis }}">
            <td>
                @if($corte->tesis)
                    {{ $corte->tesis->Nombre_trabajo }}
                @else
                    <span style="color: #666;">Trabajo no encontrado</span>
                @endif
            </td>
            <td>
                @if($corte->tesis && $corte->tesis->estudiante)
                    {{ $corte->tesis->estudiante->Nombre_estudiante }} 
                    {{ $corte->tesis->estudiante->Apellido1 }}
                    {{ $corte->tesis->estudiante->Apellido2 }}
                @else
                    <span style="color: #666;">Estudiante no encontrado</span>
                @endif
            </td>
            
            <!-- FACULTAD -->
            <td>
                @if($corte->tesis && $corte->tesis->estudiante && 
                    $corte->tesis->estudiante->carrera &&
                    $corte->tesis->estudiante->carrera->facultad)
                    {{ $corte->tesis->estudiante->carrera->facultad->Nombre_facultad }}
                @else
                    <span style="color: #666;">No especificado</span>
                @endif
            </td>
            <!-- CARRERA -->
            <td>
                @if($corte->tesis && $corte->tesis->estudiante && 
                    $corte->tesis->estudiante->carrera)
                    {{ $corte->tesis->estudiante->carrera->Nombre_carrera }}
                @else
                    <span style="color: #666;">No especificado</span>
                @endif
            </td>
            
            <td>{{ $corte->Numero_corte }}</td>
            
            <td>
                @if($corte->versiones && $corte->versiones->count() > 0)
                    <div class="versiones-container">
                        @foreach($corte->versiones as $version)
                            <div class="version-item">
                                <div class="version-info">
                                    <span class="version-numero" style="color:#00f">Versión {{ $version->version_numero }}</span>
                                    @if($version->Enlace_Github)
                                        <a style="color:#0366d6; font-size:12px;" href="{{$version->Enlace_Github}}">🔗 GitHub</a>
                                    @endif
                                </div>
                                <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                   class="btn-descargar-version" 
                                   title="Descargar versión {{ $version->version_numero }}" style="font-size: 30px;">
                                  📥
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span style="color: #666; font-style: italic;">Sin versiones</span>
                @endif
            </td>
            
            <td>
                @if($corte->aprobado)
                    <span style="color: green; font-weight: bold;">Aprobado</span>
                @elseif($corte->desaprobado)
                    <span style="color: red; font-weight: bold;">Desaprobado</span>
                @else
                    <span style="color: orange; font-weight: bold;">Pendiente</span>
                @endif
            </td>
            
            <!-- COLUMNA APROBAR/DESAPROBAR -->
            <td>
                @if(!$corte->aprobado && !$corte->desaprobado)
                    <!-- Botón Aprobar -->
                    <button type="button" class="btn-aprobar" onclick="aprobarCorte({{ $corte->idCortes_de_tesis }})" 
                            title="Aprobar corte" >Aprobar
                    </button>
                    
                    <!-- Botón Desaprobar -->
                    <button type="button" class="btn-desaprobar" onclick="desaprobarCorte({{ $corte->idCortes_de_tesis }})" 
                            title="Desaprobar corte">Desaprobar
                    </button>
                @else
                    <!-- Botón Revertir -->
                    <button type="button" class="btn-revertir" onclick="revertirCorte({{ $corte->idCortes_de_tesis }})" 
                            title="Revertir a pendiente" >Revertir a pendiente
                    </button>
                @endif
            </td>
        
            <!-- COLUMNA ACCIONES (editar/eliminar) -->
            <td id="acciones">
                <!-- Botones de acción -->
                <div style="display: flex; gap: 5px; align-items: center;">

                    <!-- Botón Ver -->
                    <a href="/cortes/ver/{{ $corte->idCortes_de_tesis }}" title="Ver detalles">
                        <img src="img/ver.jpg" class="imagen_botón" id="imagen_ver" alt="Ícono de ver" style="width: 24px; height: 24px;">
                    </a>

                    <!-- Botón Editar -->
                    <a href="/editarCorte/{{ $corte->idCortes_de_tesis }}" title="Editar corte">
                        <img src="img/editar.jpg" class="imagen_botón" id="imagen_editar" alt="Ícono de editar" style="width: 24px; height: 24px;">
                    </a>
                    
                    <!-- Botón Eliminar -->
                    <img src="img/eliminar.jpg" class="imagen_botón" id="imagen_eliminar" 
                         alt="Ícono de eliminar" title="Eliminar corte" style="width: 24px; height: 24px; cursor: pointer;"
                         onclick="eliminarCorte({{ $corte->idCortes_de_tesis }})">
                </div>
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="9" style="border: none; font-size: 18px; color: #000; text-align: center; padding: 20px;">
                    @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado') || request('filtro_numero_corte'))
                        No se encontraron cortes con los criterios de búsqueda
                    @else
                        No hay cortes registrados
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
@if($cortes->hasPages())
<div class="paginacion">
    {{ $cortes->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
@endif

<!-- JavaScript -->
<script>
// Función para eliminar corte
function eliminarCorte(id) {
    if (confirm('¿Está seguro de que desea eliminar este corte?')) {
        document.getElementById('inputIdEliminar').value = id;
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