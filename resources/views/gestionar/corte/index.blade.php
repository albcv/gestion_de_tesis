@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarCortes', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Corte
            </a>

            @if (count($cortes) > 0)
                <a href="{{ route('exportarCortesCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Cortes de Tesis</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('gestionarCortes') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text" name="buscar" placeholder="Buscar tesis o estudiante..."
                       value="{{ request('buscar') }}" class="input-filtro">

                <select name="filtro_facultad" class="input-filtro">
                    <option value="">Todas las facultades</option>
                    @foreach($facultades as $facultad)
                        <option value="{{ $facultad->idFacultad }}"
                            {{ request('filtro_facultad') == $facultad->idFacultad ? 'selected' : '' }}>
                            {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                        </option>
                    @endforeach
                </select>

                <select name="filtro_carrera" class="input-filtro">
                    <option value="">Todas las carreras</option>
                    @foreach($carreras as $carrera)
                        <option value="{{ $carrera->id }}"
                            {{ request('filtro_carrera') == $carrera->id ? 'selected' : '' }}>
                            {{ $carrera->Nombre_carrera }}
                        </option>
                    @endforeach
                </select>

                <select name="filtro_estado" class="input-filtro">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('filtro_estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="aprobado" {{ request('filtro_estado') == 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option value="desaprobado" {{ request('filtro_estado') == 'desaprobado' ? 'selected' : '' }}>Desaprobado</option>
                </select>

                <select name="filtro_numero_corte" class="input-filtro">
                    <option value="">Todos los cortes</option>
                    <option value="1" {{ request('filtro_numero_corte') == '1' ? 'selected' : '' }}>Corte 1</option>
                    <option value="2" {{ request('filtro_numero_corte') == '2' ? 'selected' : '' }}>Corte 2</option>
                    <option value="3" {{ request('filtro_numero_corte') == '3' ? 'selected' : '' }}>Corte 3</option>
                    <option value="4" {{ request('filtro_numero_corte') == '4' ? 'selected' : '' }}>Corte 4</option>
                </select>

                <select name="por_pagina" class="input-filtro">
                    <option value="10" {{ request('por_pagina', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('por_pagina', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('por_pagina', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado') || request('filtro_numero_corte'))
                    <a href="{{ route('gestionarCortes') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $cortes->firstItem() ?? 0 }} - {{ $cortes->lastItem() ?? 0 }}
                de {{ $cortes->total() }} cortes
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado') || request('filtro_numero_corte')) (filtrados) @endif
            </p>

            <button type="button" id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarCorte') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariosCortes') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <form id="formAprobarCorte" method="POST" action="{{ route('aprobarCorte') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdAprobarCorte">
        </form>

        <form id="formDesaprobarCorte" method="POST" action="{{ route('desaprobarCorte') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdDesaprobarCorte">
        </form>

        <form id="formRevertirCorte" method="POST" action="{{ route('revertirCorte') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdRevertirCorte">
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" id="check_todos" class="checkbox-fila"
                                   onchange="toggleSeleccionarTodos(this)" title="Seleccionar todos">
                        </th>
                        <th>Trabajo de Diploma</th>
                        <th>Estudiante</th>
                        <th>Facultad</th>
                        <th>Carrera</th>
                        <th>N° Corte</th>
                        <th>Versiones</th>
                        <th>Estado</th>
                        <th>Aprobar/Desaprobar</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cortes as $corte)
                        <tr id="fila_{{ $corte->idCortes_de_tesis }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $corte->idCortes_de_tesis }}"
                                       onchange="actualizarSeleccion()">
                            </td>
                            <td>
                                @if($corte->tesis)
                                    {{ $corte->tesis->Nombre_trabajo }}
                                @else
                                    <span class="sin-informacion">Trabajo no encontrado</span>
                                @endif
                            </td>
                            <td>
                                @if($corte->tesis && $corte->tesis->estudiante)
                                    {{ $corte->tesis->estudiante->Nombre_estudiante }}
                                    {{ $corte->tesis->estudiante->Apellido1 }}
                                    {{ $corte->tesis->estudiante->Apellido2 }}
                                @else
                                    <span class="sin-informacion">No encontrado</span>
                                @endif
                            </td>
                            <td>
                                @if($corte->tesis && $corte->tesis->estudiante && $corte->tesis->estudiante->carrera && $corte->tesis->estudiante->carrera->facultad)
                                    {{ $corte->tesis->estudiante->carrera->facultad->Nombre_facultad }}
                                @else
                                    <span class="sin-informacion">No especificado</span>
                                @endif
                            </td>
                            <td>
                                @if($corte->tesis && $corte->tesis->estudiante && $corte->tesis->estudiante->carrera)
                                    {{ $corte->tesis->estudiante->carrera->Nombre_carrera }}
                                @else
                                    <span class="sin-informacion">No especificado</span>
                                @endif
                            </td>
                            <td>
                                <strong>Corte {{ $corte->Numero_corte }}</strong>
                            </td>
                            <td>
                                @if($corte->versiones && $corte->versiones->count() > 0)
                                    <div class="versiones-container">
                                        @foreach($corte->versiones as $version)
                                            <div class="version-item">
                                                <span class="version-numero">v{{ $version->version_numero }}</span>
                                                <a href="{{ route('ver-documento-version-corte', $version->id) }}"
                                                   class="btn-descargar-version"
                                                   title="Descargar versión {{ $version->version_numero }}">📥</a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="sin-informacion">Sin versiones</span>
                                @endif
                            </td>
                            <td>
                                @if($corte->aprobado)
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobado</span>
                                @elseif($corte->desaprobado)
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobado</span>
                                @else
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if(!$corte->aprobado && !$corte->desaprobado)
                                    <div class="acciones-estado-inline">
                                        <button type="button" class="btn-estado-mini btn-aprobar-mini"
                                                onclick="aprobarCorte({{ $corte->idCortes_de_tesis }})"
                                                title="Aprobar">✅</button>
                                        <button type="button" class="btn-estado-mini btn-desaprobar-mini"
                                                onclick="desaprobarCorte({{ $corte->idCortes_de_tesis }})"
                                                title="Desaprobar">❌</button>
                                    </div>
                                @else
                                    <button type="button" class="btn-estado-mini btn-revertir-mini"
                                            onclick="revertirCorte({{ $corte->idCortes_de_tesis }})"
                                            title="Revertir a pendiente">↩️</button>
                                @endif
                            </td>
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('gestionarCortes', ['accion' => 'detalles', 'id' => $corte->idCortes_de_tesis]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarCorte({{ $corte->idCortes_de_tesis }})">
                                    <a href="{{ route('gestionarCortes', ['accion' => 'editar', 'id' => $corte->idCortes_de_tesis]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="sin-registros">
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
        </div>

        @if($cortes->hasPages())
            <div class="paginacion">
                {{ $cortes->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<script>
    function eliminarCorte(id) {
        if (confirm('¿Está seguro de que desea eliminar este corte?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    function aprobarCorte(id) {
        if (confirm('¿Está seguro de que desea aprobar este corte?')) {
            document.getElementById('inputIdAprobarCorte').value = id;
            document.getElementById('formAprobarCorte').submit();
        }
    }

    function desaprobarCorte(id) {
        if (confirm('¿Está seguro de que desea desaprobar este corte?')) {
            document.getElementById('inputIdDesaprobarCorte').value = id;
            document.getElementById('formDesaprobarCorte').submit();
        }
    }

    function revertirCorte(id) {
        if (confirm('¿Está seguro de que desea revertir este corte a pendiente?')) {
            document.getElementById('inputIdRevertirCorte').value = id;
            document.getElementById('formRevertirCorte').submit();
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

        const plural = ids.length === 1 ? 'corte' : 'cortes';
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

    document.querySelectorAll('.form-filtros-usuarios select').forEach(sel => {
        sel.addEventListener('change', function() {
            this.closest('form')?.submit();
        });
    });
</script>

@endsection