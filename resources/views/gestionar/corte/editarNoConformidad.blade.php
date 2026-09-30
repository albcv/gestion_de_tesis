@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/recomendaciones/editar.css'])

<div class="editar-recomendacion-container">

    <div class="form-header">
        <h1>✏️ Cambiar No Conformidad del Corte</h1>
        <p class="subtitle">Cambie la no conformidad asociada a este corte de tesis</p>
    </div>

    {{-- ============================
         INFO CORTE
         ============================ --}}
    <div class="info-recomendacion">
        <div class="info-header">
            <h3>📋 Información del Corte</h3>
            <div class="info-badges">
                <span class="badge-id">Corte #{{ $corte->Numero_corte }}</span>
            </div>
        </div>

        <div class="info-fundamentacion-detalles">
            <h4>📄 Detalles del Corte</h4>
            <div class="info-grid">
                
                <div class="info-item">
                    <span class="info-label">Trabajo de Diploma:</span>
                    <span class="info-value">{{ $corte->tesis->Nombre_trabajo }}</span>
                </div>
                <div class="info-item nc-actual-item">
                    <span class="info-label">No Conformidad Actual:</span>
                    <span class="info-value nc-actual">{{ $noConformidad->Deficiencias_detectadas }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================
         FORMULARIO
         ============================ --}}
    <div class="form-container">
        <form method="POST"
              action="{{ route('actualizarNoConformidadCorte') }}"
              class="form-recomendacion"
              id="formNC">

            @csrf
            <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
            <input type="hidden" name="no_conformidad_actual" value="{{ $noConformidad->idNoConformidades }}">

            <div class="form-group">
                <label for="no_conformidad_nueva" class="form-label">
                    <span class="label-icon">🔄</span> Seleccionar Nueva No Conformidad
                    <span class="label-hint">({{ $noConformidades->count() }} disponibles)</span>
                </label>

                @if($noConformidades->count() > 0)
                    <select id="no_conformidad_nueva"
                            name="no_conformidad_nueva"
                            required
                            class="select-nc">
                        <option value=""></option>
                        @foreach($noConformidades as $nc)
                            @if($nc->idNoConformidades != $noConformidad->idNoConformidades)
                                <option value="{{ $nc->idNoConformidades }}"
                                        data-descripcion="{{ $nc->Deficiencias_detectadas }}">
                                    {{ Str::limit($nc->Deficiencias_detectadas, 120) }}
                                </option>
                            @endif
                        @endforeach
                    </select>

                    {{-- Preview comparativo --}}
                    <div class="comparacion-nueva" id="comparacion-nueva" style="display: none;">
                        <h4>🔍 Comparación de No Conformidades</h4>
                        <div class="comparacion-grid">
                            <div class="comparacion-item comparacion-actual">
                                <div class="comparacion-titulo">🔴 Actual</div>
                                <div class="comparacion-texto">
                                    {{ $noConformidad->Deficiencias_detectadas }}
                                </div>
                            </div>
                            <div class="comparacion-item comparacion-nueva-item">
                                <div class="comparacion-titulo">🟢 Nueva</div>
                                <div class="comparacion-texto" id="nc-nueva-content"></div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="no-data">
                        <p>No hay otras no conformidades disponibles para cambiar.</p>
                        <p>Puede crear una nueva usando el botón inferior.</p>
                    </div>
                @endif

                @error('no_conformidad_nueva')
                    <div class="error-message">
                        <span class="error-icon">❌</span> {{ $message }}
                    </div>
                @enderror
            </div>

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

            <div class="form-buttons">
                <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}"
                   class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar
                </a>
                <button type="submit" class="btn-guardar" id="btnCambiar" disabled>
                    <span class="btn-icon">🔄</span> Cambiar No Conformidad
                </button>
            </div>
        </form>
    </div>

    {{-- ============================
         ACCIÓN ALTERNATIVA
         ============================ --}}
    <div class="accion-alternativa">
        <p class="accion-alternativa-texto">¿No encuentra la no conformidad adecuada?</p>
        <a href="{{ route('agregarNoConformidadCorte', $corte->idCortes_de_tesis) }}"
           class="btn-alternativa">
            ➕ Crear Nueva No Conformidad
        </a>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selectNC       = document.getElementById('no_conformidad_nueva');
    const comparacionDiv = document.getElementById('comparacion-nueva');
    const contenidoNueva = document.getElementById('nc-nueva-content');
    const form           = document.getElementById('formNC');
    const btnCambiar     = document.getElementById('btnCambiar');

    if (selectNC && comparacionDiv && contenidoNueva) {
        selectNC.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            const descripcion = option ? option.getAttribute('data-descripcion') : '';

            if (this.value && descripcion) {
                contenidoNueva.textContent = descripcion;
                comparacionDiv.style.display = 'block';
                if (btnCambiar) btnCambiar.disabled = false;
            } else {
                comparacionDiv.style.display = 'none';
                contenidoNueva.textContent = '';
                if (btnCambiar) btnCambiar.disabled = true;
            }
        });
    }

    /* ---------- Validación previa ---------- */
    if (form) {
        form.addEventListener('submit', function (e) {
            const nuevaNC = selectNC ? selectNC.value : null;
            const actualNC = "{{ $noConformidad->idNoConformidades }}";

            if (!nuevaNC) {
                e.preventDefault();
                alert('Debe seleccionar una nueva no conformidad.');
                return false;
            }

            if (nuevaNC === actualNC) {
                e.preventDefault();
                alert('Debe seleccionar una no conformidad diferente a la actual.');
                return false;
            }

            // Deshabilitar el botón para evitar doble envío
            if (btnCambiar) {
                btnCambiar.disabled = true;
                btnCambiar.innerHTML = '<span class="btn-icon">⏳</span> Procesando...';
            }

            return true;
        });
    }
});
</script>

@endsection