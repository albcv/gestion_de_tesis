@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/css/gestionar/gestionarCortesNoConformidades/crear.css'])



<div class="crear-nc-container">
    <div class="form-header">
        <h1>➕ Agregar No Conformidad al Corte</h1>
        <p class="subtitle">Seleccione una no conformidad existente o cree una nueva</p>
    </div>
    
    <!-- Información del corte -->
    <div class="info-corte">
        <div class="info-header">
            <h3>📋 Información del Corte</h3>
            <span class="info-badge">Corte #{{ $corte->Numero_corte }}</span>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID del Corte:</span>
                <span class="info-value">{{ $corte->idCortes_de_tesis }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Trabajo de Diploma:</span>
                <span class="info-value">{{ $corte->tesis->Nombre_trabajo }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    @if($corte->aprobado)
                        <span style="color: green;">Aprobado</span>
                    @elseif($corte->desaprobado)
                        <span style="color: red;">Desaprobado</span>
                    @else
                        <span style="color: orange;">Pendiente</span>
                    @endif
                </span>
            </div>
        </div>
    </div>
    
    <!-- Tabs para seleccionar opción -->
    <div class="tabs-container">
        <div class="tabs">
            <button type="button" class="tab-btn active" data-tab="existente">
                📋 Usar No Conformidad Existente
            </button>
            <button type="button" class="tab-btn" data-tab="nueva">
                🆕 Crear Nueva No Conformidad
            </button>
        </div>
        
        <!-- Contenido de las tabs -->
        <div class="tab-content">
            <!-- Tab 1: Usar existente -->
            <div id="tab-existente" class="tab-pane active">
                <div class="tab-description">
                    <p>Seleccione una no conformidad de la lista de deficiencias ya registradas en el sistema.</p>
                </div>
                
                <form method="POST" action="{{ route('agregarNoConformidadCorteExistente') }}" class="form-nc">
                    @csrf
                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                    
                    <div class="form-group">
                        <label for="no_conformidad_id" class="form-label">
                            <span class="label-icon">📋</span> Seleccionar No Conformidad
                            <span class="label-hint">({{ $noConformidades->count() }} disponibles)</span>
                        </label>
                        
                        @if($noConformidades->count() > 0)
                            <select id="no_conformidad_id" name="no_conformidad_id" required class="select-nc">
                                <option value=""></option>
                                @foreach($noConformidades as $nc)
                                    <option value="{{ $nc->idNoConformidades }}">
                                        {{ Str::limit($nc->Deficiencias_detectadas, 100) }}
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Vista previa de la no conformidad seleccionada -->
                            <div class="nc-preview" id="nc-preview" style="display: none;">
                                <h4>📝 Vista previa:</h4>
                                <div class="nc-preview-content" id="nc-preview-content"></div>
                            </div>
                        @else
                            <div class="no-data">
                                <p>No hay no conformidades registradas en el sistema.</p>
                                <p>Por favor, cree una nueva no conformidad.</p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="form-buttons">
                        <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-cancelar">
                            <span class="btn-icon">←</span> Cancelar
                        </a>
                        @if($noConformidades->count() > 0)
                            <button type="submit" class="btn-vincular">
                                <span class="btn-icon">🔗</span> Vincular No Conformidad
                            </button>
                        @endif
                    </div>
                </form>
            </div>
            
            <!-- Tab 2: Crear nueva -->
            <div id="tab-nueva" class="tab-pane">
                <div class="tab-description">
                    <p>Cree una nueva no conformidad y vincúlela automáticamente a este corte.</p>
                    <p class="hint">Si la descripción ya existe en el sistema, se vinculará la existente automáticamente.</p>
                </div>
                
                <form method="POST" action="{{ route('crearYVincularNoConformidadCorte') }}" class="form-nc">
                    @csrf
                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                    
                    <div class="form-group">
                        <label for="deficiencias_detectadas" class="form-label">
                            <span class="label-icon">📝</span> Deficiencias Detectadas
                            <span class="label-hint">(mínimo 10 caracteres, máximo 500)</span>
                        </label>
                        
                        <div class="textarea-container">
                            <textarea id="deficiencias_detectadas" name="deficiencias_detectadas" rows="8" 
                                      placeholder="Describa detalladamente las deficiencias detectadas en este corte de tesis..." 
                                      required></textarea>
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
                        <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-cancelar">
                            <span class="btn-icon">←</span> Cancelar
                        </a>
                        <button type="submit" class="btn-crear">
                            <span class="btn-icon">➕</span> Crear y Vincular
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
  
</div>

<script>
// Sistema de tabs
document.querySelectorAll('.tab-btn').forEach(button => {
    button.addEventListener('click', () => {
        const tabId = button.getAttribute('data-tab');
        
        // Remover clase active de todos los botones y paneles
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        // Agregar clase active al botón y panel seleccionado
        button.classList.add('active');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    });
});



// Contador de caracteres para nueva no conformidad
const textarea = document.getElementById('deficiencias_detectadas');
const charCount = document.getElementById('charCount');

if (textarea && charCount) {
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = `${length} / 500`;
        
        if (length > 450) {
            charCount.style.color = '#dc3545';
            charCount.style.fontWeight = 'bold';
        } else if (length > 400) {
            charCount.style.color = '#ffc107';
            charCount.style.fontWeight = 'bold';
        } else {
            charCount.style.color = '#6c757d';
            charCount.style.fontWeight = 'normal';
        }
    });
}

// Inicializar contador
if (textarea && charCount) {
    const length = textarea.value.length;
    charCount.textContent = `${length} / 500`;
}
</script>

@endsection