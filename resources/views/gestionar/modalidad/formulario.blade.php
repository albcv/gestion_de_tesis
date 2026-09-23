@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($modalidad) && $modalidad !== null;
    $accion    = $esEdicion ? route('modificarModalidad') : route('agregarModalidad');
    $titulo    = $esEdicion ? 'Editar Modalidad' : 'Crear Modalidad';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarModalidad') }}" class="btn-volver">← Volver a la lista</a>
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
            <form action="{{ $accion }}" id="formulario_modalidad" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $modalidad->idModalidad }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información de la Modalidad</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="nombre_modalidad">Nombre de la Modalidad *</label>
                            <input type="text"
                                   id="nombre_modalidad"
                                   list="modalidades"
                                   name="nombre_modalidad"
                                   class="atributo @error('nombre_modalidad') atributo-error @enderror"
                                   required
                                   minlength="10"
                                   maxlength="50"
                                   autocomplete="off"
                                   placeholder="Ej: Curso regular diurno"
                                   value="{{ old('nombre_modalidad', $esEdicion ? $modalidad->Nombre_modalidad : '') }}">
                            <datalist id="modalidades">
                                <option value="Curso regular diurno"></option>
                                <option value="Curso por encuentro"></option>
                                <option value="Curso a distancia"></option>
                            </datalist>
                            @error('nombre_modalidad')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 10 y 50 caracteres</small>
                        </div>
                    </div>
                </div>

                <div class="seccion-acciones">

                    @if (!$esEdicion)
                        {{-- Modo CREAR: dos botones --}}
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
                        {{-- Modo EDITAR: solo actualizar --}}
                        <button type="submit"
                                name="accion"
                                value="guardar"
                                class="btn-guardar">
                            💾 Actualizar Modalidad
                        </button>
                    @endif

                    <a href="{{ route('gestionarModalidad') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection