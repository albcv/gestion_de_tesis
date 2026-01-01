@extends('layouts.app')

@section('content')


@vite(['resources/css/gestionar/gestionarCortes/crearEditarCorte.css'])
@vite(['resources/js/gestionarCortes/validarCorte.js'])



<h1>Crear Corte de Tesis</h1>

<form action="{{ route('agregarCorte') }}" method="post" id="formulario_corte" enctype="multipart/form-data">
@csrf

@if(isset($tesisId) && $tesisId)
    <input type="hidden" name="id_tesis" value="{{ $tesisId }}">
    <div class="campo">
        <label>Tesis (Seleccionada automáticamente desde detalles de tesis):</label>
        <div class="tesis-info">
            <strong>Información de la tesis:</strong><br>
            @if(isset($tesisSeleccionada))
                Tesis #{{ $tesisSeleccionada->id }}: {{ $tesisSeleccionada->Nombre_trabajo }}<br>
                Estudiante: {{ $tesisSeleccionada->estudiante->Nombre_estudiante }} {{ $tesisSeleccionada->estudiante->Apellido1 }}
            @endif
        </div>
    </div>
@else
    <div class="select" id="campo_id_tesis">
        <label for="id_tesis">Tesis</label>
        <select name="id_tesis" id="id_tesis" class="atributo" required>
            <option value=""></option>
            @foreach ($tesis as $tesisItem)
                <option value="{{ $tesisItem->id }}" {{ old('id_tesis') == $tesisItem->id ? 'selected' : '' }}>
                    Tesis #{{ $tesisItem->id }}: {{ $tesisItem->Nombre_trabajo }} - 
                    @if ($tesisItem->estudiante)
                        {{ $tesisItem->estudiante->Nombre_estudiante }} {{ $tesisItem->estudiante->Apellido1 }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>
@endif

<div class="select" id="campo_número_corte">
    <label for="número_corte">Número de Corte</label>
    <select name="número_corte" id="número_corte" class="atributo" required>
        <option value=""></option>
        <option value="1" {{ old('número_corte') == 1 ? 'selected' : '' }}>Corte 1</option>
        <option value="2" {{ old('número_corte') == 2 ? 'selected' : '' }}>Corte 2</option>
        <option value="3" {{ old('número_corte') == 3 ? 'selected' : '' }}>Corte 3</option>
        <option value="4" {{ old('número_corte') == 4 ? 'selected' : '' }}>Corte 4</option>
    </select>
</div>

<!-- Sección de versión -->
<div class="seccion-version">
    <h3>Versión 1 del Corte</h3>
    
    <div class="campo" id="campo_enlace">
        <label for="enlace">Enlace a GitHub</label>
        <input type="url" id="enlace" name="enlace" class="atributo" 
               placeholder="https://github.com/usuario/repositorio" 
               value="{{ old('enlace') }}" 
               pattern="https?://.+">
        <small class="mensaje-info">Debe ser una URL válida (ej: https://github.com/usuario/repositorio)</small>
    </div>

    <div class="campo" id="campo_documento">
        <label for="documento">Documento del Corte</label>
        <input type="file" id="documento" name="documento" class="atributo" required 
               accept=".pdf,.doc,.docx">
        <small class="mensaje-info">Esta será la primera versión (v1) del corte. Formatos permitidos: PDF, DOC, DOCX (máximo 10MB)</small>
    </div>

    <div class="campo" id="campo_descripcion">
        <label for="descripcion">Descripción de la versión (opcional)</label>
        <textarea id="descripcion" name="descripcion" class="atributo" rows="3" 
                  placeholder="Describe brevemente esta primera versión del corte...">{{ old('descripcion') }}</textarea>
        <small class="mensaje-info">Ej: "Versión inicial del corte 1"</small>
    </div>
</div>

<div class="botones-accion">
    <input type="submit" value="Crear Corte" id="crear_fundamentación">
    <a href="{{ route('gestionarCortes') }}" id="btn_cancelar">Cancelar</a>
</div>

</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validar extensión del archivo
    const documentoInput = document.getElementById('documento');
    const allowedExtensions = ['.pdf', '.doc', '.docx'];
    
    documentoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const fileName = file.name.toLowerCase();
            const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
            
            if (!isValidExtension) {
                alert('Error: Solo se permiten archivos PDF, DOC y DOCX');
                this.value = '';
                this.classList.add('invalido');
                return;
            }
            
            // Validar tamaño (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Error: El archivo no puede exceder los 10MB');
                this.value = '';
                this.classList.add('invalido');
            } else {
                this.classList.remove('invalido');
                this.classList.add('valido');
            }
        }
    });
    
    // Mostrar información de la tesis seleccionada (solo si no viene de detalles de tesis)
    @if(!isset($tesisId))
        const tesisSelect = document.getElementById('id_tesis');
        const tesisInfo = document.createElement('div');
        tesisInfo.id = 'tesis-info';
        tesisInfo.className = 'tesis-info';
        tesisSelect.parentNode.appendChild(tesisInfo);
        
        function mostrarInfoTesis() {
            const selectedOption = tesisSelect.options[tesisSelect.selectedIndex];
            if (selectedOption.value) {
                const texto = selectedOption.text;
                tesisInfo.innerHTML = `<strong>Información seleccionada:</strong><br>${texto}`;
            } else {
                tesisInfo.innerHTML = '';
            }
        }
        
        tesisSelect.addEventListener('change', mostrarInfoTesis);
        mostrarInfoTesis();
    @endif
    
    // Validación en tiempo real
    const inputs = document.querySelectorAll('.atributo');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '' && this.hasAttribute('required')) {
                this.classList.add('invalido');
            } else {
                this.classList.remove('invalido');
                this.classList.add('valido');
            }
        });
    });
});
</script>

@endsection