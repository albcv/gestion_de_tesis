@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarTesis', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Tesis
            </a>

            @if (count($trabajos) > 0)
                <a href="{{ route('exportarTesisCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Trabajo de Diploma</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('gestionarTesis') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text" name="buscar" placeholder="Buscar estudiante o tesis..."
                       value="{{ request('buscar') }}" class="input-filtro">

                <select name="filtro_facultad" class="input-filtro">
                    <option value="">Todas las facultades</option>
                    @foreach ($facultades as $facultad)
                        <option value="{{ $facultad->idFacultad }}"
                            {{ request('filtro_facultad') == $facultad->idFacultad ? 'selected' : '' }}>
                            {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                        </option>
                    @endforeach
                </select>

                <select name="filtro_carrera" class="input-filtro">
                    <option value="">Todas las carreras</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id }}"
                            {{ request('filtro_carrera') == $carrera->id ? 'selected' : '' }}>
                            {{ $carrera->Nombre_carrera }}
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
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera'))
                    <a href="{{ route('gestionarTesis') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando {{ $trabajos->firstItem() ?? 0 }} - {{ $trabajos->lastItem() ?? 0 }}
                de {{ $trabajos->total() }} tesis
                @if(request('buscar') || request('filtro_facultad') || request('filtro_carrera')) (filtrados) @endif
            </p>

            <div class="acciones-masivas">
                <button type="button" id="btn_eliminar_seleccionados"
                        class="btn-eliminar-seleccionados"
                        onclick="eliminarSeleccionados()"
                        style="display: none;">
                    Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
                </button>

                <button type="button" id="btn_mover_historico"
                        class="btn-mover-historico"
                        onclick="abrirModalHistorico()"
                        style="display: none;">
                    📦 Mover a Histórico (<span id="contador_historico">0</span>)
                </button>
            </div>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarTesis') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariasTesis') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <!-- Formulario oculto para mover a histórico -->
        <form id="formMoverHistorico" method="POST" action="{{ route('moverTesisAHistorico') }}" style="display: none;">
            @csrf
            <input type="hidden" name="año" id="input_año_historico">
            <div id="inputs_historico"></div>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th class="col-check">
                            <input type="checkbox" id="check_todos" class="checkbox-fila"
                                   onchange="toggleSeleccionarTodos(this)" title="Seleccionar todos">
                        </th>
                        <th>Estudiante</th>
                        <th>Nombre del Trabajo</th>
                        <th>Carrera</th>
                        <th>Facultad</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trabajos as $trabajo)
                        <tr id="fila_{{ $trabajo->id }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $trabajo->id }}" onchange="actualizarSeleccion()">
                            </td>
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
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('gestionarTesis', ['accion' => 'detalles', 'id' => $trabajo->id]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarTesis({{ $trabajo->id }})">
                                    <a href="{{ route('gestionarTesis', ['accion' => 'editar', 'id' => $trabajo->id]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sin-registros">
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

        @if($trabajos->hasPages())
            <div class="paginacion">
                {{ $trabajos->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        @endif

    </div>
</div>

<!-- =====================================================
     Modal: Mover al histórico (introducir año)
     ===================================================== -->
<div id="modal_historico" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <h2>📦 Mover al Histórico</h2>
        <p class="modal-descripcion">
            Introduce el año al que pertenecen estas tesis. Se moverán
            <strong id="modal_cantidad">0</strong> registros.
        </p>

        <div class="modal-campo">
            <label for="modal_año">Año *</label>
            <input type="number"
                   id="modal_año"
                   class="modal-input"
                   min="2000"
                   max="2100"
                   placeholder="Ej: {{ date('Y') }}"
                   value="{{ date('Y') }}">
            <small id="modal_error" class="modal-error" style="display: none;"></small>
        </div>

        <div class="modal-advertencia">
            ⚠️ <strong>Acción destructiva.</strong>
            Se eliminarán permanentemente la tesis, la fundamentación, los cortes,
            el estudiante y el usuario asociados. Solo se conservará el documento de la
            última versión de la fundamentación y del último corte.
            <br><br>
            Solo se moverán las tesis con <strong>fundamentación aprobada</strong>.
        </div>

        <div class="modal-acciones">
            <button type="button" class="btn-cancelar-modal" onclick="cerrarModalHistorico()">
                Cancelar
            </button>
            <button type="button" class="btn-confirmar-modal" onclick="confirmarMoverHistorico()">
                Confirmar y Mover
            </button>
        </div>
    </div>
</div>

<script>
    /* ============ ELIMINAR UNA ============ */
    function eliminarTesis(id) {
        if (confirm('¿Está seguro de que desea eliminar esta tesis?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    /* ============ SELECCIÓN MÚLTIPLE ============ */
    function toggleSeleccionarTodos(master) {
        document.querySelectorAll('.check-fila').forEach(c => { c.checked = master.checked; });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        const checks = document.querySelectorAll('.check-fila');
        const seleccionados = Array.from(checks).filter(c => c.checked);

        // Contador y botón eliminar
        document.getElementById('contador_seleccionados').textContent = seleccionados.length;
        document.getElementById('btn_eliminar_seleccionados').style.display =
            seleccionados.length > 0 ? 'inline-flex' : 'none';

        // Contador y botón histórico
        const btnHist = document.getElementById('btn_mover_historico');
        const contHist = document.getElementById('contador_historico');
        if (btnHist && contHist) {
            contHist.textContent = seleccionados.length;
            btnHist.style.display = seleccionados.length > 0 ? 'inline-flex' : 'none';
        }

        // Checkbox maestro
        const master = document.getElementById('check_todos');
        if (master) {
            master.checked = seleccionados.length === checks.length && checks.length > 0;
            master.indeterminate = seleccionados.length > 0 && seleccionados.length < checks.length;
        }

        // Resaltado
        checks.forEach(c => {
            const fila = c.closest('tr');
            if (fila) fila.classList.toggle('fila-seleccionada', c.checked);
        });
    }

    /* ============ ELIMINAR VARIOS ============ */
    function eliminarSeleccionados() {
        const ids = Array.from(document.querySelectorAll('.check-fila'))
            .filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) return;

        const plural = ids.length === 1 ? 'tesis' : 'tesis';
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

    /* ============ MODAL HISTÓRICO ============ */
    function abrirModalHistorico() {
        const ids = Array.from(document.querySelectorAll('.check-fila'))
            .filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) return;

        document.getElementById('modal_cantidad').textContent = ids.length;
        document.getElementById('modal_año').value = new Date().getFullYear();
        document.getElementById('modal_error').style.display = 'none';
        document.getElementById('modal_historico').style.display = 'flex';
    }

    function cerrarModalHistorico() {
        document.getElementById('modal_historico').style.display = 'none';
    }

    function confirmarMoverHistorico() {
        const añoInput = document.getElementById('modal_año');
        const errorEl = document.getElementById('modal_error');
        const año = parseInt(añoInput.value, 10);

        // Validar año
        if (!año || año < 2000 || año > 2100) {
            errorEl.textContent = 'Introduce un año válido entre 2000 y 2100.';
            errorEl.style.display = 'block';
            añoInput.focus();
            return;
        }
        errorEl.style.display = 'none';

        const ids = Array.from(document.querySelectorAll('.check-fila'))
            .filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) {
            cerrarModalHistorico();
            return;
        }

        // Confirmación final
        const ok = confirm(
            `¿Confirmas mover ${ids.length} tesis al histórico del año ${año}?\n\n` +
            `Esta acción es IRREVERSIBLE.`
        );
        if (!ok) return;

        // Rellenar el formulario
        document.getElementById('input_año_historico').value = año;

        const contenedor = document.getElementById('inputs_historico');
        contenedor.innerHTML = '';
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            contenedor.appendChild(input);
        });

        document.getElementById('formMoverHistorico').submit();
    }

    // Cerrar modal con Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('modal_historico');
            if (modal && modal.style.display === 'flex') {
                cerrarModalHistorico();
            }
        }
    });

    // Cerrar modal al hacer clic fuera del contenido
    document.addEventListener('click', function (e) {
        const modal = document.getElementById('modal_historico');
        if (e.target === modal) {
            cerrarModalHistorico();
        }
    });

    /* ============ AUTO-SUBMIT FILTROS ============ */
    document.querySelectorAll('.form-filtros-usuarios select').forEach(sel => {
        sel.addEventListener('change', function() {
            this.closest('form')?.submit();
        });
    });
</script>

@endsection