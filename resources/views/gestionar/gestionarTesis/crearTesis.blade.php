@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarTesis/crearTesis.css') }}">

<div class="crear-tesis-container">
    <h1>Crear Nueva Tesis</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarTesis" class="btn-volver">← Volver a la lista</a>
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
    
    <form action="/agregarTesis" method="post" id="formulario_tesis" class="form-crear-tesis">
        @csrf
        
        <div class="seccion-formulario">
            <h2>Información de la Tesis</h2>
            
            <div class="form-grid">
                <div class="campo-formulario">
                    <label for="nombre_tesis">Nombre del Trabajo de Diploma</label>
                    <input type="text" id="nombre_tesis" name="nombre_tesis" class="atributo" 
                           placeholder="Ingrese el nombre completo de la tesis" 
                           value="{{ old('nombre_tesis') }}"
                           required minlength="10" maxlength="300">
            
                </div>
                
                <div class="campo-formulario">
                    <label for="id_estudiante">Estudiante Asignado</label>
                    <select id="id_estudiante" name="id_estudiante" class="atributo" required>
                        <option value="">Seleccione un estudiante</option>
                        @foreach($estudiantes as $estudiante)
                            <option value="{{ $estudiante->id }}" {{ old('id_estudiante') == $estudiante->id ? 'selected' : '' }}>
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
            <button type="submit" class="btn-crear-tesis">
                <span>+</span> Crear Tesis
            </button>
            <a href="/gestionarTesis" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formulario_tesis');
    const nombreInput = document.getElementById('nombre_tesis');
    const estudianteSelect = document.getElementById('id_estudiante');
    
    // Solo validación al enviar el formulario
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
            
            // Enfocar el primer campo con error
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