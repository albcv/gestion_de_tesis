@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($departamento) && $departamento !== null;
    $accion    = $esEdicion ? route('modificarDepartamento') : route('agregarDepartamento');
    $titulo    = $esEdicion ? 'Editar Departamento' : 'Crear Departamento';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarDepartamento') }}" class="btn-volver">← Volver a la lista</a>
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
            <form action="{{ $accion }}" id="formulario_departamento" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $departamento->idDepartamento }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información del Departamento</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="departamento">Nombre del Departamento *</label>
                            <input type="text"
                                   id="departamento"
                                   name="departamento"
                                   class="atributo @error('departamento') atributo-error @enderror"
                                   required
                                   minlength="10"
                                   maxlength="100"
                                   autocomplete="off"
                                   placeholder="Ej: Departamento de Informática"
                                   value="{{ old('departamento', $esEdicion ? $departamento->Nombre_departamento : '') }}">
                            @error('departamento')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 10 y 100 caracteres</small>
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
                            💾 Actualizar Departamento
                        </button>
                    @endif

                    <a href="{{ route('gestionarDepartamento') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection