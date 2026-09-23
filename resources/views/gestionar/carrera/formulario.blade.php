@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($carrera) && $carrera !== null;
    $accion    = $esEdicion ? route('modificarCarrera') : route('agregarCarrera');
    $titulo    = $esEdicion ? 'Editar Carrera' : 'Crear Carrera';

    // Construir la lista inicial de modalidades
    $modalidadesIniciales = [];
    if ($esEdicion) {
        foreach ($carrera->modalidades as $m) {
            $modalidadesIniciales[] = [
                'id'    => $m->idModalidad,
                'years' => $m->pivot->cantidad_years ?? '',
            ];
        }
    } elseif (old('modalidades')) {
        foreach (old('modalidades') as $m) {
            $modalidadesIniciales[] = [
                'id'    => $m['id'] ?? '',
                'years' => $m['years'] ?? '',
            ];
        }
    }

    if (count($modalidadesIniciales) === 0) {
        $modalidadesIniciales[] = ['id' => '', 'years' => ''];
    }

    $contador = count($modalidadesIniciales);
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarCarrera') }}" class="btn-volver">← Volver a la lista</a>
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
            <form action="{{ $accion }}" id="formulario_carrera" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $carrera->id }}">
                @endif

                <!-- ============ INFO BÁSICA ============ -->
                <div class="seccion-formulario">
                    <h3>Información Básica</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="facultad">Facultad *</label>
                            <select id="facultad" name="facultad"
                                    class="atributo @error('facultad') atributo-error @enderror"
                                    required>
                                <option value="">Seleccione una facultad</option>
                                @foreach ($facultades as $facultad)
                                    <option value="{{ $facultad->idFacultad }}"
                                        {{ old('facultad', $esEdicion ? $carrera->id_facultad : '') == $facultad->idFacultad ? 'selected' : '' }}>
                                        {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                                    </option>
                                @endforeach
                            </select>
                            @error('facultad') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="campo-formulario">
                            <label for="nombre_carrera">Nombre de la Carrera *</label>
                            <input type="text"
                                   id="nombre_carrera"
                                   name="nombre_carrera"
                                   class="atributo @error('nombre_carrera') atributo-error @enderror"
                                   required minlength="10" maxlength="80"
                                   autocomplete="off"
                                   placeholder="Ej: Ingeniería Informática"
                                   value="{{ old('nombre_carrera', $esEdicion ? $carrera->Nombre_carrera : '') }}">
                            @error('nombre_carrera') <small class="mensaje-error">{{ $message }}</small> @enderror
                            <small class="ayuda-campo">Entre 10 y 80 caracteres</small>
                        </div>
                    </div>
                </div>

                <!-- ============ MODALIDADES ============ -->
                <div class="seccion-formulario">
                    <h3>Modalidades de Estudio</h3>
                    <p class="ayuda-campo" style="margin-bottom:15px;">
                        Seleccione las modalidades disponibles para esta carrera y su duración en años.
                    </p>

                    <div id="modalidades-container">
                        @foreach ($modalidadesIniciales as $index => $mi)
                            <div class="modalidad-item" data-index="{{ $index }}">
                                <div class="row-modalidad">
                                    <div class="campo-modalidad">
                                        <label>Modalidad</label>
                                        <select name="modalidades[{{ $index }}][id]" class="select-modalidad">
                                            <option value="">Seleccione</option>
                                            @foreach ($modalidades as $m)
                                                <option value="{{ $m->idModalidad }}"
                                                    {{ $mi['id'] == $m->idModalidad ? 'selected' : '' }}>
                                                    {{ $m->Nombre_modalidad }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="campo-modalidad">
                                        <label>Duración (años)</label>
                                        <input type="number"
                                               name="modalidades[{{ $index }}][years]"
                                               class="input-duracion"
                                               min="1" max="10"
                                               placeholder="Ej: 5"
                                               value="{{ $mi['years'] }}">
                                    </div>
                                    <div class="acciones-modalidad">
                                        <button type="button"
                                                class="btn-eliminar-modalidad"
                                                onclick="eliminarModalidad(this)"
                                                {{ count($modalidadesIniciales) === 1 ? 'style=display:none' : '' }}>
                                            🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn-agregar-modalidad" onclick="agregarModalidad()">
                        ➕ Agregar otra modalidad
                    </button>
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
                            💾 Actualizar Carrera
                        </button>
                    @endif

                    <a href="{{ route('gestionarCarrera') }}" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
let contadorModalidades = {{ $contador }};

function agregarModalidad() {
    const container = document.getElementById('modalidades-container');
    const item = document.createElement('div');
    item.className = 'modalidad-item';
    item.dataset.index = contadorModalidades;

    item.innerHTML = `
        <div class="row-modalidad">
            <div class="campo-modalidad">
                <label>Modalidad</label>
                <select name="modalidades[${contadorModalidades}][id]" class="select-modalidad">
                    <option value="">Seleccione</option>
                    @foreach ($modalidades as $m)
                        <option value="{{ $m->idModalidad }}">{{ $m->Nombre_modalidad }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo-modalidad">
                <label>Duración (años)</label>
                <input type="number" name="modalidades[${contadorModalidades}][years]"
                       class="input-duracion" min="1" max="10" placeholder="Ej: 5">
            </div>
            <div class="acciones-modalidad">
                <button type="button" class="btn-eliminar-modalidad" onclick="eliminarModalidad(this)">🗑️</button>
            </div>
        </div>
    `;

    container.appendChild(item);
    contadorModalidades++;
    actualizarBotonesEliminar();
}

function eliminarModalidad(button) {
    const item = button.closest('.modalidad-item');
    item.remove();
    reorganizarIndices();
    actualizarBotonesEliminar();
}

function reorganizarIndices() {
    const items = document.querySelectorAll('.modalidad-item');
    items.forEach((item, index) => {
        item.dataset.index = index;
        item.querySelectorAll('[name*="modalidades"]').forEach(el => {
            el.name = el.name.replace(/\[\d+\]/, `[${index}]`);
        });
    });
    contadorModalidades = items.length;
}

function actualizarBotonesEliminar() {
    const items = document.querySelectorAll('.modalidad-item');
    const mostrar = items.length > 1;
    items.forEach(item => {
        const btn = item.querySelector('.btn-eliminar-modalidad');
        if (btn) btn.style.display = mostrar ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', actualizarBotonesEliminar);
</script>

@endsection