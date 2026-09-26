@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($historico) && $historico !== null;
    $accion    = $esEdicion ? route('modificarTesisHistorico') : route('agregarTesisHistorico');
    $titulo    = $esEdicion ? 'Editar Registro Histórico' : 'Crear Registro Histórico';
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarTesisHistorico') }}" class="btn-volver">← Volver al histórico</a>
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
            <form action="{{ $accion }}" id="formulario_historico" method="post" enctype="multipart/form-data">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $historico->id }}">
                @endif

                <!-- ============ INFORMACIÓN BÁSICA ============ -->
                <div class="seccion-formulario">
                    <h3>Información Básica</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="año">Año *</label>
                            <input type="number"
                                   id="año"
                                   name="año"
                                   class="atributo @error('año') atributo-error @enderror"
                                   required
                                   min="2000" max="2100"
                                   placeholder="Ej: {{ date('Y') }}"
                                   value="{{ old('año', $esEdicion ? $historico->año : date('Y')) }}">
                            @error('año') <small class="mensaje-error">{{ $message }}</small> @enderror
                            <small class="ayuda-campo">Entre 2000 y 2100</small>
                        </div>

                        <div class="campo-formulario">
                            <label for="nombre_estudiante">Nombre del Estudiante *</label>
                            <input type="text"
                                   id="nombre_estudiante"
                                   name="nombre_estudiante"
                                   class="atributo @error('nombre_estudiante') atributo-error @enderror"
                                   required
                                   minlength="3" maxlength="500"
                                   autocomplete="off"
                                   placeholder="Ej: Juan Pérez Gómez"
                                   value="{{ old('nombre_estudiante', $esEdicion ? $historico->nombre_estudiante : '') }}">
                            @error('nombre_estudiante') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="campo-formulario" style="grid-column: 1 / -1;">
                            <label for="nombre_tesis">Nombre de la Tesis *</label>
                            <input type="text"
                                   id="nombre_tesis"
                                   name="nombre_tesis"
                                   class="atributo @error('nombre_tesis') atributo-error @enderror"
                                   required
                                   minlength="10" maxlength="500"
                                   autocomplete="off"
                                   placeholder="Ej: Sistema de gestión para..."
                                   value="{{ old('nombre_tesis', $esEdicion ? $historico->nombre_tesis : '') }}">
                            @error('nombre_tesis') <small class="mensaje-error">{{ $message }}</small> @enderror
                            <small class="ayuda-campo">Entre 10 y 500 caracteres</small>
                        </div>
                    </div>
                </div>

                <!-- ============ DOCUMENTOS ============ -->
                <div class="seccion-formulario">
                    <h3>Documentos</h3>

                    @if ($esEdicion)
                        <p class="ayuda-campo" style="margin-bottom:15px;">
                            Si no subes nuevos archivos, se mantendrán los actuales.
                        </p>
                    @endif

                    <div class="form-grid">
                        <!-- Documento de fundamentación -->
                        <div class="campo-formulario">
                            <label for="documento_fundamentacion">
                                Documento de Fundamentación
                                @if(!$esEdicion) * @else (opcional) @endif
                            </label>
                            <input type="file"
                                   id="documento_fundamentacion"
                                   name="documento_fundamentacion"
                                   class="atributo"
                                   accept=".pdf,.doc,.docx"
                                   {{ $esEdicion ? '' : 'required' }}>
                            <small class="ayuda-campo">PDF, DOC o DOCX (máx. 10 MB)</small>

                            @if ($esEdicion && $historico->documento_fundamentacion)
                                <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <span style="font-size:13px; color:#555;">
                                        📎 Actual: {{ basename($historico->documento_fundamentacion) }}
                                    </span>
                                    <a href="{{ route('descargarFundamentacionHistorico', $historico->id) }}"
                                       class="btn-descarga-mini" style="text-decoration:none;">📥 Descargar</a>
                                </div>
                            @endif
                        </div>

                        <!-- Documento de corte -->
                        <div class="campo-formulario">
                            <label for="documento_corte">
                                Documento de Corte (opcional)
                            </label>
                            <input type="file"
                                   id="documento_corte"
                                   name="documento_corte"
                                   class="atributo"
                                   accept=".pdf,.doc,.docx">
                            <small class="ayuda-campo">PDF, DOC o DOCX (máx. 10 MB)</small>

                            @if ($esEdicion && $historico->documento_corte)
                                <div style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                    <span style="font-size:13px; color:#555;">
                                        📎 Actual: {{ basename($historico->documento_corte) }}
                                    </span>
                                    <a href="{{ route('descargarCorteHistorico', $historico->id) }}"
                                       class="btn-descarga-mini" style="text-decoration:none;">📥 Descargar</a>
                                </div>
                            @endif
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
                            💾 Actualizar Registro
                        </button>
                    @endif

                    <a href="{{ route('gestionarTesisHistorico') }}" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
    // Validación de tamaño de los archivos
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB

            if (file && file.size > maxSize) {
                alert('El archivo excede el tamaño máximo de 10MB');
                e.target.value = '';
            }
        });
    });
</script>

@endsection