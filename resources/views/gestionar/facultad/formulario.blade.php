@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($facultad) && $facultad !== null;
    $accion    = $esEdicion ? route('modificarFacultad') : route('agregarFacultad');
    $titulo    = $esEdicion ? 'Editar Facultad' : 'Crear Facultad';
    $textoBtn  = $esEdicion ? '💾 Actualizar Facultad' : '+ Crear Facultad';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarFacultad') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        {{-- Mensaje de error general (duplicados, excepciones) --}}
        @if (session('error'))
            <div class="alerta alerta-error">
                {{ session('error') }}
            </div>
        @endif

        {{-- Mensaje de éxito (por si llega por redirección) --}}
        @if (session('success'))
            <div class="alerta alerta-exito">
                {{ session('success') }}
            </div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_facultad" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $facultad->idFacultad }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información de la Facultad</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="nombre_facultad">Nombre de la Facultad *</label>
                            <input type="text"
                                   id="nombre_facultad"
                                   list="facultad"
                                   name="nombre_facultad"
                                   class="atributo @error('nombre_facultad') atributo-error @enderror"
                                   required
                                   minlength="20"
                                   maxlength="100"
                                   autocomplete="off"
                                   placeholder="Facultad de Ciencias..."
                                   value="{{ old('nombre_facultad', $esEdicion ? $facultad->Nombre_facultad : '') }}">
                            <datalist id="facultad">
                                <option value="Facultad de Ciencias Agropecuarias"></option>
                                <option value="Facultad de Ciencias Técnicas"></option>
                                <option value="Facultad de Ciencias Económicas y Empresariales"></option>
                                <option value="Facultad de Ciencias Informáticas"></option>
                                <option value="Facultad de Ciencias Sociales y Humanísticas"></option>
                                <option value="Facultad de Ciencias Pedagógicas"></option>
                                <option value="Facultad de Ciencias de la Cultura Física y el Deporte"></option>
                            </datalist>
                            @error('nombre_facultad')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 20 y 100 caracteres</small>
                        </div>

                        <div class="campo-formulario">
                            <label for="siglas">Siglas *</label>
                            <input type="text"
                                   id="siglas"
                                   list="list_siglas"
                                   name="siglas"
                                   class="atributo @error('siglas') atributo-error @enderror"
                                   required
                                   minlength="3"
                                   maxlength="10"
                                   autocomplete="off"
                                   placeholder="Ej: FCI"
                                   value="{{ old('siglas', $esEdicion ? $facultad->Siglas : '') }}">
                            <datalist id="list_siglas">
                                <option value="FCA"></option>
                                <option value="FCT"></option>
                                <option value="FCEE"></option>
                                <option value="FICE"></option>
                                <option value="FCSH"></option>
                                <option value="FCP"></option>
                                <option value="FCCFD"></option>
                            </datalist>
                            @error('siglas')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 3 y 10 caracteres</small>
                        </div>
                    </div>
                </div>

                <div class="seccion-acciones">
                    <button type="submit" class="btn-guardar">{{ $textoBtn }}</button>
                    <a href="{{ route('gestionarFacultad') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection