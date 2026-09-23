@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($rol) && $rol !== null;
    $accion    = $esEdicion ? route('modificarRol') : route('agregarRol');
    $titulo    = $esEdicion ? 'Editar Rol' : 'Crear Rol';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarRoles') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_rol" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $rol->id }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información del Rol</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="rol">Nombre del Rol *</label>
                            <input type="text"
                                   id="rol"
                                   name="rol"
                                   class="atributo @error('rol') atributo-error @enderror"
                                   required
                                   minlength="3"
                                   maxlength="120"
                                   autocomplete="off"
                                   placeholder="Ej: Administrador"
                                   value="{{ old('rol', $esEdicion ? $rol->rol : '') }}">
                            @error('rol')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 3 y 120 caracteres</small>
                        </div>
                    </div>
                </div>

                <div class="seccion-formulario">
                    <h3>Permisos del Rol</h3>

                    @php
                        $permisosSeleccionados = old('permisos',
                            $esEdicion ? $rol->permisos->pluck('id')->toArray() : []
                        );
                    @endphp

                    @if ($permisos->count() > 0)
                        <div class="permisos-grid">
                            @foreach ($permisos as $permiso)
                                <label class="permiso-item">
                                    <input type="checkbox"
                                           name="permisos[]"
                                           value="{{ $permiso->id }}"
                                           class="permiso-checkbox"
                                           {{ in_array($permiso->id, $permisosSeleccionados) ? 'checked' : '' }}>
                                    <span class="permiso-label">{{ $permiso->permiso }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="sin-permisos-msg">No hay permisos registrados en el sistema.</p>
                    @endif
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
                            💾 Actualizar Rol
                        </button>
                    @endif

                    <a href="{{ route('gestionarRoles') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection