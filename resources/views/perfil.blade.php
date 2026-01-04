@extends('layouts.app') 

@vite(['resources/css/perfil.css', 'resources/css/cambiarContraseña.css'])

@section('content')
<div class="perfil-container">
    <h1>Perfil de Usuario</h1>
    
    <div class="perfil-info">
        <div class="campo">
            <label>Nombre:</label>
            <span>{{ $user->name }}</span>
        </div>
        
        <div class="campo">
            <label>Email:</label>
            <span>{{ $user->email }}</span>
        </div>

        <div class="campo">
            <label>Rol:</label>
            <span>{{ $user->rol->rol }}</span>
        </div>
        
        <div class="campo">
            <label>Fecha de creación:</label>
            <span>{{ $user->created_at->format('d/m/Y') }}</span>
        </div>

        <div class="acciones-perfil">
            <a href="{{ route('cambiarContraseña') }}" class="btn-cambiar-contrasena">
                <i class="fas fa-key"></i> Cambiar Contraseña
            </a>
        </div>
    </div>
</div>
@endsection