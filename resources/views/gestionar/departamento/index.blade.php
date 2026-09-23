@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarDepartamento', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Departamento
            </a>

            @if (count($departamentos) > 0)
                <a href="{{ route('exportarDepartamentosCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Departamento</h1>

        @if (session('error'))
            <div class="alerta alerta-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="info-resultados">
            <p>Total de departamentos: <strong>{{ count($departamentos) }}</strong></p>

            <button type="button"
                    id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarDepartamento') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariosDepartamentos') }}" style="display: none;">
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
                        <th>Nombre</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departamentos as $departamento)
                        <tr id="fila_{{ $departamento->idDepartamento }}">
                            <td class="col-check">
                                <input type="checkbox"
                                       class="checkbox-fila check-fila"
                                       value="{{ $departamento->idDepartamento }}"
                                       onchange="actualizarSeleccion()">
                            </td>
                            <td>{{ $departamento->Nombre_departamento }}</td>
                            <td>
                                <div class="acciones-td">
                                    <img src="{{ asset('img/eliminar.jpg') }}"
                                         class="imagen_botón"
                                         alt="Eliminar"
                                         title="Eliminar"
                                         onclick="eliminarDepartamento({{ $departamento->idDepartamento }})">
                                    <a href="{{ route('gestionarDepartamento', ['accion' => 'editar', 'id' => $departamento->idDepartamento]) }}"
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
    function eliminarDepartamento(id) {
        if (confirm('¿Está seguro de que desea eliminar este departamento?')) {
            document.getElementById('inputIdEliminar').value = id;
            document.getElementById('formEliminar').submit();
        }
    }

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

        const plural = ids.length === 1 ? 'departamento' : 'departamentos';
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