@extends('layouts.app')

@section('content')

@vite(['resources/css/cambiarContraseña.css'])

<div class="cambiar-contrasena-container">
    <div class="cambiar-contrasena-card">
        <h1>🔑 Cambiar Contraseña</h1>

        @if(session('success'))
            <div class="alert alert-success">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                ❌ {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cambiarContraseña.procesar') }}" class="contrasena-form">
            @csrf

            <div class="form-group">
                <label for="contrasena_actual">🔒 Contraseña Actual</label>
                <div class="input-with-icon">
                    <input type="password"
                           id="contrasena_actual"
                           name="contrasena_actual"
                           class="form-control @error('contrasena_actual') is-invalid @enderror"
                           placeholder="Ingresa tu contraseña actual"
                           required>
                    <button type="button" class="toggle-password" data-target="contrasena_actual" aria-label="Mostrar contraseña">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
                @error('contrasena_actual')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="nueva_contrasena">🔒 Nueva Contraseña</label>
                <div class="input-with-icon">
                    <input type="password"
                           id="nueva_contrasena"
                           name="nueva_contrasena"
                           class="form-control @error('nueva_contrasena') is-invalid @enderror"
                           placeholder="Mínimo 6 caracteres"
                           required>
                    <button type="button" class="toggle-password" data-target="nueva_contrasena" aria-label="Mostrar contraseña">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
                @error('nueva_contrasena')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="nueva_contrasena_confirmation">🔒 Confirmar Nueva Contraseña</label>
                <div class="input-with-icon">
                    <input type="password"
                           id="nueva_contrasena_confirmation"
                           name="nueva_contrasena_confirmation"
                           class="form-control"
                           placeholder="Repite la nueva contraseña"
                           required>
                    <button type="button" class="toggle-password" data-target="nueva_contrasena_confirmation" aria-label="Mostrar contraseña">
                        <span class="eye-icon">👁️</span>
                    </button>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('perfil') }}" class="btn-cancelar">
                    ← Cancelar
                </a>
                <button type="submit" class="btn-guardar">
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('.eye-icon');

            if (!input || !icon) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.textContent = isHidden ? '🙈' : '👁️';
            this.setAttribute(
                'aria-label',
                isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña'
            );
        });
    });
});
</script>

@endsection