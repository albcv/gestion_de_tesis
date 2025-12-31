@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarCarrera/formularioCarrera.css') }}">

<div class="container-formulario">
    <h1>Editar Carrera</h1>
    
    <div class="card-formulario">
        <form action="{{ route('modificarCarrera') }}" method="POST" id="formulario_carrera">
            @csrf
            <input type="hidden" name="id" value="{{ $carrera->id }}">
            
            <div class="seccion-formulario">
                <h3>Información Básica</h3>
                
                <div class="campo">
                    <label for="facultad">Facultad *</label>
                    <select id="facultad" name="facultad" class="atributo" required>
                        <option value="">Seleccione una facultad</option>
                        @foreach($facultades as $facultad)
                            <option value="{{ $facultad->idFacultad }}" 
                                {{ (old('facultad') ?: $carrera->id_facultad) == $facultad->idFacultad ? 'selected' : '' }}>
                                {{ $facultad->Siglas }} - {{ $facultad->Nombre_facultad }}
                            </option>
                        @endforeach
                    </select>
                    @error('facultad')
                        <span class="error-validacion">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="campo">
                    <label for="nombre_carrera">Nombre de la Carrera *</label>
                    <input type="text" id="nombre_carrera" name="nombre_carrera" 
                           class="atributo" minlength="10" maxlength="80" 
                           value="{{ old('nombre_carrera', $carrera->Nombre_carrera) }}" required>
                    @error('nombre_carrera')
                        <span class="error-validacion">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="seccion-formulario">
                <h3>Modalidades de Estudio</h3>
                <p class="descripcion">Seleccione las modalidades disponibles para esta carrera</p>
                
                <div id="modalidades-container">
                    @php
                        $modalidadesCarrera = $carrera->modalidades->keyBy('idModalidad');
                        $contador = 0;
                    @endphp
                    
                    @if($carrera->modalidades->count() > 0)
                        @foreach($carrera->modalidades as $modalidad)
                        <div class="modalidad-item" data-index="{{ $contador }}">
                            <div class="row-modalidad">
                                <div class="campo-modalidad">
                                    <label>Modalidad</label>
                                    <select name="modalidades[{{ $contador }}][id]" class="select-modalidad">
                                        <option value="">Seleccione modalidad</option>
                                        @foreach($modalidades as $m)
                                            <option value="{{ $m->idModalidad }}" 
                                                {{ $m->idModalidad == $modalidad->idModalidad ? 'selected' : '' }}>
                                                {{ $m->Nombre_modalidad }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="campo-modalidad">
                                    <label>Duración (años)</label>
                                    <input type="number" name="modalidades[{{ $contador }}][years]" 
                                           class="input-duracion" min="1" max="10" 
                                           value="{{ $modalidad->pivot->cantidad_years }}" 
                                           placeholder="Ej: 5">
                                </div>
                                <div class="acciones-modalidad">
                                    <button type="button" class="btn-eliminar-modalidad" 
                                            onclick="eliminarModalidad(this)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @php $contador++; @endphp
                        @endforeach
                    @else
                        <div class="modalidad-item" data-index="0">
                            <div class="row-modalidad">
                                <div class="campo-modalidad">
                                    <label>Modalidad</label>
                                    <select name="modalidades[0][id]" class="select-modalidad">
                                        <option value="">Seleccione modalidad</option>
                                        @foreach($modalidades as $modalidad)
                                            <option value="{{ $modalidad->idModalidad }}">
                                                {{ $modalidad->Nombre_modalidad }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="campo-modalidad">
                                    <label>Duración (años)</label>
                                    <input type="number" name="modalidades[0][years]" 
                                           class="input-duracion" min="1" max="10" 
                                           placeholder="Ej: 5">
                                </div>
                                <div class="acciones-modalidad">
                                    <button type="button" class="btn-eliminar-modalidad" 
                                            onclick="eliminarModalidad(this)" style="display: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @php $contador = 1; @endphp
                    @endif
                </div>
                
                <button type="button" class="btn-agregar-modalidad" onclick="agregarModalidad()">
                    <i class="fas fa-plus"></i> Agregar Otra Modalidad
                </button>
            </div>
            
            <div class="acciones-formulario">
                <button type="submit" class="btn btn-guardar">
                    <i class="fas fa-save"></i> Actualizar Carrera
                </button>
                <a href="{{ route('gestionarCarrera') }}" class="btn btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <a href="{{ route('verCarrera', $carrera->id) }}" 
                   class="btn btn-ver">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let contadorModalidades = {{ $contador }};

function agregarModalidad() {
    const container = document.getElementById('modalidades-container');
    const nuevoItem = document.createElement('div');
    nuevoItem.className = 'modalidad-item';
    nuevoItem.dataset.index = contadorModalidades;
    
    nuevoItem.innerHTML = `
        <div class="row-modalidad">
            <div class="campo-modalidad">
                <label>Modalidad</label>
                <select name="modalidades[${contadorModalidades}][id]" class="select-modalidad">
                    <option value="">Seleccione modalidad</option>
                    @foreach($modalidades as $modalidad)
                        <option value="{{ $modalidad->idModalidad }}">
                            {{ $modalidad->Nombre_modalidad }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="campo-modalidad">
                <label>Duración (años)</label>
                <input type="number" name="modalidades[${contadorModalidades}][years]" 
                       class="input-duracion" min="1" max="10" 
                       placeholder="Ej: 5">
            </div>
            <div class="acciones-modalidad">
                <button type="button" class="btn-eliminar-modalidad" onclick="eliminarModalidad(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.appendChild(nuevoItem);
    contadorModalidades++;
    
    // Mostrar botón eliminar en el primer item si estaba oculto
    const items = document.querySelectorAll('.modalidad-item');
    if (items.length > 1) {
        const primerItem = items[0];
        const btnEliminar = primerItem.querySelector('.btn-eliminar-modalidad');
        if (btnEliminar) btnEliminar.style.display = 'block';
    }
}

function eliminarModalidad(button) {
    const item = button.closest('.modalidad-item');
    item.remove();
    reorganizarIndices();
}

function reorganizarIndices() {
    const items = document.querySelectorAll('.modalidad-item');
    items.forEach((item, index) => {
        item.dataset.index = index;
        const selects = item.querySelectorAll('[name*="modalidades"]');
        selects.forEach(select => {
            const name = select.name;
            select.name = name.replace(/\[\d+\]/, `[${index}]`);
        });
    });
    contadorModalidades = items.length;
    
    // Ocultar botón eliminar si solo queda un item
    if (items.length <= 1) {
        const primerItem = items[0];
        const btnEliminar = primerItem.querySelector('.btn-eliminar-modalidad');
        if (btnEliminar) btnEliminar.style.display = 'none';
    }
}
</script>
@endsection