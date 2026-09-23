@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($tesis) && $tesis !== null;
    $accion    = $esEdicion ? route('modificarTesis') : route('agregarTesis');
    $titulo    = $esEdicion ? 'Editar Tesis' : 'Crear Tesis';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarTesis') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_tesis" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $tesis->id }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información de la Tesis</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="nombre_tesis">Nombre del Trabajo de Diploma *</label>
                            <input type="text"
                                   id="nombre_tesis"
                                   name="nombre_tesis"
                                   class="atributo @error('nombre_tesis') atributo-error @enderror"
                                   required
                                   minlength="10"
                                   maxlength="300"
                                   autocomplete="off"
                                   placeholder="Ingrese el nombre completo de la tesis"
                                   value="{{ old('nombre_tesis', $esEdicion ? $tesis->Nombre_trabajo : '') }}">
                            @error('nombre_tesis')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 10 y 500 caracteres</small>
                        </div>

                        <div class="campo-formulario">
                            <label for="id_estudiante">Estudiante Asignado *</label>
                            <select id="id_estudiante" name="id_estudiante"
                                    class="atributo @error('id_estudiante') atributo-error @enderror"
                                    required>
                                <option value="">Seleccione un estudiante</option>
                                @foreach($estudiantes as $estudiante)
                                    <option value="{{ $estudiante->id }}"
                                        {{ old('id_estudiante', $esEdicion ? $tesis->id_estudiante : '') == $estudiante->id ? 'selected' : '' }}>
                                        {{ $estudiante->Nombre_estudiante }}
                                        {{ $estudiante->Apellido1 }}
                                        {{ $estudiante->Apellido2 }}
                                        (CI: {{ $estudiante->CI_estudiante }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_estudiante')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
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
                            💾 Actualizar Tesis
                        </button>
                    @endif

                    <a href="{{ route('gestionarTesis') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection