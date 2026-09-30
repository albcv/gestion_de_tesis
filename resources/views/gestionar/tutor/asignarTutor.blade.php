@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/tutor/asignarTutor.css'])

<div class="main-content-wrapper">
    <div class="asignar-tutor-container">
        <div class="form-header">
            <h1>👨‍🏫 Asignar Tutor al Estudiante</h1>
        </div>

        {{-- ============================
             INFO DEL ESTUDIANTE
             ============================ --}}
        <div class="info-estudiante">
            <div class="info-header">
                <h3>📚 Información del Estudiante</h3>
                <span class="info-badge {{ $cantidadTutores >= 2 ? 'info-badge-warning' : '' }}">
                    {{ $cantidadTutores }}/2 Tutores Asignados
                </span>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">
                        {{ $estudiante->Nombre_estudiante }} {{ $estudiante->Apellido1 }} {{ $estudiante->Apellido2 }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">CI:</span>
                    <span class="info-value">{{ $estudiante->CI_estudiante }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Carrera:</span>
                    <span class="info-value">{{ $estudiante->carrera->Nombre_carrera ?? 'No especificada' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Facultad:</span>
                    <span class="info-value">{{ $estudiante->carrera->facultad->Nombre_facultad ?? 'No especificada' }}</span>
                </div>

                @if($tutoresActuales && $tutoresActuales->count() > 0)
                <div class="info-item tutores-actuales">
                    <span class="info-label">Tutores Actuales:</span>
                    <div class="lista-tutores-actual">
                        @foreach($tutoresActuales as $tutor)
                        <div class="tutor-actual-item">
                            <span class="tutor-actual-nombre">
                                {{ $tutor->profesor->Nombre_profesor }}
                                {{ $tutor->profesor->Apellido1 }}
                                {{ $tutor->profesor->Apellido2 }}
                            </span>
                            <span class="tutor-actual-info">
                                {{ $tutor->profesor->Categoria_docente }} -
                                {{ $tutor->profesor->departamento->Nombre_departamento ?? 'Sin departamento' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ============================
             FORMULARIO
             ============================ --}}
        <div class="form-container">
            @if($slotsDisponibles > 0)
                <form method="POST" action="{{ route('agregarTutorEstudiante') }}" class="form-asignar" id="formAsignar">
                    @csrf
                    <input type="hidden" name="id_estudiante" value="{{ $estudiante->id }}">

                    {{-- Cabecera con contador --}}
                    <div class="info-seleccion">
                        <div class="info-seleccion-texto">
                            Puede asignar hasta
                            <strong>{{ $slotsDisponibles }}</strong>
                            tutor(es) más a este estudiante.
                        </div>
                        <span class="contador-seleccion" id="contador-seleccion">
                            <span id="num-seleccionados">0</span> / {{ $slotsDisponibles }} seleccionados
                        </span>
                    </div>

                    @error('id_profesor')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    @error('id_profesor.*')
                        <span class="error-message">{{ $message }}</span>
                    @enderror

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

                    {{-- Tabla de profesores con checkboxes --}}
                    @if($profesores->count() > 0)
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
                                            <th>Tutorados</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($profesores as $profesor)
                                            @php
                                                $yaEsTutor = $tutoresActuales->contains('id_profesor', $profesor->id);
                                            @endphp

                                            @if(!$yaEsTutor)
                                            <tr class="profesor-row" data-id="{{ $profesor->id }}">
                                                <td class="col-check">
                                                    <input type="checkbox"
                                                           name="id_profesor[]"
                                                           value="{{ $profesor->id }}"
                                                           class="check-profesor"
                                                           data-nombre="{{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}"
                                                           data-categoria-docente="{{ $profesor->Categoria_docente }}"
                                                           data-categoria-cientifica="{{ $profesor->Categoria_cientifica }}"
                                                           data-departamento="{{ $profesor->departamento ? $profesor->departamento->Nombre_departamento : 'No asignado' }}"
                                                           data-tutorados="{{ $profesor->tutorados ? $profesor->tutorados->count() : 0 }}">
                                                </td>
                                                <td>{{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}</td>
                                                <td>{{ $profesor->departamento->Nombre_departamento ?? 'No asignado' }}</td>
                                                <td>{{ $profesor->Categoria_docente }}</td>
                                                <td>{{ $profesor->Categoria_cientifica }}</td>
                                                <td>
                                                    <span class="tutorados-badge {{ $profesor->tutorados && $profesor->tutorados->count() >= 10 ? 'badge-warning' : 'badge-success' }}">
                                                        {{ $profesor->tutorados ? $profesor->tutorados->count() : 0 }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Preview de tutores seleccionados --}}
                        <div class="seleccion-preview" id="seleccion-preview" style="display: none;">
                            <h4>✅ Tutores Seleccionados</h4>
                            <div class="seleccion-lista" id="seleccion-lista"></div>
                        </div>
                    @else
                        <div class="no-data">
                            <p>No hay profesores disponibles para asignar como tutor.</p>
                            <p>Por favor, registre profesores en el sistema primero.</p>
                        </div>
                    @endif

                    <div class="form-buttons">
                        @if($profesores->count() > 0)
                            <button type="submit" class="btn-asignar" id="btnAsignar" disabled>
                                <span class="btn-icon">➕</span>
                                Asignar Tutor(es)
                            </button>
                        @endif
                        <a href="{{ route('verUsuario', $estudiante->id_usuario) }}" class="btn-cancelar">
                            <span class="btn-icon">←</span> Cancelar
                        </a>
                    </div>
                </form>
            @else
                <div class="max-tutores-alcanzado">
                    <div class="max-icon">⚠️</div>
                    <h3>Límite de Tutores Alcanzado</h3>
                    <p>Este estudiante ya tiene el máximo de {{ \App\Http\Controllers\tutorEstudianteController::MAX_TUTORES_POR_ESTUDIANTE }} tutores asignados.</p>
                    <p>Si desea cambiar un tutor, primero debe eliminar uno de los existentes desde la vista del estudiante.</p>
                    <a href="{{ route('verUsuario', $estudiante->id_usuario) }}" class="btn-volver-estudiante">
                        ← Volver al Estudiante
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formAsignar');
    if (!form) return;

    const MAX_SELECCION = {{ (int) $slotsDisponibles }};
    const checkboxes    = form.querySelectorAll('.check-profesor');
    const rows          = form.querySelectorAll('.profesor-row');
    const btnAsignar    = document.getElementById('btnAsignar');
    const numSelec      = document.getElementById('num-seleccionados');
    const contador      = document.getElementById('contador-seleccion');
    const preview       = document.getElementById('seleccion-preview');
    const listaPreview  = document.getElementById('seleccion-lista');

    /* ---------- Render principal ---------- */
    function actualizar() {
        const seleccionados = Array.from(checkboxes).filter(c => c.checked);
        const count = seleccionados.length;

        // Contador
        numSelec.textContent = count;
        contador.classList.toggle('contador-lleno', count >= MAX_SELECCION);

        // Botón
        btnAsignar.disabled = count === 0;

        // Resaltar filas + bloquear las no seleccionadas al llegar al tope
        checkboxes.forEach(c => {
            const row = c.closest('tr');
            if (row) row.classList.toggle('selected', c.checked);
            c.disabled = (count >= MAX_SELECCION && !c.checked);
        });

        // Preview
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
            const tut      = c.getAttribute('data-tutorados') || '0';

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
                        <span><b>Tutorados:</b> ${tut}</span>
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
            if (!cb || cb.disabled) return;

            cb.checked = !cb.checked;
            actualizar();
        });
    });

    /* ---------- Submit ---------- */
    form.addEventListener('submit', function (e) {
        const seleccionados = Array.from(checkboxes).filter(c => c.checked);

        if (seleccionados.length === 0) {
            e.preventDefault();
            alert('Por favor, seleccione al menos un profesor.');
            return false;
        }

        if (seleccionados.length > MAX_SELECCION) {
            e.preventDefault();
            alert(`Solo puede asignar hasta ${MAX_SELECCION} tutor(es).`);
            return false;
        }

        // Aviso si alguno tiene 10+ tutorados
        const conMuchos = seleccionados.filter(c => parseInt(c.getAttribute('data-tutorados') || 0) >= 10);
        if (conMuchos.length > 0) {
            const nombres = conMuchos.map(c => '• ' + c.getAttribute('data-nombre')).join('\n');
            if (!confirm(`⚠️ Los siguientes profesores ya tienen 10 o más tutorados:\n\n${nombres}\n\n¿Desea continuar?`)) {
                e.preventDefault();
                return false;
            }
        }

        btnAsignar.disabled = true;
        btnAsignar.innerHTML = '<span class="btn-icon">⏳</span> Procesando...';
        return true;
    });

    actualizar();
});
</script>

@endsection