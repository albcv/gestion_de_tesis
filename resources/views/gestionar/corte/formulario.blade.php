@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($corte) && $corte !== null;
    $accion    = $esEdicion ? route('modificarCorte') : route('agregarCorte');
    $titulo    = $esEdicion ? 'Editar Corte' : 'Crear Corte';

    // Etiqueta de la tesis actual (para precargar el input en modo edición)
    $tesisActualLabel = '';
    if ($esEdicion && $corte->tesis) {
        $est = $corte->tesis->estudiante;
        $nombreEst = $est
            ? trim($est->Nombre_estudiante . ' ' . $est->Apellido1)
            : 'Sin estudiante';
        $tesisActualLabel = $corte->tesis->Nombre_trabajo . ' - ' . $nombreEst;
    }
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarCortes') }}" class="btn-volver">← Volver a la lista</a>
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
            <form action="{{ $accion }}" id="formulario_corte" method="post" enctype="multipart/form-data">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                @endif

                <!-- ============ INFO CORTE ============ -->
                <div class="seccion-formulario">
                    <h3>Información del Corte</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="tesis_buscar">Tesis Asociada *</label>

                            @if(isset($tesisId) && $tesisId && !$esEdicion)
                                {{-- Preseleccionada desde "Detalles de Tesis": no se puede cambiar --}}
                                <input type="hidden" name="id_tesis" value="{{ $tesisId }}">
                                <input type="text" class="atributo" disabled
                                       value="{{ $tesisSeleccionada->Nombre_trabajo ?? 'Tesis seleccionada' }}
                                              - {{ $tesisSeleccionada->estudiante
                                                    ? trim($tesisSeleccionada->estudiante->Nombre_estudiante . ' ' . $tesisSeleccionada->estudiante->Apellido1)
                                                    : 'Sin estudiante' }}">
                                <small class="ayuda-campo">Tesis preseleccionada desde Detalles de Tesis</small>
                            @else
                                {{-- Datalist con búsqueda server-side --}}
                                <input type="text"
                                       id="tesis_buscar"
                                       class="atributo @error('id_tesis') atributo-error @enderror"
                                       list="tesis-list"
                                       placeholder="Escribe el nombre de la tesis o del estudiante..."
                                       autocomplete="off"
                                       required
                                       value="{{ old('_tesis_label', $esEdicion ? $tesisActualLabel : '') }}">

                                <datalist id="tesis-list"></datalist>

                                <input type="hidden"
                                       name="id_tesis"
                                       id="id_tesis_hidden"
                                       value="{{ old('id_tesis', $esEdicion ? $corte->id_tesis : '') }}">

                                <small class="ayuda-campo">
                                    Escribe al menos 2 caracteres para filtrar
                                </small>
                                <small id="tesis_estado" style="display:none; color:#666; font-style:italic; margin-top:4px;"></small>
                            @endif

                            @error('id_tesis') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="campo-formulario">
                            <label for="número_corte">Número de Corte *</label>
                            <select name="número_corte" id="número_corte"
                                    class="atributo @error('número_corte') atributo-error @enderror"
                                    required>
                                <option value="">Seleccione</option>
                                @for ($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}"
                                        {{ old('número_corte', $esEdicion ? $corte->Numero_corte : '') == $i ? 'selected' : '' }}>
                                        Corte {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            @error('número_corte') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <!-- ============ DOCUMENTO / VERSIONES ============ -->
                <div class="seccion-formulario">
                    <h3>Documento y Enlace</h3>

                    @if ($esEdicion && $ultimaVersion)
                        <div class="version-actual-info">
                            <p><strong>Última versión disponible:</strong> v{{ $ultimaVersion->version_numero }}</p>
                            <p><strong>Archivo:</strong> {{ $ultimaVersion->nombre_archivo }}</p>
                            @if($ultimaVersion->Enlace_Github)
                                <p><strong>Enlace GitHub:</strong>
                                    <a href="{{ $ultimaVersion->Enlace_Github }}" target="_blank">
                                        {{ $ultimaVersion->Enlace_Github }}
                                    </a>
                                </p>
                            @endif
                            <a href="{{ route('ver-documento-version-corte', $ultimaVersion->id) }}"
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
                    @endif

                    <div class="form-grid" style="margin-top:18px;">
                        <div class="campo-formulario">
                            <label for="enlace">Enlace a GitHub (opcional)</label>
                            <input type="url"
                                   id="enlace"
                                   name="enlace"
                                   class="atributo @error('enlace') atributo-error @enderror"
                                   placeholder="https://github.com/usuario/repositorio"
                                   maxlength="500"
                                   value="{{ old('enlace', $esEdicion && $ultimaVersion ? $ultimaVersion->Enlace_Github : '') }}">
                            @error('enlace') <small class="mensaje-error">{{ $message }}</small> @enderror
                            <small class="ayuda-campo">URL válida (máx. 500 caracteres)</small>
                        </div>

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
                                      placeholder="Ej: Correcciones del corte 2...">{{ old('descripcion', $esEdicion && $ultimaVersion ? $ultimaVersion->descripcion : '') }}</textarea>
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
                            💾 Actualizar Corte
                        </button>
                    @endif

                    <a href="{{ route('gestionarCortes') }}" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar  = document.getElementById('tesis_buscar');
    const datalist     = document.getElementById('tesis-list');
    const inputHidden  = document.getElementById('id_tesis_hidden');
    const estadoMsg    = document.getElementById('tesis_estado');

    if (!inputBuscar || !datalist || !inputHidden) return;

    const urlBuscar = '{{ route("buscarTesis") }}';
    const mapLabels = new Map(); // label => id
    let timeoutId = null;

    function setEstado(msg) {
        if (!estadoMsg) return;
        if (msg) {
            estadoMsg.textContent = msg;
            estadoMsg.style.display = 'inline-block';
        } else {
            estadoMsg.style.display = 'none';
        }
    }

    function cargarTesis(termino) {
        setEstado('Buscando...');

        fetch(`${urlBuscar}?q=${encodeURIComponent(termino)}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            datalist.innerHTML = '';
            mapLabels.clear();

            data.forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.label;
                datalist.appendChild(opt);
                mapLabels.set(t.label, t.id);
            });

            setEstado(data.length === 0
                ? 'Sin resultados'
                : `${data.length} tesis encontradas`);

            // Si el valor actual coincide exactamente con una opción, sincronizar hidden
            sincronizarHidden();
        })
        .catch(err => {
            console.error('Error al buscar tesis:', err);
            setEstado('Error al buscar');
        });
    }

    function sincronizarHidden() {
        const label = inputBuscar.value.trim();
        if (mapLabels.has(label)) {
            inputHidden.value = mapLabels.get(label);
        } else {
            inputHidden.value = '';
        }
    }

    inputBuscar.addEventListener('input', function () {
        clearTimeout(timeoutId);
        const termino = this.value.trim();

        // Si lo escrito coincide exactamente con una opción conocida,
        // es una selección: guardar el ID y no volver a buscar.
        if (mapLabels.has(termino)) {
            inputHidden.value = mapLabels.get(termino);
            setEstado('');
            return;
        }

        // Si borró todo, limpiar el ID y buscar los primeros
        if (termino.length === 0) {
            inputHidden.value = '';
        }

        // Pequeño debounce para no saturar el servidor
        timeoutId = setTimeout(() => {
            cargarTesis(termino);
        }, 250);
    });

    // Al perder el foco, sincronizar por si eligió de la lista
    inputBuscar.addEventListener('blur', sincronizarHidden);

    // Al seleccionar del datalist (algunos navegadores disparan "change")
    inputBuscar.addEventListener('change', sincronizarHidden);

    // Cargar las primeras opciones al abrir el formulario
    // (en modo edición ya tenemos un label precargado, pero igual refrescamos la lista)
    cargarTesis(inputBuscar.value.trim());
});
</script>

@endsection