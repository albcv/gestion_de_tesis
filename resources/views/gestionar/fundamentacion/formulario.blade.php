@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($fundamentacion) && $fundamentacion !== null;
    $accion    = $esEdicion ? route('modificarFundamentación') : route('agregarFundamentación');
    $titulo    = $esEdicion ? 'Editar Fundamentación' : 'Crear Fundamentación';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarFundamentaciones') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alerta alerta-error">
                <ul style="margin:0; padding-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_fundamentacion" method="post"
                  enctype="multipart/form-data">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
                @endif

                <!-- ============ TESIS ============ -->
                <div class="seccion-formulario">
                    <h3>Información de la Tesis</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="id_tesis">Tesis Asociada *</label>
                            <select id="id_tesis" name="id_tesis"
                                    class="atributo @error('id_tesis') atributo-error @enderror"
                                    required>
                                <option value="">Seleccione una tesis</option>
                                @foreach ($tesis as $tesisItem)
                                    <option value="{{ $tesisItem->id }}"
                                        {{ old('id_tesis', $esEdicion ? $fundamentacion->id_tesis : (isset($tesisSeleccionada) ? $tesisSeleccionada->id : '')) == $tesisItem->id ? 'selected' : '' }}>
                                        Tesis #{{ $tesisItem->id }}: {{ $tesisItem->Nombre_trabajo }}
                                        @if ($tesisItem->estudiante)
                                            - {{ $tesisItem->estudiante->Nombre_estudiante }} {{ $tesisItem->estudiante->Apellido1 }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tesis') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <!-- ============ DOCUMENTO / VERSIONES ============ -->
                <div class="seccion-formulario">
                    <h3>Documento de la Fundamentación</h3>

                    @if ($esEdicion && $ultimaVersion)
                        <div class="version-actual-info">
                            <p><strong>Última versión disponible:</strong> v{{ $ultimaVersion->version_numero }}</p>
                            <p><strong>Archivo:</strong> {{ $ultimaVersion->nombre_archivo }}</p>
                            <p><strong>Tamaño:</strong>
                                @if($ultimaVersion->tamanio >= 1048576)
                                    {{ number_format($ultimaVersion->tamanio / 1048576, 2) }} MB
                                @elseif($ultimaVersion->tamanio >= 1024)
                                    {{ number_format($ultimaVersion->tamanio / 1024, 2) }} KB
                                @else
                                    {{ $ultimaVersion->tamanio }} bytes
                                @endif
                            </p>
                            <a href="{{ route('ver-documento-version', $ultimaVersion->id) }}"
                               class="btn-descarga-mini" target="_blank">
                                📥 Descargar versión actual
                            </a>
                        </div>

                        <div class="opciones-version">
                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="accion_version" value="actualizar" checked>
                                    <span class="radio-text">
                                        Reemplazar la versión actual (v{{ $ultimaVersion->version_numero }})
                                    </span>
                                    <input type="hidden" name="version_id" value="{{ $ultimaVersion->id }}">
                                </label>
                            </div>

                            <div class="radio-group">
                                <label class="radio-label">
                                    <input type="radio" name="accion_version" value="crear">
                                    <span class="radio-text">
                                        Crear nueva versión (v{{ $ultimaVersion->version_numero + 1 }})
                                    </span>
                                </label>
                            </div>
                        </div>
                    @elseif ($esEdicion && !$ultimaVersion)
                        <div class="alerta alerta-error">
                            ⚠️ Esta fundamentación no tiene versiones aún. Se creará la v1 al subir un archivo.
                        </div>
                        <input type="hidden" name="accion_version" value="crear">
                    @else
                        <p class="ayuda-campo">Al guardar, el archivo se registrará como <strong>versión 1</strong> de la fundamentación.</p>
                    @endif

                    <div class="form-grid" style="margin-top:18px;">
                        <div class="campo-formulario">
                            <label for="documento">
                                Documento
                                @if(!$esEdicion) * @else (opcional) @endif
                            </label>
                            <input type="file"
                                   id="documento"
                                   name="documento"
                                   class="atributo"
                                   accept=".pdf,.doc,.docx"
                                   {{ $esEdicion ? '' : 'required' }}>
                            <small class="ayuda-campo">PDF, DOC o DOCX (máx. 10 MB)</small>
                        </div>

                        <div class="campo-formulario">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea id="descripcion"
                                      name="descripcion"
                                      class="atributo"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Ej: Versión inicial de la fundamentación...">{{ old('descripcion', $esEdicion && $ultimaVersion ? $ultimaVersion->descripcion : '') }}</textarea>
                            <small class="ayuda-campo">Máx. 500 caracteres</small>
                        </div>
                    </div>
                </div>

                <!-- ============ ACCIONES ============ -->
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
                            💾 Actualizar Fundamentación
                        </button>
                    @endif

                    <a href="{{ route('gestionarFundamentaciones') }}" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</div>

@endsection