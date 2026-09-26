@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarTesisHistorico', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Registro Histórico
            </a>

            <a href="{{ route('gestionarTesis') }}" class="btn-crear" style="background:linear-gradient(135deg,#0a3,#030);">
                ← Volver a Tesis Activas
            </a>

            @if (count($historicos) > 0)
                <a href="{{ route('exportarTesisHistoricoCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>📦 Histórico de Tesis</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('gestionarTesisHistorico') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text" name="buscar" placeholder="Buscar por tesis o estudiante..."
                       value="{{ request('buscar') }}" class="input-filtro">

                <select name="filtro_año" class="input-filtro">
                    <option value="">Todos los años</option>
                    @foreach ($años as $a)
                        <option value="{{ $a }}" {{ request('filtro_año') == $a ? 'selected' : '' }}>
                            {{ $a }}
                        </option>
                    @endforeach
                </select>

                <select name="por_pagina" class="input-filtro">
                    <option value="10" {{ request('por_pagina', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('por_pagina', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('por_pagina', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('buscar') || request('filtro_año'))
                    <a href="{{ route('gestionarTesisHistorico') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $historicos->firstItem() ?? 0 }} - {{ $historicos->lastItem() ?? 0 }}
                de {{ $historicos->total() }} registros
                @if(request('buscar') || request('filtro_año')) (filtrados) @endif
            </p>

            <button type="button" id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarTesisHistorico') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariasTesisHistorico') }}" style="display: none;">
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
                        <th>Año</th>
                        <th>Nombre de la Tesis</th>
                        <th>Estudiante</th>
                        <th style="text-align:center;">Fundamentación</th>
                        <th style="text-align:center;">Corte</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($historicos as $h)
                        <tr id="fila_{{ $h->id }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $h->id }}" onchange="actualizarSeleccion()">
                            </td>
                            <td>
                                <span class="badge-rol-detalle"
                                      style="background:linear-gradient(135deg,#78350f,#451a03);">
                                    {{ $h->año }}
                                </span>
                            </td>
                            <td>{{ $h->nombre_tesis }}</td>
                            <td>{{ $h->nombre_estudiante }}</td>
                            <td style="text-align:center;">
                                @if($h->documento_fundamentacion)
                                    <a href="{{ route('descargarFundamentacionHistorico', $h->id) }}"
                                       style="text-decoration:none; font-size:22px;"
                                       title="Descargar fundamentación">📥</a>
                                @else
                                    <span class="sin-informacion">—</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($h->documento_corte)
                                    <a href="{{ route('descargarCorteHistorico', $h->id) }}"
                                       style="text-decoration:none; font-size:22px;"
                                       title="Descargar corte">📥</a>
                                @else
                                    <span class="sin-informacion">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('gestionarTesisHistorico', ['accion' => 'detalles', 'id' => $h->id]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarHistorico({{ $h->id }})">
                                    <a href="{{ route('gestionarTesisHistorico', ['accion' => 'editar', 'id' => $h->id]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="sin-registros">
                                @if(request('buscar') || request('filtro_año'))
                                    No se encontraron registros con los criterios de búsqueda
                                @else
                                    No hay tesis en el histórico
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historicos->hasPages())
            <div class="paginacion">
                {{ $historicos->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<script>
    function eliminarHistorico(id) {
        if (confirm('¿Está seguro de eliminar este registro del histórico?\n\nLos documentos también serán eliminados.')) {
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

        const plural = ids.length === 1 ? 'registro' : 'registros';
        if (!confirm(`¿Está seguro de eliminar ${ids.length} ${plural} del histórico?\n\nLos documentos también serán eliminados.`)) return;

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

    document.querySelectorAll('.form-filtros-usuarios select').forEach(sel => {
        sel.addEventListener('change', function() {
            this.closest('form')?.submit();
        });
    });
</script>

@endsection