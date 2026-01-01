@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/gestionarTesis/gestionarTesis.css'])


<div class="contenido-principal">
    <div class="contenedor-tesis">
        
        <div class="botones-superiores">
            <a href="/crearTesis" id="crearTesis" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Tesis
            </a>
        </div>
        
        <h1>Gestionar Trabajo de Diploma</h1>
        
        <!-- Barra de herramientas: Búsqueda, Filtros y Crear -->
        <div class="herramientas-tesis">
            <div class="herramientas-contenedor">
                <form method="GET" action="{{ route('gestionarTesis') }}" class="form-herramientas">
                    <div class="herramientas-grid">
                        <!-- Búsqueda -->
                        <div class="herramienta-item">
                            <div class="grupo-busqueda">
                                <input 
                                    type="text" 
                                    name="buscar" 
                                    id="buscar_tesis" 
                                    placeholder="Buscar estudiante o tesis..." 
                                    value="{{ request('buscar') }}"
                                    class="input-busqueda"
                                >
                                <button type="submit" class="btn-buscar" id="btn_buscar" title="Buscar">
                                    <img src="{{ asset('img/buscar.png') }}" alt="Buscar" id="ícono_buscar">
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('gestionarTesis') }}" class="btn-limpiar" title="Limpiar búsqueda">×</a>
                                @endif
                            </div>
                        </div>

                        <!-- Filtro por facultad -->
                        <div class="herramienta-item">
                            <label for="filtro_facultad" class="filtro-label">Facultad:</label>
                            <select name="filtro_facultad" id="filtro_facultad" class="select-filtro">
                                <option value="">Todas</option>
                                @foreach ($facultades as $facultad)
                                    <option value="{{ $facultad->idFacultad }}" {{ request('filtro_facultad') == $facultad->idFacultad ? 'selected' : '' }}>
                                        {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtro por carrera -->
                        <div class="herramienta-item">
                            <label for="filtro_carrera" class="filtro-label">Carrera:</label>
                            <select name="filtro_carrera" id="filtro_carrera" class="select-filtro">
                                <option value="">Todas</option>
                                @foreach ($carreras as $carrera)
                                    <option value="{{ $carrera->id }}" {{ request('filtro_carrera') == $carrera->id ? 'selected' : '' }}>
                                        {{ $carrera->Nombre_carrera }}
                                    </option>
                                @endforeach
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
                            @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera'))
                                <a href="{{ route('gestionarTesis') }}" class="btn-limpiar-todo">Limpiar todo</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Información de resultados -->
        <div class="info-resultados">
            <p>
                Mostrando {{ $trabajos->firstItem() ?? 0 }} - {{ $trabajos->lastItem() ?? 0 }} 
                de {{ $trabajos->total() }} tesis
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera'))
                    (filtrados)
                @endif
            </p>
        </div>

        <!-- Mensajes de éxito o error -->
        @if(session('success'))
            <div class="mensaje-alerta mensaje-exito">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="mensaje-alerta mensaje-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Tabla de tesis -->
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Nombre del trabajo</th>
                        <th>Carrera</th>
                        <th>Facultad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trabajos as $trabajo)
                    <tr id="{{ $trabajo->id }}">
                        <td>
                            @if($trabajo->estudiante)
                                {{ $trabajo->estudiante->Nombre_estudiante }} 
                                {{ $trabajo->estudiante->Apellido1 }} 
                                {{ $trabajo->estudiante->Apellido2 }}
                            @else
                                <span class="sin-informacion">Estudiante no encontrado</span>
                            @endif
                        </td>
                      
                        <td>{{ $trabajo->Nombre_trabajo }}</td>
                        
                        <td>
                            @if($trabajo->estudiante && $trabajo->estudiante->carrera)
                                {{ $trabajo->estudiante->carrera->Nombre_carrera }}
                            @else
                                <span class="sin-informacion">No asignada</span>
                            @endif
                        </td>
                        
                        <td>
                            @if($trabajo->estudiante && $trabajo->estudiante->carrera && $trabajo->estudiante->carrera->facultad)
                                {{ $trabajo->estudiante->carrera->facultad->Siglas }}
                            @else
                                <span class="sin-informacion">No asignada</span>
                            @endif
                        </td>
                        
                        <td style="position: relative">
                            <img src="{{ asset('img/eliminar.jpg') }}" id="imagen_eliminar" class="imagen_botón btn_eliminar" alt="Ícono de eliminar" title="Eliminar" onclick="eliminarTesis({{ $trabajo->id }})">
                            <a href="/editarTesis/{{ $trabajo->id }}"> 
                                <img src="{{ asset('img/editar.jpg') }}" id="imagen_editar" class="imagen_botón" alt="Ícono de editar" title="Editar">
                            </a>
                            <a href="/verTesis/{{ $trabajo->id }}">
                                <img src="{{ asset('img/ver.jpg') }}" id="imagen_ver" class="imagen_botón" alt="Ícono de ver" title="Ver">
                            </a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sin-registros">
                                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera'))
                                    No se encontraron tesis con los criterios de búsqueda
                                @else
                                    No hay tesis registradas
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($trabajos->hasPages())
        <div class="paginacion">
            {{ $trabajos->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
        @endif

        <!-- Formulario oculto para eliminar -->
        <form id="formEliminarTesis" method="POST" action="/eliminarTesis" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>
    </div>
</div>

<script>
// Función para eliminar tesis
function eliminarTesis(id) {
    if (confirm('¿Está seguro de que desea eliminar esta tesis?')) {
        document.getElementById('inputIdEliminar').value = id;
        document.getElementById('formEliminarTesis').submit();
    }
}

// Funcionalidad para filtros
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar filtro de carreras según facultad seleccionada
    const filtroFacultad = document.getElementById('filtro_facultad');
    const filtroCarrera = document.getElementById('filtro_carrera');
    
    // Auto-submit al cambiar filtros
    const filtros = document.querySelectorAll('.select-filtro');
    filtros.forEach(filtro => {
        filtro.addEventListener('change', function() {
            // Si el formulario está listo, se envía automáticamente
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Limpiar búsqueda
    const btnLimpiarBusqueda = document.querySelector('.btn-limpiar');
    if (btnLimpiarBusqueda) {
        btnLimpiarBusqueda.addEventListener('click', function(e) {
            e.preventDefault();
            const inputBusqueda = document.getElementById('buscar_tesis');
            if (inputBusqueda) {
                inputBusqueda.value = '';
                const form = inputBusqueda.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }
});
</script>



@endsection