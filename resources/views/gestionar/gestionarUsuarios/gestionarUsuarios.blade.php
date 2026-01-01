@extends('layouts.app')

@section('content')

@vite(['resources/css/formulario.css'])
@vite(['resources/css/gestionar/gestionarUsuarios/gestionarUsuarios.css'])
@vite(['resources/js/gestionarUsuarios/eliminarUsuario.js'])
@vite(['resources/js/fila_seleccionada.js'])


<a href="/crearUsuario" id="crearUsuario" class="btn-crear"><span id="ícono_crear">+</span> Crear Usuario</a>

<h1>Gestionar Usuarios</h1>

<!-- Barra de herramientas: Búsqueda, Filtros y Crear -->
<div class="herramientas-usuarios">
    <!-- Formulario de búsqueda y filtros combinados -->
    <div class="herramientas-contenedor">
        <form method="GET" action="{{ route('gestionarUsuarios') }}" class="form-herramientas">
            <div class="herramientas-grid">
                <!-- Búsqueda -->
                <div class="herramienta-item">
                    <div class="grupo-busqueda">
                        <input 
                            type="text" 
                            name="buscar" 
                            id="buscar_usuario" 
                            placeholder="Buscar usuario..." 
                            value="{{ request('buscar') }}"
                            class="input-busqueda"
                        >
                        <button type="submit" class="btn-buscar" id="btn_buscar" title="Buscar">
                            <img src="img/buscar.png" alt="Buscar" id="ícono_buscar">
                        </button>
                        @if(request('buscar'))
                            <a href="{{ route('gestionarUsuarios') }}" class="btn-limpiar" title="Limpiar búsqueda">×</a>
                        @endif
                    </div>
                </div>

                <!-- Filtro por rol -->
                <div class="herramienta-item">
                    <label for="filtro_rol" class="filtro-label">Rol:</label>
                    <select name="filtro_rol" id="filtro_rol" class="select-filtro">
                        <option value="">Todos</option>
                        <option value="estudiante" {{ request('filtro_rol') == 'estudiante' ? 'selected' : '' }}>Estudiantes</option>
                        <option value="profesor" {{ request('filtro_rol') == 'profesor' ? 'selected' : '' }}>Profesores</option>
                        @foreach ($roles as $rol)
                            @if(!in_array(strtolower($rol->rol), ['estudiante', 'profesor']))
                                <option value="{{ $rol->id }}" {{ request('filtro_rol') == $rol->id ? 'selected' : '' }}>
                                    {{ $rol->rol }}
                                </option>
                            @endif
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
                    @if(request('buscar') || request('filtro_rol'))
                        <a href="{{ route('gestionarUsuarios') }}" class="btn-limpiar-todo">Limpiar todo</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
    


</div>

<!-- Información de resultados -->
<div class="info-resultados">
    <p>
        Mostrando {{ $usuarios->firstItem() ?? 0 }} - {{ $usuarios->lastItem() ?? 0 }} 
        de {{ $usuarios->total() }} usuarios
        @if(request('buscar') || request('filtro_rol'))
            (filtrados)
        @endif
    </p>
</div>

 

<!-- Tabla de usuarios -->
<table>
    <thead>
        <tr>
            <th>Nombre completo</th>
            <th>Usuario</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($usuarios as $usuario)
        <tr id="{{ $usuario->id }}">
            <td>
                @php
                    $perfilInfo = '';
                    if ($usuario->estudiante) {
                        $perfilInfo = $usuario->estudiante->Nombre_estudiante . " " . $usuario->estudiante->Apellido1." " .$usuario->estudiante->Apellido2;
                    } elseif ($usuario->profesor) {
                        $perfilInfo = $usuario->profesor->Nombre_profesor . " " . $usuario->profesor->Apellido1." " .$usuario->profesor->Apellido2;
                    } else {
                        $perfilInfo = "No hay información";
                    }
                @endphp
                {{ $perfilInfo }}
            </td>

            <td>{{$usuario->name}}</td>
            <td>{{$usuario->email}}</td>
            <td>{{$usuario->rol->rol}}</td>

            <td>
                <img src="img\eliminar.jpg" id="imagen_eliminar" class="imagen_botón btn_eliminar" alt="Ícono de eliminar" title="Eliminar">
               <a href="/editarUsuario/{{ $usuario->id }}"> 
                <img src="img/editar.jpg" id="imagen_editar" class="imagen_botón" alt="Ícono de editar" title="Editar">
               </a>
                <a href="/verUsuario/{{ $usuario->id }}">
                    <img src="img\ver.jpg" id="imagen_ver" class="imagen_botón" alt="Ícono de ver" title="Ver">
                </a>
            </td>
        </tr>
        @empty
            <tr>
                <td colspan="5" style="border: none; font-size: 18px; color: #000; text-align: center; padding: 20px;">
                    @if(request('buscar') || request('filtro_rol'))
                        No se encontraron usuarios con los criterios de búsqueda
                    @else
                        No hay usuarios registrados
                    @endif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<!-- Paginación -->
@if($usuarios->hasPages())
<div class="paginacion">
    {{ $usuarios->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
@endif

<form id="formEliminar" method="POST" action="/eliminarUsuario" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

@endsection