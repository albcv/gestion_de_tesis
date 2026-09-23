@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <!-- Acciones superiores: crear + exportar CSV -->
        <div class="botones-superiores">
            <a href="{{ route('gestionarModalidad', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Modalidad
            </a>

            @if (count($modalidades) > 0)
                <a href="{{ route('exportarModalidadesCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Modalidad</h1>

        @if (session('error'))
            <div class="alerta alerta-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Información de resultados + acción bulk -->
        <div class="info-resultados">
            <p>Total de modalidades: <strong>{{ count($modalidades) }}</strong></p>

            <button type="button"
                    id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <!-- Formulario oculto para eliminar una sola -->
        <form id="formEliminar" method="POST" action="{{ route('eliminarModalidad') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <!-- Formulario oculto para eliminar varias -->
        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariasModalidades') }}" style="display: none;">
            @csrf
            <div id="inputs_ids_varias"></div>
        </form>

        <!-- Tabla de modalidades -->
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
                        <th>Nombre</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($modalidades as $modalidad)
                        <tr id="fila_{{ $modalidad->idModalidad }}">
                            <td class="col-check">
                                <input type="checkbox"
                                       class="checkbox-fila check-fila"
                                       value="{{ $modalidad->idModalidad }}"
                                       onchange="actualizarSeleccion()">
                            </td>
                            <td>{{ $modalidad->Nombre_modalidad }}</td>
                            <td>
                                <div class="acciones-td">
                                    <img src="{{ asset('img/eliminar.jpg') }}"
                                         class="imagen_botón"
                                         alt="Eliminar"
                                         title="Eliminar"
                                         onclick="eliminarModalidad({{ $modalidad->idModalidad }})">
                                    <a href="{{ route('gestionarModalidad', ['accion' => 'editar', 'id' => $modalidad->idModalidad]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}"
                                             class="imagen_botón"
                                             alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="sin-registros">No hay registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    /* ============ ELIMINAR UNA ============ */
    function eliminarModalidad(id) {
        if (confirm('¿Está seguro de que desea eliminar esta modalidad?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

    /* ============ SELECCIÓN MÚLTIPLE ============ */
    function toggleSeleccionarTodos(master) {
        const checks = document.querySelectorAll('.check-fila');
        checks.forEach(c => { c.checked = master.checked; });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        const checks = document.querySelectorAll('.check-fila');
        const seleccionados = Array.from(checks).filter(c => c.checked);

        document.getElementById('contador_seleccionados').textContent = seleccionados.length;

        const btn = document.getElementById('btn_eliminar_seleccionados');
        btn.style.display = seleccionados.length > 0 ? 'inline-flex' : 'none';

        const master = document.getElementById('check_todos');
        if (master) {
            master.checked = seleccionados.length === checks.length && checks.length > 0;
            master.indeterminate = seleccionados.length > 0 && seleccionados.length < checks.length;
        }

        checks.forEach(c => {
            const fila = c.closest('tr');
            if (fila) {
                fila.classList.toggle('fila-seleccionada', c.checked);
            }
        });
    }

    function eliminarSeleccionados() {
        const checks = document.querySelectorAll('.check-fila');
        const ids = Array.from(checks).filter(c => c.checked).map(c => c.value);

        if (ids.length === 0) return;

        const plural = ids.length === 1 ? 'modalidad' : 'modalidades';
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