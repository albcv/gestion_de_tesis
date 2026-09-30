@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/asignarOponente.css'])

<div class="vincular-profesor-container">

    <div class="form-header">
        <h1>👨‍🏫 Vincular Profesores a la Fundamentación</h1>
        <p class="subtitle">Seleccione uno o varios profesores para la revisión de esta fundamentación de tesis</p>
    </div>

    {{-- ============================
         INFO FUNDAMENTACIÓN
         ============================ --}}
    <div class="info-fundamentacion">
        <div class="info-header">
            <h3>📋 Información de la Fundamentación</h3>
            <span class="info-badge">ID #{{ $fundamentacion->id_fundamentacion }}</span>
        </div>

        <div class="info-grid">
            
            <div class="info-item">
                <span class="info-label">Trabajo de Diploma:</span>
                <span class="info-value">{{ $fundamentacion->tesis->Nombre_trabajo }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Profesores Actuales:</span>
                <span class="info-value">{{ $fundamentacion->profesores->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    @if($fundamentacion->aprobada)
                        <span style="color: green; font-weight: 600;">Aprobada</span>
                    @elseif($fundamentacion->desaprobada)
                        <span style="color: red; font-weight: 600;">Desaprobada</span>
                    @else
                        <span style="color: orange; font-weight: 600;">Pendiente</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- ============================
         FORMULARIO
         ============================ --}}
    <div class="form-container">
        <form method="POST"
              action="{{ route('vincularProfesorFundamentación.post') }}"
              class="form-vincular"
              id="formVincular">

            @csrf
            <input type="hidden" name="fundamentacion_id" value="{{ $fundamentacion->id_fundamentacion }}">

            {{-- Cabecera con contador --}}
            @if($profesoresDisponibles->count() > 0)
                <div class="info-seleccion">
                    <div class="info-seleccion-texto">
                        Profesores disponibles: <strong>{{ $profesoresDisponibles->count() }}</strong>
                    </div>
                    <span class="contador-seleccion" id="contador-seleccion">
                        <span id="num-seleccionados">0</span> seleccionado(s)
                    </span>
                </div>
            @endif

            @error('profesor_id')
                <span class="error-message">{{ $message }}</span>
            @enderror
            @error('profesor_id.*')
                <span class="error-message">{{ $message }}</span>
            @enderror

            {{-- Alertas de sesión --}}
            @if(session('success'))
                <div class="alert alert-success">
                    <span class="alert-icon">✅</span> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <span class="alert-icon">❌</span> {{ session('error') }}
                </div>
            @endif

            {{-- ============================
                 TABLA DE PROFESORES CON CHECKBOX
                 ============================ --}}
            @if($profesoresDisponibles->count() > 0)

                <div class="profesores-disponibles">
                    <h4>📋 Seleccione el/los Profesor(es)</h4>
                    <div class="table-responsive">
                        <table class="table-profesores">
                            <thead>
                                <tr>
                                    <th class="col-check"></th>
                                    <th>Nombre</th>
                                    <th>Departamento</th>
                                    <th>Categoría Docente</th>
                                    <th>Categoría Científica</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($profesoresDisponibles as $profesor)
                                    <tr class="profesor-row" data-id="{{ $profesor->id }}">
                                        <td class="col-check">
                                            <input type="checkbox"
                                                   name="profesor_id[]"
                                                   value="{{ $profesor->id }}"
                                                   class="check-profesor"
                                                   data-nombre="{{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}"
                                                   data-categoria-docente="{{ $profesor->Categoria_docente }}"
                                                   data-categoria-cientifica="{{ $profesor->Categoria_cientifica }}"
                                                   data-departamento="{{ $profesor->departamento ? $profesor->departamento->Nombre_departamento : 'No asignado' }}">
                                        </td>
                                        <td>
                                            {{ $profesor->Nombre_profesor }}
                                            {{ $profesor->Apellido1 }}
                                            {{ $profesor->Apellido2 }}
                                        </td>
                                        <td>{{ $profesor->departamento->Nombre_departamento ?? 'No asignado' }}</td>
                                        <td>{{ $profesor->Categoria_docente }}</td>
                                        <td>{{ $profesor->Categoria_cientifica }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Preview de profesores seleccionados --}}
                <div class="seleccion-preview" id="seleccion-preview" style="display: none;">
                    <h4>✅ Profesores Seleccionados</h4>
                    <div class="seleccion-lista" id="seleccion-lista"></div>
                </div>

            @else
                <div class="no-data">
                    <p>No hay profesores disponibles para vincular.</p>
                    <p>Todos los profesores ya están vinculados a esta fundamentación o no hay profesores registrados.</p>
                </div>
            @endif

            <div class="form-buttons">
                <a href="{{ route('verFundamentación', ['id' => $fundamentacion->id_fundamentacion]) }}"
                   class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar
                </a>

                @if($profesoresDisponibles->count() > 0)
                    <button type="submit"
                            class="btn-vincular"
                            id="btnVincular"
                            disabled>
                        <span class="btn-icon">🔗</span>
                        Vincular Profesor(es)
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formVincular');
    if (!form) return;

    const checkboxes   = form.querySelectorAll('.check-profesor');
    const rows         = form.querySelectorAll('.profesor-row');
    const btnVincular  = document.getElementById('btnVincular');
    const numSelec     = document.getElementById('num-seleccionados');
    const contador     = document.getElementById('contador-seleccion');
    const preview      = document.getElementById('seleccion-preview');
    const listaPreview = document.getElementById('seleccion-lista');

    /* ---------- Render principal ---------- */
    function actualizar() {
        const seleccionados = Array.from(checkboxes).filter(c => c.checked);
        const count = seleccionados.length;

        // Contador
        if (numSelec) numSelec.textContent = count;
        if (contador) contador.classList.toggle('contador-lleno', count > 0);

        // Botón
        if (btnVincular) btnVincular.disabled = count === 0;

        // Resaltar filas
        checkboxes.forEach(c => {
            const row = c.closest('tr');
            if (row) row.classList.toggle('selected', c.checked);
        });

        // Preview
        if (!preview || !listaPreview) return;

        if (count === 0) {
            preview.style.display = 'none';
            listaPreview.innerHTML = '';
            return;
        }
        preview.style.display = 'block';

        listaPreview.innerHTML = seleccionados.map(c => {
            const id       = c.value;
            const nombre   = c.getAttribute('data-nombre') || '';
            const catDoc   = c.getAttribute('data-categoria-docente') || '—';
            const catCient = c.getAttribute('data-categoria-cientifica') || '—';
            const dep      = c.getAttribute('data-departamento') || '—';

            return `
                <div class="seleccion-item">
                    <div class="seleccion-item-header">
                        <strong>${nombre}</strong>
                        <button type="button" class="btn-quitar" data-id="${id}" title="Quitar">×</button>
                    </div>
                    <div class="seleccion-item-info">
                        <span><b>Docente:</b> ${catDoc}</span>
                        <span><b>Científica:</b> ${catCient}</span>
                        <span><b>Depto:</b> ${dep}</span>
                    </div>
                </div>
            `;
        }).join('');

        // Botones "quitar"
        listaPreview.querySelectorAll('.btn-quitar').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const cb = form.querySelector(`.check-profesor[value="${id}"]`);
                if (cb) {
                    cb.checked = false;
                    actualizar();
                }
            });
        });
    }

    /* ---------- Checkbox change ---------- */
    checkboxes.forEach(c => c.addEventListener('change', actualizar));

    /* ---------- Click en fila = toggle ---------- */
    rows.forEach(row => {
        row.addEventListener('click', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON') return;

            const cb = this.querySelector('.check-profesor');
            if (!cb) return;

            cb.checked = !cb.checked;
            actualizar();
        });
    });

    /* ---------- Submit ---------- */
    form.addEventListener('submit', function (e) {
        const seleccionados = Array.from(checkboxes).filter(c => c.checked);

        // Solo validar que haya al menos un profesor seleccionado
        if (seleccionados.length === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un profesor para vincular.');
            return false;
        }

        // Deshabilitar el botón para evitar doble envío y mostrar estado
        if (btnVincular) {
            btnVincular.disabled = true;
            btnVincular.innerHTML = '<span class="btn-icon">⏳</span> Procesando...';
        }

        return true;
    });

    actualizar();
});
</script>

@endsection