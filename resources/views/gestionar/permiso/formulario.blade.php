@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($permiso) && $permiso !== null;
    $accion    = $esEdicion ? route('modificarPermiso') : route('agregarPermiso');
    $titulo    = $esEdicion ? 'Editar Permiso' : 'Crear Permiso';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarPermisos') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_permiso" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $permiso->id }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información del Permiso</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="permiso">Nombre del Permiso *</label>
                            <input type="text"
                                   id="permiso"
                                   name="permiso"
                                   class="atributo @error('permiso') atributo-error @enderror"
                                   required
                                   minlength="3"
                                   maxlength="120"
                                   autocomplete="off"
                                   placeholder="Ej: gestionarFacultad"
                                   value="{{ old('permiso', $esEdicion ? $permiso->permiso : '') }}">
                            @error('permiso')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 3 y 120 caracteres</small>
                        </div>
                    </div>
                </div>

                <div class="seccion-acciones">

                    @if (!$esEdicion)
                        <button type="submit" name="accion" value="guardar" class="btn-guardar">
                            💾 Crear
                        </button>
                        <button type="submit" name="accion" value="continuar" class="btn-guardar btn-continuar">
                            ➕ Crear y continuar
                        </button>
                    @else
                        <button type="submit" name="accion" value="guardar" class="btn-guardar">
                            💾 Actualizar Permiso
                        </button>
                    @endif

                    <a href="{{ route('gestionarPermisos') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection