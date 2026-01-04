@extends('layouts.app')

@vite(['resources/css/cambiarContraseña.css'])

@section('content')
<div class="cambiar-contrasena-container">
    <div class="cambiar-contrasena-card">
        <h1><i class="fas fa-key"></i> Cambiar Contraseña</h1>
        
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('cambiarContraseña.procesar') }}" class="contrasena-form">
            @csrf
            
            <div class="form-group">
                <label for="contrasena_actual">
                    <i class="fas fa-lock"></i> Contraseña Actual
                </label>
                <div class="input-with-icon">
                    <input type="password" 
                           id="contrasena_actual" 
                           name="contrasena_actual" 
                           class="form-control @error('contrasena_actual') is-invalid @enderror"
                           placeholder="Ingresa tu contraseña actual"
                           required>
                    <button type="button" class="toggle-password" data-target="contrasena_actual">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                @error('contrasena_actual')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="nueva_contrasena">
                    <i class="fas fa-lock"></i> Nueva Contraseña
                </label>
                <div class="input-with-icon">
                    <input type="password" 
                           id="nueva_contrasena" 
                           name="nueva_contrasena" 
                           class="form-control @error('nueva_contrasena') is-invalid @enderror"
                           placeholder="Mínimo 6 caracteres"
                           required>
                    <button type="button" class="toggle-password" data-target="nueva_contrasena">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                @error('nueva_contrasena')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="nueva_contrasena_confirmation">
                    <i class="fas fa-lock"></i> Confirmar Nueva Contraseña
                </label>
                <div class="input-with-icon">
                    <input type="password" 
                           id="nueva_contrasena_confirmation" 
                           name="nueva_contrasena_confirmation" 
                           class="form-control"
                           placeholder="Repite la nueva contraseña"
                           required>
                    <button type="button" class="toggle-password" data-target="nueva_contrasena_confirmation">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            
            
            <div class="form-actions">
                <a href="{{ route('perfil') }}" class="btn-cancelar">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle para mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
});
</script>
@endsection