@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarTutorEstudiante/asignarTutor.css') }}">

@php
    $profesoresArray = $profesores->map(function($profesor) {
        return [
            'id' => $profesor->id,
            'categoria_docente' => $profesor->Categoria_docente,
            'categoria_cientifica' => $profesor->Categoria_cientifica,
            'departamento' => $profesor->departamento ? $profesor->departamento->Nombre_departamento : 'No asignado',
            'tutorados_count' => $profesor->tutorados ? $profesor->tutorados->count() : 0
        ];
    })->toArray();
@endphp

<div class="main-content-wrapper">
   
    
    <div class="asignar-tutor-container">
        <div class="form-header">
            <h1>👨‍🏫 Asignar Tutor al Estudiante</h1>

        </div>
        
        <!-- Información del estudiante -->
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
                
                <!-- Lista de tutores actuales -->
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
        
        <!-- Formulario para asignar tutor -->
        <div class="form-container">
            @if($cantidadTutores < 2)
            <form method="POST" action="{{ route('agregarTutorEstudiante') }}" class="form-asignar" id="formAsignar">
                @csrf
                <input type="hidden" name="id_estudiante" value="{{ $estudiante->id }}">
                
                <div class="form-group">
                    <label for="id_profesor" class="form-label">
                        <span class="label-icon">👨‍🏫</span> Seleccionar Tutor
                        <span class="label-hint">({{ $profesores->count() }} disponibles)</span>
                    </label>
                    
                    @if($profesores->count() > 0)
                        <select id="id_profesor" name="id_profesor" required class="select-profesor">
                            <option value=""></option>
                            @foreach($profesores as $profesor)
                                @php
                                    $yaEsTutor = false;
                                    foreach($tutoresActuales as $tutor) {
                                        if($tutor->id_profesor == $profesor->id) {
                                            $yaEsTutor = true;
                                            break;
                                        }
                                    }
                                @endphp
                                
                                @if(!$yaEsTutor)
                                <option value="{{ $profesor->id }}" 
                                    data-categoria-docente="{{ $profesor->Categoria_docente }}"
                                    data-categoria-cientifica="{{ $profesor->Categoria_cientifica }}"
                                    data-departamento="{{ $profesor->departamento ? $profesor->departamento->Nombre_departamento : 'No asignado' }}"
                                    data-tutorados="{{ $profesor->tutorados ? $profesor->tutorados->count() : 0 }}">
                                    {{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                    @if($profesor->departamento)
                                        - {{ $profesor->departamento->Nombre_departamento }}
                                    @endif
                                </option>
                                @endif
                            @endforeach
                        </select>
                        
                        <!-- Vista previa del profesor seleccionado -->
                        <div class="profesor-preview" id="profesor-preview" style="display: none;">
                            <h4>👨‍🏫 Información del Tutor Seleccionado</h4>
                            <div class="profesor-info-grid">
                                <div class="info-item">
                                    <span class="info-label">Categoría Docente:</span>
                                    <span class="info-value" id="categoria-docente"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Categoría Científica:</span>
                                    <span class="info-value" id="categoria-cientifica"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Departamento:</span>
                                    <span class="info-value" id="departamento"></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Estudiantes Tutoreados:</span>
                                    <span class="info-value" id="tutorados-actuales"></span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="no-data">
                            <p>No hay profesores disponibles para asignar como tutor.</p>
                            <p>Por favor, registre profesores en el sistema primero.</p>
                        </div>
                    @endif
                    
                    @error('id_profesor')
                        <span class="error-message">{{ $message }}</span>
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
                
                <div class="form-buttons">
                    @if($profesores->count() > 0)
                        <button type="submit" class="btn-asignar" id="btnAsignar">
                            <span class="btn-icon">➕</span>
                            Asignar Tutor
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
                <p>Este estudiante ya tiene el máximo de 2 tutores asignados.</p>
                <p>Si desea cambiar un tutor, primero debe eliminar uno de los existentes desde la vista del estudiante.</p>
                <a href="{{ route('verUsuario', $estudiante->id_usuario) }}" class="btn-volver-estudiante">
                    ← Volver al Estudiante
                </a>
            </div>
            @endif
        </div>
        
        <!-- Lista de profesores disponibles -->
        @if($profesores->count() > 0 && $cantidadTutores < 2)
        <div class="profesores-disponibles">
            <h4>📋 Lista de Profesores Disponibles</h4>
            <div class="table-responsive">
                <table class="table-profesores">
                    <thead>
                        <tr>
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
                                $yaEsTutor = false;
                                foreach($tutoresActuales as $tutor) {
                                    if($tutor->id_profesor == $profesor->id) {
                                        $yaEsTutor = true;
                                        break;
                                    }
                                }
                            @endphp
                            
                            @if(!$yaEsTutor)
                            <tr class="profesor-row" 
                                data-id="{{ $profesor->id }}"
                                data-categoria-docente="{{ $profesor->Categoria_docente }}"
                                data-categoria-cientifica="{{ $profesor->Categoria_cientifica }}"
                                data-departamento="{{ $profesor->departamento ? $profesor->departamento->Nombre_departamento : 'No asignado' }}"
                                data-tutorados="{{ $profesor->tutorados ? $profesor->tutorados->count() : 0 }}">
                                <td>
                                    {{ $profesor->Nombre_profesor }} {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                </td>
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
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectProfesor = document.getElementById('id_profesor');
    const previewDiv = document.getElementById('profesor-preview');
    const btnAsignar = document.getElementById('btnAsignar');
    const profesorRows = document.querySelectorAll('.profesor-row');
    const form = document.getElementById('formAsignar');
    
    // Función para mostrar vista previa del profesor
    function mostrarVistaPreviaProfesor(profesorId) {
        if (!profesorId) {
            previewDiv.style.display = 'none';
            return;
        }
        
        const option = selectProfesor.options[selectProfesor.selectedIndex];
        if (option) {
            document.getElementById('categoria-docente').textContent = 
                option.getAttribute('data-categoria-docente') || 'No especificada';
            document.getElementById('categoria-cientifica').textContent = 
                option.getAttribute('data-categoria-cientifica') || 'No especificada';
            document.getElementById('departamento').textContent = 
                option.getAttribute('data-departamento') || 'No asignado';
            document.getElementById('tutorados-actuales').textContent = 
                option.getAttribute('data-tutorados') || '0';
            
            previewDiv.style.display = 'block';
            
            // Resaltar fila correspondiente en la tabla
            profesorRows.forEach(row => {
                if (row.getAttribute('data-id') == profesorId) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
        }
    }
    
    // Evento cambio en select
    if (selectProfesor) {
        selectProfesor.addEventListener('change', function() {
            mostrarVistaPreviaProfesor(this.value);
        });
        
        // Mostrar vista previa si ya hay un valor seleccionado
        if (selectProfesor.value) {
            mostrarVistaPreviaProfesor(selectProfesor.value);
        }
    }
    
    // Click en fila de la tabla para seleccionar profesor
    profesorRows.forEach(row => {
        row.addEventListener('click', function() {
            const profesorId = this.getAttribute('data-id');
            selectProfesor.value = profesorId;
            mostrarVistaPreviaProfesor(profesorId);
            selectProfesor.focus();
        });
    });
    
    // Validación antes de enviar formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            const profesorId = selectProfesor ? selectProfesor.value : null;
            
            if (!profesorId) {
                e.preventDefault();
                alert('Por favor, seleccione un profesor para asignar como tutor.');
                return false;
            }
            
            // Obtener número de tutorados del profesor seleccionado
            const option = selectProfesor.options[selectProfesor.selectedIndex];
            const tutoradosCount = parseInt(option.getAttribute('data-tutorados') || 0);
            
            // Advertencia si tiene muchos tutorados
            if (tutoradosCount >= 10) {
                if (!confirm(`⚠️ Este profesor ya tiene ${tutoradosCount} estudiantes tutoreados.\n\n¿Desea continuar con la asignación?`)) {
                    e.preventDefault();
                    return false;
                }
            }
            
        
            
            // Mostrar loading en botón
            if (btnAsignar) {
                btnAsignar.disabled = true;
                btnAsignar.innerHTML = '<span class="btn-icon">⏳</span> Procesando...';
            }
            
            return true;
        });
    }
});
</script>

@endsection