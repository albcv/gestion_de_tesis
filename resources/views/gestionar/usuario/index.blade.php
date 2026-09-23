@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarUsuarios', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Usuario
            </a>

            @if (count($usuarios) > 0)
                <a href="{{ route('exportarUsuariosCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Usuarios</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('gestionarUsuarios') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text" name="buscar" placeholder="Buscar usuario..."
                       value="{{ request('buscar') }}" class="input-filtro">

                <select name="filtro_rol" class="input-filtro">
                    <option value="">Todos los roles</option>
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

                <select name="por_pagina" class="input-filtro">
                    <option value="10" {{ request('por_pagina', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('por_pagina', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('por_pagina', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('buscar') || request('filtro_rol'))
                    <a href="{{ route('gestionarUsuarios') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $usuarios->firstItem() ?? 0 }} - {{ $usuarios->lastItem() ?? 0 }}
                de {{ $usuarios->total() }} usuarios
                @if(request('buscar') || request('filtro_rol')) (filtrados) @endif
            </p>

            <button type="button" id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarUsuario') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariosUsuarios') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" id="check_todos" class="checkbox-fila"
                                   onchange="toggleSeleccionarTodos(this)" title="Seleccionar todos">
                        </th>
                        <th>Nombre completo</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        @php
                            $perfilInfo = 'No hay información';
                            if ($usuario->estudiante) {
                                $perfilInfo = trim($usuario->estudiante->Nombre_estudiante . ' ' .
                                    $usuario->estudiante->Apellido1 . ' ' .
                                    $usuario->estudiante->Apellido2);
                            } elseif ($usuario->profesor) {
                                $perfilInfo = trim($usuario->profesor->Nombre_profesor . ' ' .
                                    $usuario->profesor->Apellido1 . ' ' .
                                    $usuario->profesor->Apellido2);
                            }
                        @endphp
                        <tr id="fila_{{ $usuario->id }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $usuario->id }}" onchange="actualizarSeleccion()">
                            </td>
                            <td>{{ $perfilInfo }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->rol->rol ?? 'Sin rol' }}</td>
                            <td>
                                <div class="acciones-td">
                                     <a href="{{ route('gestionarUsuarios', ['accion' => 'detalles', 'id' => $usuario->id]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarUsuario({{ $usuario->id }})">
                                    <a href="{{ route('gestionarUsuarios', ['accion' => 'editar', 'id' => $usuario->id]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                   
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sin-registros">
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
        </div>

        @if($usuarios->hasPages())
            <div class="paginacion">
                {{ $usuarios->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<script>
    function eliminarUsuario(id) {
        if (confirm('¿Está seguro de que desea eliminar este usuario?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    function toggleSeleccionarTodos(master) {
        document.querySelectorAll('.check-fila').forEach(c => { c.checked = master.checked; });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        const checks = document.querySelectorAll('.check-fila');
        const seleccionados = Array.from(checks).filter(c => c.checked);

        document.getElementById('contador_seleccionados').textContent = seleccionados.length;
        document.getElementById('btn_eliminar_seleccionados').style.display =
            seleccionados.length > 0 ? 'inline-flex' : 'none';

        const master = document.getElementById('check_todos');
        if (master) {
            master.checked = seleccionados.length === checks.length && checks.length > 0;
            master.indeterminate = seleccionados.length > 0 && seleccionados.length < checks.length;
        }

        checks.forEach(c => {
            const fila = c.closest('tr');
            if (fila) fila.classList.toggle('fila-seleccionada', c.checked);
        });
    }

    function eliminarSeleccionados() {
        const ids = Array.from(document.querySelectorAll('.check-fila'))
            .filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) return;

        const plural = ids.length === 1 ? 'usuario' : 'usuarios';
        if (!confirm(`¿Está seguro de eliminar ${ids.length} ${plural}?`)) return;

        const contenedor = document.getElementById('inputs_ids_varias');
        contenedor.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            contenedor.appendChild(input);
        });

        document.getElementById('formEliminarVarias').submit();
    }
</script>

@endsection