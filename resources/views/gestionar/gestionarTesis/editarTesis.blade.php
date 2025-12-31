@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarTesis/editarTesis.css') }}">

<div class="editar-tesis-container">
    <h1>Editar Tesis</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarTesis" class="btn-volver">← Volver a la lista</a>
        <a href="/verTesis/{{ $tesis->id }}" class="btn-ver">👁️ Ver Detalles</a>
    </div>
    
    <!-- Mensajes de error -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <form action="/modificarTesis" method="post" id="formulario_tesis" class="form-editar-tesis">
        @csrf
        <input type="hidden" name="id" value="{{ $tesis->id }}">
        
        <div class="seccion-formulario">
            <h2>Información Actual de la Tesis</h2>
            
            <div class="informacion-actual">
                <div class="info-item">
                    <span class="info-label">Estudiante Actual:</span>
                    <span class="info-valor">
                        @if($tesis->estudiante)
                            {{ $tesis->estudiante->Nombre_estudiante }} 
                            {{ $tesis->estudiante->Apellido1 }} 
                            {{ $tesis->estudiante->Apellido2 }}
                        @else
                            <span class="no-asignado">No asignado</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tesis Actual:</span>
                    <span class="info-valor">{{ $tesis->Nombre_trabajo }}</span>
                </div>
            </div>
        </div>
        
        <div class="seccion-formulario">
            <h2>Modificar Información</h2>
            
            <div class="form-grid">
                <div class="campo-formulario">
                    <label for="nombre_tesis">Nuevo Nombre del Trabajo</label>
                    <input type="text" id="nombre_tesis" name="nombre_tesis" class="atributo" 
                           value="{{ old('nombre_tesis', $tesis->Nombre_trabajo) }}"
                           placeholder="Ingrese el nuevo nombre de la tesis" required
                           minlength="10" maxlength="300">
                </div>
                
                <div class="campo-formulario">
                    <label for="id_estudiante">Nuevo Estudiante Asignado</label>
                    <select id="id_estudiante" name="id_estudiante" class="atributo" required>
                        <option value="">Seleccione un estudiante</option>
                        @foreach($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" 
                                    {{ old('id_estudiante', $tesis->id_estudiante) == $estudiante->id ? 'selected' : '' }}>
                                {{ $estudiante->Nombre_estudiante }} 
                                {{ $estudiante->Apellido1 }} 
                                {{ $estudiante->Apellido2 }}
                                (CI: {{ $estudiante->CI_estudiante }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="seccion-acciones">
            <button type="submit" class="btn-guardar">
                💾 Guardar Cambios
            </button>
            <a href="/gestionarTesis" class="btn-cancelar">Cancelar</a>
            <button type="button" class="btn-eliminar" onclick="confirmarEliminacion()">
                🗑️ Eliminar Tesis
            </button>
        </div>
    </form>
</div>

<!-- Formulario oculto para eliminar -->
<form id="formEliminar" method="POST" action="/eliminarTesis" style="display: none;">
    @csrf
    <input type="hidden" name="id" value="{{ $tesis->id }}">
</form>

<script>

function confirmarEliminacion() {
    if (confirm('¿Está seguro de que desea eliminar esta tesis?')) {
        document.getElementById('formEliminar').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formulario_tesis');
    const nombreInput = document.getElementById('nombre_tesis');
    const estudianteSelect = document.getElementById('id_estudiante');

    form.addEventListener('submit', function(e) {
        let errores = [];
        
        // Validar nombre
        if (nombreInput.value.trim().length < 10) {
            errores.push('El nombre debe tener al menos 10 caracteres');
        } else if (nombreInput.value.trim().length > 300) {
            errores.push('El nombre no puede exceder los 300 caracteres');
        }
        
        // Validar estudiante
        if (!estudianteSelect.value) {
            errores.push('Debe seleccionar un estudiante');
        }
        
        // Si hay errores, mostrar alert y prevenir envío
        if (errores.length > 0) {
            e.preventDefault();
            
            // Crear mensaje de error
            let mensaje = 'Por favor, corrija los siguientes errores:\n\n';
            errores.forEach(error => {
                mensaje += '• ' + error + '\n';
            });
            
            alert(mensaje);
            
        
            if (nombreInput.value.trim().length < 10 || nombreInput.value.trim().length > 300) {
                nombreInput.focus();
            } else if (!estudianteSelect.value) {
                estudianteSelect.focus();
            }
        }
    });
});
</script>

@endsection