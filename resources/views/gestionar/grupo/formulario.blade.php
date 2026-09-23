@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($grupo) && $grupo !== null;
    $accion    = $esEdicion ? route('modificarGrupo') : route('agregarGrupo');
    $titulo    = $esEdicion ? 'Editar Grupo' : 'Crear Grupo';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarGrupos') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_grupo" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $grupo->id }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información del Grupo</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="número">Número del Grupo *</label>
                            <input type="number"
                                   id="número"
                                   name="número"
                                   class="atributo @error('número') atributo-error @enderror"
                                   required
                                   min="1"
                                   max="999"
                                   autocomplete="off"
                                   placeholder="Ej: 301"
                                   value="{{ old('número', $esEdicion ? $grupo->número : '') }}">
                            @error('número')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 1 y 999</small>
                        </div>
                    </div>
                </div>

                <div class="seccion-acciones">

                    @if (!$esEdicion)
                        <button type="submit"
                                name="accion"
                                value="guardar"
                                class="btn-guardar">
                            💾 Crear
                        </button>

                        <button type="submit"
                                name="accion"
                                value="continuar"
                                class="btn-guardar btn-continuar">
                            ➕ Crear y continuar
                        </button>
                    @else
                        <button type="submit"
                                name="accion"
                                value="guardar"
                                class="btn-guardar">
                            💾 Actualizar Grupo
                        </button>
                    @endif

                    <a href="{{ route('gestionarGrupos') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection