@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/css/gestionar/gestionarCortesNoConformidades/editar.css'])


<div class="editar-nc-container">
    <div class="form-header">
        <h1>✏️ Cambiar No Conformidad del Corte</h1>
        <p class="subtitle">Cambie la no conformidad asociada a este corte de tesis</p>
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
                <span class="info-label">No Conformidad Actual:</span>
                <span class="info-value nc-actual">{{ $noConformidad->Deficiencias_detectadas }}</span>
            </div>
        </div>
    </div>
    
    <!-- Formulario de edición -->
    <div class="form-container">
        <form method="POST" action="{{ route('actualizarNoConformidadCorte') }}" class="form-nc">
            @csrf
            <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
            <input type="hidden" name="no_conformidad_actual" value="{{ $noConformidad->idNoConformidades }}">
            
            <div class="form-group">
                <label for="no_conformidad_nueva" class="form-label">
                    <span class="label-icon">🔄</span> Seleccionar Nueva No Conformidad
                    <span class="label-hint">({{ $noConformidades->count() }} disponibles)</span>
                </label>
                
                <select id="no_conformidad_nueva" name="no_conformidad_nueva" required class="select-nc">
                    <option value=""></option>
                    @foreach($noConformidades as $nc)
                        @if($nc->idNoConformidades != $noConformidad->idNoConformidades)
                            <option value="{{ $nc->idNoConformidades }}">
                                {{ Str::limit($nc->Deficiencias_detectadas, 120) }}
                            </option>
                        @endif
                    @endforeach
                </select>
                
              
            </div>
            
            <!-- Mensajes de éxito/error -->
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
                <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar
                </a>
                <button type="submit" class="btn-actualizar">
                    <span class="btn-icon">🔄</span> Cambiar No Conformidad
                </button>
            </div>
        </form>
    </div>
    
    <!-- Opción para crear nueva no conformidad -->
  
        <a href="{{ route('agregarNoConformidadCorte', $corte->idCortes_de_tesis) }}" class="btn-alternativa">
            ➕ Crear Nueva No Conformidad
        </a>
    
</div>

<script>
// Mostrar comparación cuando se selecciona nueva no conformidad
const selectNuevaNC = document.getElementById('no_conformidad_nueva');
const comparacionDiv = document.getElementById('comparacion-nueva');
const contenidoNuevaNC = document.getElementById('nc-nueva-content');

if (selectNuevaNC) {
    selectNuevaNC.addEventListener('change', function() {
        if (this.value) {
            // Obtener el texto de la opción seleccionada
            const selectedOption = this.options[this.selectedIndex];
            contenidoNuevaNC.textContent = selectedOption.text;
            comparacionDiv.style.display = 'block';
        } else {
            comparacionDiv.style.display = 'none';
        }
    });
}

// Validación antes de enviar el formulario
const form = document.querySelector('.form-nc');
if (form) {
    form.addEventListener('submit', function(e) {
        const nuevaNC = document.getElementById('no_conformidad_nueva').value;
        const actualNC = "{{ $noConformidad->idNoConformidades }}";
        
        if (nuevaNC === actualNC) {
            e.preventDefault();
            alert('Debe seleccionar una no conformidad diferente a la actual.');
            return false;
        }
        
        if (!nuevaNC) {
            e.preventDefault();
            alert('Debe seleccionar una nueva no conformidad.');
            return false;
        }
        
        return confirm('¿Está seguro de cambiar la no conformidad asociada a este corte?');
    });
}
</script>

@endsection