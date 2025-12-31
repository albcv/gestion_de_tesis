@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/formulario.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarCortesProfesor/vincular.css') }}">

<div class="vincular-profesor-container">
    <div class="form-header">
        <h1>👨‍🏫 Vincular Profesor al Corte</h1>
        <p class="subtitle">Asocie un profesor oponente a este corte de tesis</p>
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
                <span class="info-label">Profesores Actuales:</span>
                <span class="info-value">{{ $corte->profesores->count() }}</span>
            </div>
        </div>
    </div>
    
    <!-- Formulario para vincular profesor -->
    <div class="form-container">
        <form method="POST" action="{{ route('vincularProfesorCorte.post') }}" class="form-vincular">
            @csrf
            <input type="hidden" name="corte_tesis_id" value="{{ $corte->idCortes_de_tesis }}">
            
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
                        <p>Todos los profesores ya están vinculados a este corte o no hay profesores registrados.</p>
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
                <a href="{{ route('verCorte', ['id' => $corte->idCortes_de_tesis]) }}" class="btn-cancelar">
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
            
            // Extraer información del texto (asumiendo un formato específico)
            const partes = textoCompleto.split(' - ');
            const nombre = partes[0];
            const departamentoYCategoria = partes[1] || '';
            
            // Crear contenido HTML para la vista previa
            let html = `
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">${nombre}</span>
                </div>
            `;
            
            if (departamentoYCategoria) {
                const [departamento, categoria] = departamentoYCategoria.split(' (');
                html += `
                    <div class="info-item">
                        <span class="info-label">Departamento:</span>
                        <span class="info-value">${departamento}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Categoría:</span>
                        <span class="info-value">${categoria ? categoria.replace(')', '') : 'No especificada'}</span>
                    </div>
                `;
            }
            
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
        
        return confirm('¿Está seguro de vincular este profesor al corte?');
    });
}
</script>

@endsection