@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/gestionar/gestionarCarrera/formularioCarrera.css'])


<div class="container-formulario">
    <h1>Agregar Nueva Carrera</h1>
    
    <div class="card-formulario">
        <form action="{{ route('agregarCarrera_post') }}" method="POST" id="formulario_carrera">
            @csrf
            
            <div class="seccion-formulario">
                <h3>Información Básica</h3>
                
                <div class="campo">
                    <label for="facultad">Facultad *</label>
                    <select id="facultad" name="facultad" class="atributo" required>
                        <option value="">Seleccione una facultad</option>
                        @foreach($facultades as $facultad)
                            <option value="{{ $facultad->idFacultad }}" 
                                {{ old('facultad') == $facultad->idFacultad ? 'selected' : '' }}>
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
                           value="{{ old('nombre_carrera') }}" required>
                    @error('nombre_carrera')
                        <span class="error-validacion">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            
            <div class="seccion-formulario">
                <h3>Modalidades de Estudio</h3>
                <p class="descripcion">Seleccione las modalidades disponibles para esta carrera</p>
                
                <div id="modalidades-container">
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
                </div>
                
                <button type="button" class="btn-agregar-modalidad" onclick="agregarModalidad()">
                    <i class="fas fa-plus"></i> Agregar Otra Modalidad
                </button>
            </div>
            
            <div class="acciones-formulario">
                <button type="submit" class="btn btn-guardar">
                    <i class="fas fa-save"></i> Guardar Carrera
                </button>
                <a href="{{ route('gestionarCarrera') }}" class="btn btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
let contadorModalidades = 1;

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
}

// Mostrar botón eliminar en el primer item si hay más de uno
document.addEventListener('DOMContentLoaded', function() {
    const items = document.querySelectorAll('.modalidad-item');
    if (items.length > 1) {
        const primerItem = items[0];
        const btnEliminar = primerItem.querySelector('.btn-eliminar-modalidad');
        if (btnEliminar) btnEliminar.style.display = 'block';
    }
});
</script>
@endsection