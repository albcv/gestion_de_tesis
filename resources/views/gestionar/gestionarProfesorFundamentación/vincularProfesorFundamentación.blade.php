@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarProfesorFundamentación/vincularProfesorFundamentación.css') }}">

<div class="vincular-profesor-container">
    <div class="form-header">
        <h1>👨‍🏫 Vincular Profesor a la Fundamentación</h1>
        <p class="subtitle">Asigne un profesor para la revisión de esta fundamentación de tesis</p>
    </div>
    
    <!-- Información de la fundamentación -->
    <div class="info-fundamentacion">
        <div class="info-header">
            <h3>📋 Información de la Fundamentación</h3>
            <span class="info-badge">ID #{{ $fundamentacion->id_fundamentacion }}</span>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ID de la Fundamentación:</span>
                <span class="info-value">{{ $fundamentacion->id_fundamentacion }}</span>
            </div>
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
    
    <!-- Formulario para vincular profesor -->
    <div class="form-container">
        <form method="POST" action="{{ route('vincularProfesorFundamentación.post') }}" class="form-vincular">
            @csrf
            <input type="hidden" name="fundamentacion_id" value="{{ $fundamentacion->id_fundamentacion }}">
            
            <div class="form-group">
                <label for="profesor_id" class="form-label">
                    <span class="label-icon">👨‍🏫</span> Seleccionar Profesor
                    <span class="label-hint">({{ $profesoresDisponibles->count() }} disponibles)</span>
                </label>
                
                @if($profesoresDisponibles->count() > 0)
                    <select id="profesor_id" name="profesor_id" required class="select-profesor">
                        <option value=""></option>
                        @foreach($profesoresDisponibles as $profesor)
                            <option value="{{ $profesor->id }}">
                                {{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                @if($profesor->departamento)
                                    - {{ $profesor->departamento->Nombre_departamento }}
                                @endif
                                ({{ $profesor->Categoria_docente }})
                            </option>
                        @endforeach
                    </select>
                    
                    <!-- Vista previa del profesor seleccionado -->
                    <div class="profesor-preview" id="profesor-preview" style="display: none;">
                        <h4>👨‍🏫 Información del Profesor:</h4>
                        <div class="profesor-info-grid" id="profesor-info-content"></div>
                    </div>
                @else
                    <div class="no-data">
                        <p>No hay profesores disponibles para vincular.</p>
                        <p>Todos los profesores ya están vinculados a esta fundamentación o no hay profesores registrados.</p>
                    </div>
                @endif
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
                <a href="{{ route('verFundamentación', ['id' => $fundamentacion->id_fundamentacion]) }}" class="btn-cancelar">
                    <span class="btn-icon">←</span> Cancelar
                </a>
                @if($profesoresDisponibles->count() > 0)
                    <button type="submit" class="btn-vincular">
                        <span class="btn-icon">🔗</span> Vincular Profesor
                    </button>
                @endif
            </div>
        </form>
    </div>
    
    
</div>

<script>
// Vista previa del profesor seleccionado
const selectProfesor = document.getElementById('profesor_id');
const previewDiv = document.getElementById('profesor-preview');
const previewContent = document.getElementById('profesor-info-content');

if (selectProfesor) {
    selectProfesor.addEventListener('change', function() {
        if (this.value) {
            // Obtener el texto de la opción seleccionada
            const selectedOption = this.options[this.selectedIndex];
            const textoCompleto = selectedOption.text;
            
            // Crear contenido HTML para la vista previa
            let html = `
                <div class="info-item">
                    <span class="info-label">Profesor:</span>
                    <span class="info-value">${textoCompleto}</span>
                </div>
            `;
            
            previewContent.innerHTML = html;
            previewDiv.style.display = 'block';
        } else {
            previewDiv.style.display = 'none';
        }
    });
}

// Validación antes de enviar el formulario
const form = document.querySelector('.form-vincular');
if (form) {
    form.addEventListener('submit', function(e) {
        const profesorId = document.getElementById('profesor_id').value;
        
        if (!profesorId) {
            e.preventDefault();
            alert('Debe seleccionar un profesor para vincular.');
            return false;
        }
        
        return confirm('¿Está seguro de vincular este profesor a la fundamentación?');
    });
}
</script>

@endsection