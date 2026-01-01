@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/css/gestionar/gestionarRecomendacionesFundamentación/editar.css'])


<div class="editar-recomendacion-container">
    <div class="form-header">
        <h1>✏️ Editar Recomendación</h1>
        <p class="subtitle">Modifique la recomendación para esta fundamentación</p>
    </div>
    
    <!-- Información de la recomendación -->
    <div class="info-recomendacion">
        <div class="info-header">
            <h3>📋 Información de la Recomendación</h3>
            <div class="info-badges">
                <span class="badge-id">ID: {{ $recomendacion->id_recomendaciones_fundamentacion }}</span>
                <span class="badge-fecha">Creada: {{ $recomendacion->created_at->format('d/m/Y') }}</span>
            </div>
        </div>
        
        <div class="info-fundamentacion-detalles">
            <h4>📄 Información de la Fundamentación</h4>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Trabajo de Diploma:</span>
                    <span class="info-value">{{ $recomendacion->fundamentacion->tesis->Nombre_trabajo }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estudiante:</span>
                    <span class="info-value">
                        {{ $recomendacion->fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                        {{ $recomendacion->fundamentacion->tesis->estudiante->Apellido1 }}
                        {{ $recomendacion->fundamentacion->tesis->estudiante->Apellido2 }}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">CI del Estudiante:</span>
                    <span class="info-value">{{ $recomendacion->fundamentacion->tesis->estudiante->CI_estudiante }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Fundamentación:</span>
                    <span class="info-value">{{ $recomendacion->fundamentacion->id_fundamentacion }}</span>
                </div>
                
                @if($recomendacion->fundamentacion->tesis->estudiante->grupo)
                    <div class="info-item">
                        <span class="info-label">Grupo:</span>
                        <span class="info-value">{{ $recomendacion->fundamentacion->tesis->estudiante->grupo->número }}</span>
                    </div>
                @endif
                
                @if($recomendacion->fundamentacion->tesis->estudiante->grupo && $recomendacion->fundamentacion->tesis->estudiante->grupo->carrera)
                    <div class="info-item">
                        <span class="info-label">Carrera:</span>
                        <span class="info-value">{{ $recomendacion->fundamentacion->tesis->estudiante->grupo->carrera->nombre ?? $recomendacion->fundamentacion->tesis->estudiante->grupo->carrera->Nombre_carrera }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Formulario de edición -->
    <div class="form-container">
        <form method="POST" action="{{ route('modificarRecomendacionFundamentacion') }}" class="form-recomendacion">
            @csrf
            <input type="hidden" name="id" value="{{ $recomendacion->id_recomendaciones_fundamentacion }}">
            <input type="hidden" name="id_fundamentacion" value="{{ $recomendacion->id_fundamentacion }}">
            
            <div class="form-group">
                <label for="recomendacion" class="form-label">
                    <span class="label-icon">📝</span> Recomendación
                  
                </label>
                <div class="textarea-container">
                    <textarea id="recomendacion" name="recomendacion" rows="12" 
                              placeholder="Modifique aquí la recomendación para esta fundamentación..." 
                              required>{{ old('recomendacion', $recomendacion->recomendacion) }}</textarea>
                    <div class="textarea-footer">
                        <span class="char-count" id="charCount">{{ strlen($recomendacion->recomendacion) }} / 2000</span>
                        <span class="last-update">Última modificación: {{ $recomendacion->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                @error('recomendacion')
                    <div class="error-message">
                        <span class="error-icon">❌</span> {{ $message }}
                    </div>
                @enderror
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
            

            
            <!-- Botones -->
            <div class="form-buttons">
                <a href="/verFundamentación/{{ $recomendacion->id_fundamentacion }}" class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar y Volver
                </a>
                <button type="submit" class="btn-guardar">
                    <span class="btn-icon">💾</span> Actualizar Recomendación
                </button>
                <button type="button" class="btn-reset" onclick="resetForm()">
                    <span class="btn-icon">↺</span> Restaurar Original
                </button>
            </div>
        </form>
    </div>
    
    
</div>

<script>
// Contador de caracteres
document.getElementById('recomendacion').addEventListener('input', function() {
    const length = this.value.length;
    const charCount = document.getElementById('charCount');
    charCount.textContent = `${length} / 2000`;
    
    if (length > 1900) {
        charCount.style.color = '#dc3545';
        charCount.style.fontWeight = 'bold';
    } else if (length > 1500) {
        charCount.style.color = '#ffc107';
        charCount.style.fontWeight = 'bold';
    } else {
        charCount.style.color = '#6c757d';
        charCount.style.fontWeight = 'normal';
    }
});

// Restaurar contenido original
let originalContent = "{{ addslashes($recomendacion->recomendacion) }}";

function resetForm() {
    if (confirm('¿Está seguro de que desea restaurar el contenido original de la recomendación? Se perderán los cambios no guardados.')) {
        document.getElementById('recomendacion').value = originalContent.replace(/\\/g, '');
        
        // Actualizar contador
        const length = originalContent.replace(/\\/g, '').length;
        const charCount = document.getElementById('charCount');
        charCount.textContent = `${length} / 2000`;
        
        // Mostrar mensaje de éxito
        showToast('Contenido restaurado correctamente', 'success');
    }
}

// Mostrar toast de notificación
function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span>
        <span class="toast-message">${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Animar entrada
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Inicializar contador
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('recomendacion');
    const length = textarea.value.length;
    const charCount = document.getElementById('charCount');
    charCount.textContent = `${length} / 2000`;
});
</script>

@endsection