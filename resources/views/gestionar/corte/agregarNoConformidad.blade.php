@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/recomendaciones/crear.css'])

<div class="crear-recomendacion-container">

    <div class="form-header">
        <h1>➕ Agregar No Conformidad al Corte</h1>
        <p class="subtitle">Seleccione una no conformidad existente o cree una nueva</p>
    </div>

    {{-- ============================
         INFO CORTE
         ============================ --}}
    <div class="info-fundamentacion">
        <div class="info-header">
            <h3>📋 Información del Corte</h3>
            <span class="info-badge">Corte #{{ $corte->Numero_corte }}</span>
        </div>

        <div class="info-grid">
           
            <div class="info-item">
                <span class="info-label">Trabajo de Diploma:</span>
                <span class="info-value">{{ $corte->tesis->Nombre_trabajo }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    @if($corte->aprobado)
                        <span style="color: green; font-weight: 600;">Aprobado</span>
                    @elseif($corte->desaprobado)
                        <span style="color: red; font-weight: 600;">Desaprobado</span>
                    @else
                        <span style="color: orange; font-weight: 600;">Pendiente</span>
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- ============================
         TABS + FORMULARIOS
         ============================ --}}
    <div class="form-container tabs-form-container">

        {{-- Tabs --}}
        <div class="tabs-nav">
            <button type="button" class="tab-btn active" data-tab="existente">
                📋 Usar No Conformidad Existente
            </button>
            <button type="button" class="tab-btn" data-tab="nueva">
                🆕 Crear Nueva No Conformidad
            </button>
        </div>

        {{-- Contenido de las tabs --}}
        <div class="tab-content">

            {{-- ============================
                 TAB 1: USAR EXISTENTE
                 ============================ --}}
            <div id="tab-existente" class="tab-pane active">

                <div class="tab-description">
                    <p>Seleccione una no conformidad de la lista de deficiencias ya registradas en el sistema.</p>
                </div>

                <form method="POST"
                      action="{{ route('agregarNoConformidadCorteExistente') }}"
                      class="form-recomendacion">
                    @csrf
                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">

                    <div class="form-group">
                        <label for="no_conformidad_id" class="form-label">
                            <span class="label-icon">📋</span> Seleccionar No Conformidad
                            <span class="label-hint">({{ $noConformidades->count() }} disponibles)</span>
                        </label>

                        @if($noConformidades->count() > 0)
                            <select id="no_conformidad_id"
                                    name="no_conformidad_id"
                                    required
                                    class="select-nc">
                                <option value=""></option>
                                @foreach($noConformidades as $nc)
                                    <option value="{{ $nc->idNoConformidades }}"
                                            data-descripcion="{{ $nc->Deficiencias_detectadas }}">
                                        {{ Str::limit($nc->Deficiencias_detectadas, 100) }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Vista previa --}}
                            <div class="nc-preview" id="nc-preview" style="display: none;">
                                <h4>📝 Vista previa de la No Conformidad</h4>
                                <div class="nc-preview-content" id="nc-preview-content"></div>
                            </div>
                        @else
                            <div class="no-data">
                                <p>No hay no conformidades registradas en el sistema.</p>
                                <p>Por favor, cree una nueva no conformidad.</p>
                            </div>
                        @endif
                    </div>

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
                        @if($noConformidades->count() > 0)
                            <button type="submit" class="btn-guardar">
                                <span class="btn-icon">🔗</span> Vincular No Conformidad
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            {{-- ============================
                 TAB 2: CREAR NUEVA
                 ============================ --}}
            <div id="tab-nueva" class="tab-pane">

                <div class="tab-description">
                    <p>Cree una nueva no conformidad y vincúlela automáticamente a este corte.</p>
                    <p class="hint">Si la descripción ya existe en el sistema, se vinculará la existente automáticamente.</p>
                </div>

                <form method="POST"
                      action="{{ route('crearYVincularNoConformidadCorte') }}"
                      class="form-recomendacion">
                    @csrf
                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">

                    <div class="form-group">
                        <label for="deficiencias_detectadas" class="form-label">
                            <span class="label-icon">📝</span> Deficiencias Detectadas
                            <span class="label-hint">(mínimo 10 caracteres, máximo 500)</span>
                        </label>

                        <div class="textarea-container">
                            <textarea id="deficiencias_detectadas"
                                      name="deficiencias_detectadas"
                                      rows="10"
                                      placeholder="Describa detalladamente las deficiencias detectadas en este corte de tesis..."
                                      required>{{ old('deficiencias_detectadas') }}</textarea>
                            <div class="textarea-footer">
                                <span class="char-count" id="charCount">0 / 500</span>
                            </div>
                        </div>

                        @error('deficiencias_detectadas')
                            <div class="error-message">
                                <span class="error-icon">❌</span> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-buttons">
                        <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}"
                           class="btn-cancelar">
                            <span class="btn-icon">←</span> Cancelar
                        </a>
                        <button type="submit" class="btn-guardar">
                            <span class="btn-icon">➕</span> Crear y Vincular
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ============================
       SISTEMA DE TABS
       ============================ */
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            const tabId = button.getAttribute('data-tab');

            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));

            button.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
        });
    });

    /* ============================
       PREVIEW DE NC EXISTENTE
       ============================ */
    const selectNc      = document.getElementById('no_conformidad_id');
    const preview       = document.getElementById('nc-preview');
    const previewContent = document.getElementById('nc-preview-content');

    if (selectNc && preview && previewContent) {
        selectNc.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            const descripcion = option ? option.getAttribute('data-descripcion') : '';

            if (this.value && descripcion) {
                previewContent.innerHTML = `
                    <div class="info-item">
                        <span class="info-label">Descripción:</span>
                        <span class="info-value">${descripcion}</span>
                    </div>
                `;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
                previewContent.innerHTML = '';
            }
        });
    }

    /* ============================
       CONTADOR DE CARACTERES
       ============================ */
    const textarea  = document.getElementById('deficiencias_detectadas');
    const charCount = document.getElementById('charCount');

    if (textarea && charCount) {
        const actualizar = () => {
            const length = textarea.value.length;
            charCount.textContent = `${length} / 500`;

            if (length > 450) {
                charCount.style.color = '#dc2626';
                charCount.style.fontWeight = 'bold';
            } else if (length > 400) {
                charCount.style.color = '#d97706';
                charCount.style.fontWeight = 'bold';
            } else {
                charCount.style.color = '';
                charCount.style.fontWeight = '';
            }
        };

        textarea.addEventListener('input', actualizar);
        actualizar(); // inicializar
    }
});
</script>

@endsection