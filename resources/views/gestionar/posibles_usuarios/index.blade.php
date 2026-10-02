@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarUsuarios') }}" class="btn-crear">
                ← Volver a Usuarios
            </a>
        </div>

        <h1>Posibles Usuarios</h1>
        <p style="color:#000; margin-top:-8px;">
            Solicitudes de registro pendientes de aprobación.
        </p>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('posiblesUsuarios') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text"
                       name="buscar"
                       placeholder="Buscar nombre, email o CI..."
                       value="{{ request('buscar') }}"
                       class="input-filtro">

                <select name="filtro_rol" class="input-filtro">
                    <option value="">Todos los roles solicitados</option>
                    <option value="estudiante" {{ request('filtro_rol') == 'estudiante' ? 'selected' : '' }}>Estudiantes</option>
                    <option value="profesor"   {{ request('filtro_rol') == 'profesor'   ? 'selected' : '' }}>Profesores</option>
                </select>

                <select name="por_pagina" class="input-filtro">
                    <option value="10"  {{ request('por_pagina', 10) == 10  ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('por_pagina', 10) == 25  ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('por_pagina', 10) == 50  ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('buscar') || request('filtro_rol'))
                    <a href="{{ route('posiblesUsuarios') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $posibles->firstItem() ?? 0 }} - {{ $posibles->lastItem() ?? 0 }}
                de {{ $posibles->total() }} solicitudes
                @if(request('buscar') || request('filtro_rol')) (filtradas) @endif
            </p>

            <button type="button"
                    id="btn_rechazar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="rechazarSeleccionados()"
                    style="display: none;">
                Rechazar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        {{-- Formularios ocultos --}}
        <form id="formAceptar" method="POST" action="" style="display: none;">
            @csrf
        </form>

        <form id="formRechazar" method="POST" action="" style="display: none;">
            @csrf
        </form>

        <form id="formRechazarVarios" method="POST"
              action="{{ route('rechazarVariosPosiblesUsuarios') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox"
                                   id="check_todos"
                                   class="checkbox-fila"
                                   onchange="toggleSeleccionarTodos(this)"
                                   title="Seleccionar todos">
                        </th>
                        <th>Solicita</th>
                        <th>Nombre completo</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>CI</th>
                        <th>Fecha</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($posibles as $posible)
                        @php
                            $nombreCompleto = trim(
                                ($posible->nombre_persona ?? '') . ' ' .
                                ($posible->apellido1 ?? '') . ' ' .
                                ($posible->apellido2 ?? '')
                            );
                            $esEstudiante = $posible->rol_solicitado === 'estudiante';
                        @endphp
                        <tr id="fila_{{ $posible->id }}">
                            <td class="col-check">
                                <input type="checkbox"
                                       class="checkbox-fila check-fila"
                                       value="{{ $posible->id }}"
                                       onchange="actualizarSeleccion()">
                            </td>

                            {{-- Rol solicitado --}}
                            <td>
                                @if($esEstudiante)
                                    <span class="badge-rol-detalle" style="font-weight: bold; color:#000;">
                                        🎓 Estudiante
                                    </span>
                                @else
                                    <span class="badge-rol-detalle" style="font-weight: bold;color:#000;">
                                        👨‍🏫 Profesor
                                    </span>
                                @endif
                            </td>

                            <td>{{ $nombreCompleto ?: '—' }}</td>
                            <td>{{ $posible->name }}</td>
                            <td>{{ $posible->email }}</td>
                            <td>{{ $posible->ci ?? '—' }}</td>
                            <td>{{ $posible->created_at->format('d/m/Y H:i') }}</td>

                            {{-- Acciones --}}
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('verPosibleUsuario', $posible->id) }}"
                                       title="Ver detalles"
                                       class="btn-ver-solicitud">
                                        👁 Ver
                                    </a>
                                    <button type="button"
                                            class="btn-asignar-oponente"
                                            onclick="aceptarPosible({{ $posible->id }})">
                                        ✅ Aceptar
                                    </button>
                                    <button type="button"
                                            class="btn-estado-mini btn-desaprobar-mini"
                                            title="Rechazar"
                                            onclick="rechazarPosible({{ $posible->id }})">
                                        ❌
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="sin-registros">
                                @if(request('buscar') || request('filtro_rol'))
                                    No se encontraron solicitudes con los criterios de búsqueda
                                @else
                                    No hay solicitudes de registro pendientes
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($posibles->hasPages())
            <div class="paginacion">
                {{ $posibles->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<script>
    /* ============ ACEPTAR (rol automático) ============ */
    function aceptarPosible(id) {
        if (!confirm('¿Aceptar esta solicitud y crear el usuario? El rol se asignará según la solicitud.')) return;

        const form = document.getElementById('formAceptar');
        form.action = '{{ url("/posibles-usuarios") }}/' + id + '/aceptar';
        form.submit();
    }

    /* ============ RECHAZAR (uno) ============ */
    function rechazarPosible(id) {
        if (!confirm('¿Rechazar esta solicitud? El registro se eliminará permanentemente.')) return;

        const form = document.getElementById('formRechazar');
        form.action = '{{ url("/posibles-usuarios") }}/' + id + '/rechazar';
        form.submit();
    }

    /* ============ SELECCIÓN MÚLTIPLE ============ */
    function toggleSeleccionarTodos(master) {
        document.querySelectorAll('.check-fila').forEach(c => { c.checked = master.checked; });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        const checks = document.querySelectorAll('.check-fila');
        const seleccionados = Array.from(checks).filter(c => c.checked);

        document.getElementById('contador_seleccionados').textContent = seleccionados.length;
        document.getElementById('btn_rechazar_seleccionados').style.display =
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

    /* ============ RECHAZAR VARIOS ============ */
    function rechazarSeleccionados() {
        const ids = Array.from(document.querySelectorAll('.check-fila'))
            .filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) return;

        const plural = ids.length === 1 ? 'solicitud' : 'solicitudes';
        if (!confirm(`¿Rechazar ${ids.length} ${plural}? Se eliminarán permanentemente.`)) return;

        const contenedor = document.getElementById('inputs_ids_varias');
        contenedor.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            contenedor.appendChild(input);
        });

        document.getElementById('formRechazarVarios').submit();
    }
</script>

@endsection