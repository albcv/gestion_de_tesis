@extends('layouts.app')

@section('content')


@vite(['resources/css/gestionar/gestionarFundamentación/gestionarFundamentaciones.css'])


<!-- Botón Crear Fundamentación -->
<a href="/crearFundamentación" id="crearFundamentación" class="btn-crear" title="crear"><span id="ícono_crear">+</span> Crear Fundamentación</a>

<h1>Gestionar fundamentación</h1>

<!-- Barra de herramientas: Búsqueda y Filtros -->
<div class="herramientas-fundamentaciones">
    <div class="herramientas-contenedor">
        <form method="GET" action="{{ route('gestionarFundamentaciones') }}" class="form-herramientas">
            <div class="herramientas-grid">
                <!-- Búsqueda -->
                <div class="herramienta-item">
                    <div class="grupo-busqueda">
                        <input 
                            type="text" 
                            name="buscar" 
                            id="buscar_fundamentacion" 
                            placeholder="Buscar tesis o estudiante..." 
                            value="{{ request('buscar') }}"
                            class="input-busqueda"
                        >
                        <button type="submit" class="btn-buscar" id="btn_buscar" title="Buscar">
                            <img src="img/buscar.png" alt="Buscar" id="ícono_buscar">
                        </button>
                        @if(request('buscar'))
                            <a href="{{ route('gestionarFundamentaciones') }}" class="btn-limpiar" title="Limpiar búsqueda">×</a>
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
                        <option value="aprobada" {{ request('filtro_estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                        <option value="desaprobada" {{ request('filtro_estado') == 'desaprobada' ? 'selected' : '' }}>Desaprobada</option>
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
                    @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado'))
                        <a href="{{ route('gestionarFundamentaciones') }}" class="btn-limpiar-todo">Limpiar todo</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Información de resultados -->
<div class="info-resultados">
    <p>
        Mostrando {{ $fundamentaciones->firstItem() ?? 0 }} - {{ $fundamentaciones->lastItem() ?? 0 }} 
        de {{ $fundamentaciones->total() }} fundamentaciones
        @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado'))
            (filtradas)
        @endif
    </p>
</div>

<!-- Formularios ocultos para eliminar, aprobar, desaprobar y revertir -->
<form id="formEliminar" method="POST" action="/eliminarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<form id="formAprobar" method="POST" action="/aprobarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdAprobar">
</form>

<form id="formDesaprobar" method="POST" action="/desaprobarFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdDesaprobar">
</form>

<form id="formRevertir" method="POST" action="/revertirFundamentación" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdRevertir">
</form>

<!-- Tabla de fundamentaciones -->
<table>
    <thead>
        <tr>
            <th>Trabajo de diploma</th>
            <th>Estudiante</th>
            <th>Facultad</th>
            <th>Carrera</th>
            <th>Documento</th>
            <th>Estado</th>
            <th>Aprobar/Desaprobar</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($fundamentaciones as $fundamentacion)
        <tr id="{{ $fundamentacion->id_fundamentacion }}">
            <td>
                @if($fundamentacion->tesis)
                    {{ $fundamentacion->tesis->Nombre_trabajo }}
                @else
                    <span style="color: #000;">Trabajo no encontrado</span>
                @endif
            </td>
            <td>
                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante)
                    {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                    {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                    {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                @else
                    <span style="color: #000;">Estudiante no encontrado</span>
                @endif
            </td>
            
            <td>
                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante && 
                    $fundamentacion->tesis->estudiante->carrera &&
                    $fundamentacion->tesis->estudiante->carrera->facultad)
                    {{ $fundamentacion->tesis->estudiante->carrera->facultad->Nombre_facultad }}
                @else
                    <span style="color: #000;">No especificado</span>
                @endif
            </td>
            <td>
                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante && 
                    $fundamentacion->tesis->estudiante->carrera)
                    {{ $fundamentacion->tesis->estudiante->carrera->Nombre_carrera }}
                @else
                    <span style="color: #000;">No especificado</span>
                @endif
            </td>
            
            <td>
                @if($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                    <div class="versiones-container">
                        @foreach($fundamentacion->versiones as $version)
                            <div class="version-item">
                                <div class="version-info">
                                    <span class="version-numero" style="color:#00f">Versión {{ $version->version_numero }}</span>
                                </div>
                                <a href="{{ route('ver-documento-version', $version->id) }}" 
                                   class="btn-descargar-version" 
                                   title="Descargar versión {{ $version->version_numero }}" style="font-size: 30px;">
                                  📥
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span style="color: #000; font-style: italic;">Sin versiones</span>
                @endif
            </td>
            
            <td>
                @if($fundamentacion->aprobada)
                    <span style="color: green; font-weight: bold;">Aprobada</span>
                @elseif($fundamentacion->desaprobada)
                    <span style="color: red; font-weight: bold;">Desaprobada</span>
                @else
                    <span style="color: orange; font-weight: bold;">Pendiente</span>
                @endif
            </td>
            
            <!-- COLUMNA APROBAR/DESAPROBAR -->
            <td>
                @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                    <!-- Botón Aprobar -->
                    <button type="button" class="btn-aprobar" onclick="aprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})" 
                            title="Aprobar fundamentación" >Aprobar
                    </button>
                    
                    <!-- Botón Desaprobar -->
                    <button type="button" class="btn-desaprobar" onclick="desaprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})" 
                            title="Desaprobar fundamentación">Desaprobar
                    </button>
                @else
                    <!-- Botón Revertir -->
                    <button type="button" class="btn-revertir" onclick="revertirFundamentacion({{ $fundamentacion->id_fundamentacion }})" 
                            title="Revertir a pendiente" >Revertir a pendiente
                    </button>
                @endif
            </td>
        
            <!-- COLUMNA ACCIONES (editar/eliminar) -->
            <td style="width: 120px" id="acciones">
                <!-- Botones de acción -->
                <div style="display: flex; gap: 5px; align-items: center;">

        <a href="/verFundamentación/{{ $fundamentacion->id_fundamentacion }}" 
            title="Ver detalles" style="color: #000; text-decoration: none; font-weight: 500;">
              <img src="img/ver.jpg" class="imagen_botón" id="imagen_ver" alt="Ícono de editar" style="width: 24px; height: 24px;">
        </a>

                    <!-- Botón Editar -->
                    <a href="/editarFundamentación/{{ $fundamentacion->id_fundamentacion }}" title="Editar fundamentación">
                        <img src="img/editar.jpg" class="imagen_botón" id="imagen_editar" alt="Ícono de editar" style="width: 24px; height: 24px;">
                    </a>
                    
                    <!-- Botón Eliminar -->
                    <img src="img/eliminar.jpg" class="imagen_botón" id="imagen_eliminar" 
                        alt="Ícono de eliminar" title="Eliminar fundamentación" style="width: 24px; height: 24px; cursor: pointer;"
                        onclick="eliminarFundamentacion({{ $fundamentacion->id_fundamentacion }})">
                </div>
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="8" style="border: none; font-size: 18px; color: #000; text-align: center; padding: 20px;">
                    @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado'))
                        No se encontraron fundamentaciones con los criterios de búsqueda
                    @else
                        No hay fundamentaciones registradas
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
@if($fundamentaciones->hasPages())
<div class="paginacion">
    {{ $fundamentaciones->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
@endif

<!-- JavaScript -->
<script>
// Función para eliminar fundamentación
function eliminarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea eliminar esta fundamentación?')) {
        document.getElementById('inputIdEliminar').value = id;
        document.getElementById('formEliminar').submit();
    }
}

// Función para aprobar fundamentación
function aprobarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea aprobar esta fundamentación?')) {
        document.getElementById('inputIdAprobar').value = id;
        document.getElementById('formAprobar').submit();
    }
}

// Función para desaprobar fundamentación
function desaprobarFundamentacion(id) {
    if (confirm('¿Está seguro de que desea desaprobar esta fundamentación?')) {
        document.getElementById('inputIdDesaprobar').value = id;
        document.getElementById('formDesaprobar').submit();
    }
}

// Función para revertir fundamentación
function revertirFundamentacion(id) {
    if (confirm('¿Está seguro de que desea revertir esta fundamentación a estado pendiente?')) {
        document.getElementById('inputIdRevertir').value = id;
        document.getElementById('formRevertir').submit();
    }
}
</script>

@endsection