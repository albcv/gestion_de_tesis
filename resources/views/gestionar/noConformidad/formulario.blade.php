@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($nc) && $nc !== null;
    $accion    = $esEdicion ? route('modificarNoConformidades') : route('agregarNoConformidades');
    $titulo    = $esEdicion ? 'Editar No Conformidad' : 'Crear No Conformidad';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarNoConformidades') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_nc" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $nc->idNoConformidades }}">
                @endif

                <div class="seccion-formulario">
                    <h3>Información de la No Conformidad</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="deficiencias_detectadas">Deficiencias Detectadas *</label>
                            <textarea id="deficiencias_detectadas"
                                      name="deficiencias_detectadas"
                                      class="atributo @error('deficiencias_detectadas') atributo-error @enderror"
                                      required
                                      minlength="10"
                                      maxlength="500"
                                      rows="6"
                                      placeholder="Describa detalladamente las deficiencias detectadas...">{{ old('deficiencias_detectadas', $esEdicion ? $nc->Deficiencias_detectadas : '') }}</textarea>
                            @error('deficiencias_detectadas')
                                <small class="mensaje-error">{{ $message }}</small>
                            @enderror
                            <small class="ayuda-campo">Entre 10 y 500 caracteres</small>
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
                            💾 Actualizar No Conformidad
                        </button>
                    @endif

                    <a href="{{ route('gestionarNoConformidades') }}" class="btn-cancelar">Cancelar</a>
                </div>
            </form>
        </div>

    </div>
</div>

@endsection