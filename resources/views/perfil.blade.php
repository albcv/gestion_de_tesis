@extends('layouts.app')

@section('content')

@vite(['resources/css/perfil.css'])

<div class="perfil-container">
    <h1>👤 Perfil de Usuario</h1>

    <div class="perfil-info">

        <div class="campo">
            <label>👤 Nombre</label>
            <span>{{ $user->name }}</span>
        </div>

        <div class="campo">
            <label>📧 Email</label>
            <span>{{ $user->email }}</span>
        </div>

        <div class="campo">
            <label>🛡️ Rol</label>
            <span>{{ $user->rol->rol }}</span>
        </div>

        <div class="campo">
            <label>📅 Fecha de creación</label>
            <span>{{ $user->created_at->format('d/m/Y') }}</span>
        </div>

        <!-- Acciones: cambiar contraseña + cerrar sesión -->
        <div class="acciones-perfil">
            <a href="{{ route('cambiarContraseña') }}" class="btn-cambiar-contrasena">
                🔑 Cambiar Contraseña
            </a>

            <button type="button"
                    class="btn-cerrar-sesion"
                    onclick="confirmarCerrarSesion()">
                🚪 Cerrar Sesión
            </button>
        </div>

    </div>
</div>

<!-- Formulario POST oculto para logout -->
<form id="formLogout" method="POST" action="{{ route('logout') }}" style="display: none;">
    @csrf
</form>

<script>
    function confirmarCerrarSesion() {
        if (confirm('¿Está seguro de que desea cerrar sesión?')) {
            document.getElementById('formLogout').submit();
        }
    }
</script>

@endsection