@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarRecomendacionesFundamentación/crear.css') }}">

<div class="crear-recomendacion-container">
    <div class="form-header">
        <h1>➕ Agregar Recomendación</h1>
        <p class="subtitle">Complete el formulario para agregar una recomendación a esta fundamentación</p>
    </div>
    
    <!-- Información de la fundamentación -->
    <div class="info-fundamentacion">
        <div class="info-header">
            <h3>📋 Información de la Fundamentación</h3>
            <span class="info-badge">ID: {{ $fundamentacion->id_fundamentacion }}</span>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Trabajo de Diploma:</span>
                <span class="info-value">{{ $fundamentacion->tesis->Nombre_trabajo }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estudiante:</span>
                <span class="info-value">
                    {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                    {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                    {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">CI del Estudiante:</span>
                <span class="info-value">{{ $fundamentacion->tesis->estudiante->CI_estudiante }}</span>
            </div>
            
            @if($fundamentacion->tesis->estudiante->grupo)
                <div class="info-item">
                    <span class="info-label">Grupo:</span>
                    <span class="info-value">{{ $fundamentacion->tesis->estudiante->grupo->número }}</span>
                </div>
            @endif
            
            @if($fundamentacion->tesis->estudiante->carrera)
                <div class="info-item">
                    <span class="info-label">Carrera:</span>
                    <span class="info-value">{{ $fundamentacion->tesis->estudiante->carrera->nombre ?? $fundamentacion->tesis->estudiante->carrera->Nombre_carrera }}</span>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Formulario -->
    <div class="form-container">
        <form method="POST" action="{{ route('agregarRecomendacionFundamentacion.store') }}" class="form-recomendacion">
            @csrf
            <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
            
            <div class="form-group">
                <label for="recomendacion" class="form-label">
                    <span class="label-icon">💡</span> Recomendación
                    <span class="label-hint">(mínimo 3 caracteres, máximo 2000)</span>
                </label>
                <div class="textarea-container">
                    <textarea id="recomendacion" name="recomendacion" rows="12" 
                              placeholder="Escriba aquí la recomendación para esta fundamentación. Sea claro y específico sobre los aspectos que deben mejorarse o considerarse..." 
                              required>{{ old('recomendacion') }}</textarea>
                    <div class="textarea-footer">
                        <span class="char-count" id="charCount">0 / 2000</span>
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
                <a href="/verFundamentación/{{ $fundamentacion->id_fundamentacion }}" class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar y Volver
                </a>
                <button type="submit" class="btn-guardar">
                    <span class="btn-icon">💾</span> Agregar Recomendación
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


// Inicializar contador
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('recomendacion');
    const length = textarea.value.length;
    const charCount = document.getElementById('charCount');
    charCount.textContent = `${length} / 2000`;
});
</script>

@endsection