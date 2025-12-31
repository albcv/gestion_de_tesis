@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/gestionar/gestionarFundamentación/crearFundamentación.css') }}">
<script src="{{ asset('js/gestionarFundamentaciones/validarFundamentación.js') }}"></script>

<h1>Crear Fundamentación</h1>

<form action="{{ route('agregarFundamentación') }}" method="post" id="formulario_fundamentación" enctype="multipart/form-data">
@csrf

<div class="select" id="campo_id_tesis">
    <label for="id_tesis">Tesis</label>
    <select name="id_tesis" id="id_tesis" class="atributo" required>
        <option value=""></option>
        @foreach ($tesis as $tesisItem)
            <option value="{{ $tesisItem->id }}" 
                {{ (isset($tesisSeleccionada) && $tesisSeleccionada->id == $tesisItem->id) ? 'selected' : '' }}
                {{ (isset($tesisSeleccionada) && $tesisSeleccionada->id == $tesisItem->id) ? 'disabled style="background-color: #e9ecef;"' : '' }}>
                {{ $tesisItem->Nombre_trabajo }} - 
                @if ($tesisItem->estudiante)
                    {{ $tesisItem->estudiante->Nombre_estudiante }} {{ $tesisItem->estudiante->Apellido1 }}
                @endif
            </option>
        @endforeach
    </select>
    
    <!-- Campo oculto para enviar el ID de la tesis cuando está deshabilitada -->
    @if(isset($tesisSeleccionada))
        <input type="hidden" name="id_tesis" value="{{ $tesisSeleccionada->id }}">
    @endif
</div>

<!-- Sección de versión -->
<div class="seccion-version">
    <h3>Versión 1 de la Fundamentación</h3>
    
    <div class="campo" id="campo_documento">
        <label for="documento">Documento de Fundamentación</label>
        <input type="file" id="documento" name="documento" class="atributo" required accept=".pdf,.doc,.docx" title="Formatos permitidos: PDF, DOC, DOCX (máximo 10MB)">
        <small class="help-text">Esta será la primera versión (v1) de la fundamentación</small>
    </div>

    <div class="campo" id="campo_descripcion">
        <label for="descripcion">Descripción de la versión (opcional)</label>
        <textarea id="descripcion" name="descripcion" class="atributo" rows="3" placeholder="Describe brevemente esta primera versión de la fundamentación..."></textarea>
        <small class="help-text">Ej: "Versión inicial de la fundamentación"</small>
    </div>
</div>

<div class="botones-accion">
    <input type="submit" value="Aceptar" id="crear_fundamentación">
    <a href="{{ route('gestionarFundamentaciones') }}" id="btn_cancelar">Cancelar</a>
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
            }
            
            // Validar tamaño (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Error: El archivo no puede exceder los 10MB');
                this.value = '';
            }
        }
    });
    
    // Si hay una tesis seleccionada, mostrar mensaje informativo
    const tesisSelect = document.getElementById('id_tesis');
    @if(isset($tesisSeleccionada))
        tesisSelect.style.backgroundColor = '#e9ecef';
        tesisSelect.title = "Esta tesis fue seleccionada desde 'Detalles de Tesis' y no puede ser modificada";
        
        // Crear mensaje informativo
        const infoDiv = document.createElement('div');
        infoDiv.className = 'info-tesis-seleccionada';
        infoDiv.innerHTML = `
            <div class="info-box">
                <strong>⚠️ Información:</strong> 
                Esta tesis fue seleccionada desde 'Detalles de Tesis'. 
                Para cambiar la tesis, regrese a la lista de fundamentaciones.
            </div>
        `;
        tesisSelect.parentNode.insertBefore(infoDiv, tesisSelect.nextSibling);
    @endif
});
</script>

@endsection