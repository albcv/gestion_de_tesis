@extends('layouts.app') 


@vite(['resources/css/perfil.css'])

@section('content')

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

    </div>
    


@endsection