@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarFundamentaciones', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Fundamentación
            </a>

            {{-- Botón en ROJO: Estudiantes sin fundamentación --}}
            <a href="/estudiantesAtrasadosFundamentación"
               class="btn-crear"
               title="Ver estudiantes que no han subido su fundamentación de tesis"
               style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
                      border-color: #991b1b;
                      color: #ffffff;">
                🎓 Estudiantes sin fundamentación
            </a>

            @if (count($fundamentaciones) > 0)
                <a href="{{ route('exportarFundamentacionesCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Fundamentación</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('gestionarFundamentaciones') }}" class="form-filtros-usuarios">
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
                    <option value="aprobada" {{ request('filtro_estado') == 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                    <option value="desaprobada" {{ request('filtro_estado') == 'desaprobada' ? 'selected' : '' }}>Desaprobada</option>
                </select>

                <select name="por_pagina" class="input-filtro">
                    <option value="10" {{ request('por_pagina', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('por_pagina', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('por_pagina', 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('por_pagina', 10) == 100 ? 'selected' : '' }}>100</option>
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado'))
                    <a href="{{ route('gestionarFundamentaciones') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $fundamentaciones->firstItem() ?? 0 }} - {{ $fundamentaciones->lastItem() ?? 0 }}
                de {{ $fundamentaciones->total() }} fundamentaciones
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera') || request('filtro_estado')) (filtradas) @endif
            </p>

            <button type="button" id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <!-- Formularios ocultos -->
        <form id="formEliminar" method="POST" action="{{ route('eliminarFundamentación') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariasFundamentaciones') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <form id="formAprobar" method="POST" action="{{ route('aprobarFundamentación') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdAprobar">
        </form>

        <form id="formDesaprobar" method="POST" action="{{ route('desaprobarFundamentación') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdDesaprobar">
        </form>

        <form id="formRevertir" method="POST" action="{{ route('revertirFundamentación') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdRevertir">
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
                        <th>Versiones</th>
                        <th>Estado</th>
                        <th>Aprobar/Desaprobar</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($fundamentaciones as $fundamentacion)
                        <tr id="fila_{{ $fundamentacion->id_fundamentacion }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $fundamentacion->id_fundamentacion }}"
                                       onchange="actualizarSeleccion()">
                            </td>
                            <td>
                                @if($fundamentacion->tesis)
                                    {{ $fundamentacion->tesis->Nombre_trabajo }}
                                @else
                                    <span class="sin-informacion">Trabajo no encontrado</span>
                                @endif
                            </td>
                            <td>
                                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante)
                                    {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }}
                                    {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                                    {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                                @else
                                    <span class="sin-informacion">No encontrado</span>
                                @endif
                            </td>
                            <td>
                                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante &&
                                    $fundamentacion->tesis->estudiante->carrera &&
                                    $fundamentacion->tesis->estudiante->carrera->facultad)
                                    {{ $fundamentacion->tesis->estudiante->carrera->facultad->Nombre_facultad }}
                                @else
                                    <span class="sin-informacion">No especificado</span>
                                @endif
                            </td>
                            <td>
                                @if($fundamentacion->tesis && $fundamentacion->tesis->estudiante &&
                                    $fundamentacion->tesis->estudiante->carrera)
                                    {{ $fundamentacion->tesis->estudiante->carrera->Nombre_carrera }}
                                @else
                                    <span class="sin-informacion">No especificado</span>
                                @endif
                            </td>
                            <td>
                                @if($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                                    <div class="versiones-container">
                                        @foreach($fundamentacion->versiones as $version)
                                            <div class="version-item">
                                                <span class="version-numero">v{{ $version->version_numero }}</span>
                                                <a href="{{ route('ver-documento-version', $version->id) }}"
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
                                @if($fundamentacion->aprobada)
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobada</span>
                                @elseif($fundamentacion->desaprobada)
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobada</span>
                                @else
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                                    <div class="acciones-estado-inline">
                                        <button type="button" class="btn-estado-mini btn-aprobar-mini"
                                                onclick="aprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})"
                                                title="Aprobar">✅</button>
                                        <button type="button" class="btn-estado-mini btn-desaprobar-mini"
                                                onclick="desaprobarFundamentacion({{ $fundamentacion->id_fundamentacion }})"
                                                title="Desaprobar">❌</button>
                                    </div>
                                @else
                                    <button type="button" class="btn-estado-mini btn-revertir-mini"
                                            onclick="revertirFundamentacion({{ $fundamentacion->id_fundamentacion }})"
                                            title="Revertir a pendiente">↩️</button>
                                @endif
                            </td>
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('gestionarFundamentaciones', ['accion' => 'detalles', 'id' => $fundamentacion->id_fundamentacion]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarFundamentacion({{ $fundamentacion->id_fundamentacion }})">
                                    <a href="{{ route('gestionarFundamentaciones', ['accion' => 'editar', 'id' => $fundamentacion->id_fundamentacion]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="sin-registros">
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
        </div>

        @if($fundamentaciones->hasPages())
            <div class="paginacion">
                {{ $fundamentaciones->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<script>
    function eliminarFundamentacion(id) {
        if (confirm('¿Está seguro de que desea eliminar esta fundamentación?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    function aprobarFundamentacion(id) {
        if (confirm('¿Está seguro de que desea aprobar esta fundamentación?')) {
            document.getElementById('inputIdAprobar').value = id;
            document.getElementById('formAprobar').submit();
        }
    }

    function desaprobarFundamentacion(id) {
        if (confirm('¿Está seguro de que desea desaprobar esta fundamentación?')) {
            document.getElementById('inputIdDesaprobar').value = id;
            document.getElementById('formDesaprobar').submit();
        }
    }

    function revertirFundamentacion(id) {
        if (confirm('¿Está seguro de que desea revertir esta fundamentación a pendiente?')) {
            document.getElementById('inputIdRevertir').value = id;
            document.getElementById('formRevertir').submit();
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

        const plural = ids.length === 1 ? 'fundamentación' : 'fundamentaciones';
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