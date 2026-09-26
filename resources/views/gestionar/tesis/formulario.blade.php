@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($tesis) && $tesis !== null;
    $accion    = $esEdicion ? route('modificarTesis') : route('agregarTesis');
    $titulo    = $esEdicion ? 'Editar Tesis' : 'Crear Tesis';

    // Etiqueta del estudiante actual (para precargar en modo edición)
    $estudianteActualLabel = '';
    if ($esEdicion && $tesis->estudiante) {
        $e = $tesis->estudiante;
        $nombre = trim($e->Nombre_estudiante . ' ' . $e->Apellido1 . ' ' . ($e->Apellido2 ?? ''));
        $estudianteActualLabel = $nombre . ' (CI: ' . $e->CI_estudiante . ')';
    }
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
                            <small class="ayuda-campo">Entre 10 y 300 caracteres</small>
                        </div>

                        <div class="campo-formulario">
                            <label for="estudiante_buscar">Estudiante Asignado *</label>

                            <input type="text"
                                   id="estudiante_buscar"
                                   class="atributo @error('id_estudiante') atributo-error @enderror"
                                   list="estudiante-list"
                                   placeholder="Escribe nombre, apellidos o CI..."
                                   autocomplete="off"
                                   required
                                   value="{{ old('_estudiante_label', $esEdicion ? $estudianteActualLabel : '') }}">

                            <datalist id="estudiante-list"></datalist>

                            <input type="hidden"
                                   name="id_estudiante"
                                   id="id_estudiante_hidden"
                                   value="{{ old('id_estudiante', $esEdicion ? $tesis->id_estudiante : '') }}">

                            <small class="ayuda-campo">
                                Escribe al menos 2 caracteres. Solo estudiantes sin tesis asignada.
                            </small>
                            <small id="estudiante_estado" style="display:none; color:#666; font-style:italic; margin-top:4px;"></small>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar  = document.getElementById('estudiante_buscar');
    const datalist     = document.getElementById('estudiante-list');
    const inputHidden  = document.getElementById('id_estudiante_hidden');
    const estadoMsg    = document.getElementById('estudiante_estado');

    if (!inputBuscar || !datalist || !inputHidden) return;

    const urlBuscar = '{{ route("buscarEstudiantes") }}';
    const idActual  = inputHidden.value || '';
    const mapLabels = new Map();
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

    function cargarEstudiantes(termino) {
        setEstado('Buscando...');

        const url = `${urlBuscar}?q=${encodeURIComponent(termino)}&id_actual=${encodeURIComponent(idActual)}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            datalist.innerHTML = '';
            mapLabels.clear();

            data.forEach(e => {
                const opt = document.createElement('option');
                opt.value = e.label;
                datalist.appendChild(opt);
                mapLabels.set(e.label, e.id);
            });

            setEstado(data.length === 0
                ? 'Sin estudiantes disponibles'
                : `${data.length} estudiante(s) encontrado(s)`);

            sincronizarHidden();
        })
        .catch(err => {
            console.error('Error al buscar estudiantes:', err);
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

        if (mapLabels.has(termino)) {
            inputHidden.value = mapLabels.get(termino);
            setEstado('');
            return;
        }

        if (termino.length === 0) {
            inputHidden.value = '';
        }

        timeoutId = setTimeout(() => {
            cargarEstudiantes(termino);
        }, 250);
    });

    inputBuscar.addEventListener('blur', sincronizarHidden);
    inputBuscar.addEventListener('change', sincronizarHidden);

    // Cargar opciones iniciales
    cargarEstudiantes('');
});
</script>

@endsection