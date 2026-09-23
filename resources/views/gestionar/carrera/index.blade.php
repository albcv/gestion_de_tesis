@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('gestionarCarrera', ['accion' => 'crear']) }}" class="btn-crear">
                <span id="ícono_crear">+</span> Crear Carrera
            </a>

            @if (count($carreras) > 0)
                <a href="{{ route('exportarCarrerasCsv') }}" class="btn-exportar-csv">
                    📄 Exportar a CSV
                </a>
            @endif
        </div>

        <h1>Gestionar Carreras</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        <!-- Filtros -->
        <form method="GET" action="{{ route('gestionarCarrera') }}" class="form-filtros-usuarios">
            <div class="filtros-fila">
                <input type="text" name="carrera_nombre" placeholder="Buscar carrera por nombre..."
                       value="{{ request('carrera_nombre') }}" class="input-filtro">

                <select name="facultad_id" class="input-filtro">
                    <option value="">Todas las facultades</option>
                    @foreach($facultadesSelect as $facultad)
                        <option value="{{ $facultad->idFacultad }}"
                            {{ request('facultad_id') == $facultad->idFacultad ? 'selected' : '' }}>
                            {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn-guardar-filtro">Aplicar</button>
                @if(request('carrera_nombre') || request('facultad_id'))
                    <a href="{{ route('gestionarCarrera') }}" class="btn-limpiar-filtro">Limpiar</a>
                @endif
            </div>
        </form>

        <div class="info-resultados">
            <p>
                Mostrando <strong>{{ $carreras->count() }}</strong>
                {{ $carreras->count() == 1 ? 'carrera' : 'carreras' }}
                @if(request('carrera_nombre') || request('facultad_id')) (filtradas) @endif
            </p>

            <button type="button" id="btn_eliminar_seleccionados"
                    class="btn-eliminar-seleccionados"
                    onclick="eliminarSeleccionados()"
                    style="display: none;">
                Eliminar seleccionados (<span id="contador_seleccionados">0</span>)
            </button>
        </div>

        <form id="formEliminar" method="POST" action="{{ route('eliminarCarrera') }}" style="display: none;">
            @csrf
            <input type="hidden" name="id" id="inputIdEliminar">
        </form>

        <form id="formEliminarVarias" method="POST" action="{{ route('eliminarVariasCarreras') }}" style="display: none;">
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
                        <th>Nombre de la Carrera</th>
                        <th>Facultad</th>
                        <th>Modalidades</th>
                        <th style="text-align:center;">Estudiantes</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($carreras as $carrera)
                        <tr id="fila_{{ $carrera->id }}">
                            <td class="col-check">
                                <input type="checkbox" class="checkbox-fila check-fila"
                                       value="{{ $carrera->id }}" onchange="actualizarSeleccion()">
                            </td>
                            <td>
                                <strong>{{ $carrera->Nombre_carrera }}</strong>
                                <br>
                                <small style="color:#666;">
                                    {{ $carrera->created_at ? $carrera->created_at->format('d/m/Y') : 'N/A' }}
                                </small>
                            </td>
                            <td>
                                @if($carrera->facultad)
                                    <span class="permiso-badge">{{ $carrera->facultad->Siglas }}</span>
                                    {{ $carrera->facultad->Nombre_facultad }}
                                @else
                                    <span class="sin-informacion">Sin facultad</span>
                                @endif
                            </td>
                            <td>
                                @if($carrera->modalidades->count() > 0)
                                    <div class="permisos-list">
                                        @foreach($carrera->modalidades as $modalidad)
                                            <span class="permiso-badge">
                                                {{ $modalidad->Nombre_modalidad }}
                                                ({{ $modalidad->pivot->cantidad_years ?? '?' }}a)
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="sin-permisos">Sin modalidades</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <span style="font-size:18px; font-weight:700; color:#030;">
                                    {{ $carrera->cantidad_estudiantes }}
                                </span>
                            </td>
                            <td>
                                <div class="acciones-td">
                                    <a href="{{ route('gestionarCarrera', ['accion' => 'detalles', 'id' => $carrera->id]) }}"
                                       title="Ver detalles">
                                        <img src="{{ asset('img/ver.jpg') }}" class="imagen_botón" alt="Ver">
                                    </a>
                                    <img src="{{ asset('img/eliminar.jpg') }}" class="imagen_botón"
                                         alt="Eliminar" title="Eliminar"
                                         onclick="eliminarCarrera({{ $carrera->id }})">
                                    <a href="{{ route('gestionarCarrera', ['accion' => 'editar', 'id' => $carrera->id]) }}"
                                       title="Editar">
                                        <img src="{{ asset('img/editar.jpg') }}" class="imagen_botón" alt="Editar">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sin-registros">
                                @if(request('carrera_nombre') || request('facultad_id'))
                                    No se encontraron carreras con los criterios de búsqueda
                                @else
                                    No hay carreras registradas
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    function eliminarCarrera(id) {
        if (confirm('¿Está seguro de que desea eliminar esta carrera?')) {
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

        const plural = ids.length === 1 ? 'carrera' : 'carreras';
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