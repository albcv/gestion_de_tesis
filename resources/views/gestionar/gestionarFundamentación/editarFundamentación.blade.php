@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/gestionar/gestionarFundamentación/editarFundamentación.css'])
@vite(['resources/js/gestionarFundamentaciones/validarFundamentación.js'])


<h1 id="editar">Editar Fundamentación</h1>

<form action="{{ route('modificarFundamentación') }}" method="post" id="formulario_fundamentación" enctype="multipart/form-data">
@csrf
<input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">

<div class="select" id="campo_id_tesis">
    <label for="id_tesis">Tesis</label>
    <select name="id_tesis" id="id_tesis" class="atributo" required>
        <option value=""></option>
        @foreach ($tesis as $tesisItem)
            <option value="{{ $tesisItem->id }}" 
                {{ old('id_tesis', $fundamentacion->id_tesis) == $tesisItem->id ? 'selected' : '' }}>
                {{ $tesisItem->Nombre_trabajo }} - 
                @if ($tesisItem->estudiante)
                    {{ $tesisItem->estudiante->Nombre_estudiante }} {{ $tesisItem->estudiante->Apellido1 }}
                @endif
            </option>
        @endforeach
    </select>
</div>

<!-- Sección de gestión de versiones -->
<div class="seccion-versiones">
    <h3>Gestión de Versiones</h3>
    
    @if($ultimaVersion)
    <div class="version-actual">
        <h4>Última versión disponible (v{{ $ultimaVersion->version_numero }})</h4>
        <div class="info-version">
            <p><strong>Archivo:</strong> {{ $ultimaVersion->nombre_archivo }}</p>
            <p><strong>Tamaño:</strong> {{ number_format($ultimaVersion->tamanio / 1024, 2) }} KB</p>
            <p><strong>Fecha de subida:</strong> {{ $ultimaVersion->created_at->format('d/m/Y H:i') }}</p>
            @if($ultimaVersion->descripcion)
                <p><strong>Descripción:</strong> {{ $ultimaVersion->descripcion }}</p>
            @endif
            
            <a href="{{ route('ver-documento-version', $ultimaVersion->id) }}" 
               class="btn-descargar-version" target="_blank">
                📥 Descargar esta versión
            </a>
        </div>
        
        <div class="opciones-version">
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="accion_version" value="actualizar" checked>
                    <span class="radio-text">Actualizar esta versión (v{{ $ultimaVersion->version_numero }})</span>
                    <input type="hidden" name="version_id" value="{{ $ultimaVersion->id }}">
                </label>
                <small class="help-text">Reemplazará el archivo y/o descripción de la versión actual</small>
            </div>
            
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="accion_version" value="crear">
                    <span class="radio-text">Crear nueva versión (v{{ $ultimaVersion->version_numero + 1 }})</span>
                </label>
                <small class="help-text">Creará una nueva versión manteniendo la anterior</small>
            </div>
        </div>
    </div>
    @else
    <div class="sin-versiones">
        <p>⚠️ Esta fundamentación no tiene versiones. Se creará la versión 1.</p>
        <input type="hidden" name="accion_version" value="crear">
    </div>
    @endif
    
    <div class="campo" id="campo_documento">
        <label for="documento">Documento de Fundamentación</label>
        <input type="file" id="documento" name="documento" class="atributo" accept=".pdf,.doc,.docx" 
               title="Dejar en blanco para mantener el documento actual. Formatos permitidos: PDF, DOC, DOCX (máximo 10MB)">
        <small class="help-text">Si no selecciona un archivo, solo se actualizará la descripción (si se proporciona)</small>
    </div>

    <div class="campo" id="campo_descripcion">
        <label for="descripcion">Descripción de la versión (opcional)</label>
        <textarea id="descripcion" name="descripcion" class="atributo" rows="3" 
                  placeholder="Describe los cambios en esta versión...">{{ old('descripcion', $ultimaVersion->descripcion ?? '') }}</textarea>
        <small class="help-text">Ej: "Corrección de errores tipográficos" o "Actualización de contenido"</small>
    </div>
    
    @if($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
    <div class="lista-versiones">
        <h4>Todas las versiones ({{ $fundamentacion->versiones->count() }})</h4>
        <div class="versiones-grid">
            @foreach($fundamentacion->versiones as $version)
            <div class="version-item {{ $loop->last ? 'version-actual-item' : '' }}">
                <div class="version-header">
                    <span class="version-numero">v{{ $version->version_numero }}</span>
                    @if($loop->last)
                    <span class="badge-actual">Actual</span>
                    @endif
                </div>
                <div class="version-body">
                    <p class="version-fecha">{{ $version->created_at->format('d/m/Y H:i') }}</p>
                    <p class="version-tamanio">{{ number_format($version->tamanio / 1024, 2) }} KB</p>
                    @if($version->descripcion)
                    <p class="version-desc">{{ Str::limit($version->descripcion, 50) }}</p>
                    @endif
                </div>
                <div class="version-actions">
                    <a href="{{ route('ver-documento-version', $version->id) }}" 
                       class="btn-descarga-mini" title="Descargar">📥</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="botones-accion">
    <input type="submit" value="Guardar Cambios" id="crear_fundamentación">
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
                return;
            }
            
            // Validar tamaño (10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('Error: El archivo no puede exceder los 10MB');
                this.value = '';
            }
        }
    });
    
    // Mostrar información de la tesis seleccionada
    const tesisSelect = document.getElementById('id_tesis');
    const tesisInfo = document.createElement('div');
    tesisInfo.id = 'tesis-info';
    tesisInfo.className = 'tesis-info';
    tesisSelect.parentNode.appendChild(tesisInfo);
    
    function mostrarInfoTesis() {
        const selectedOption = tesisSelect.options[tesisSelect.selectedIndex];
        if (selectedOption.value) {
            const texto = selectedOption.text;
            tesisInfo.innerHTML = `<div class="info-box"><strong>Información seleccionada:</strong> ${texto}</div>`;
        } else {
            tesisInfo.innerHTML = '';
        }
    }
    
    tesisSelect.addEventListener('change', mostrarInfoTesis);
    mostrarInfoTesis();
    
    // Mostrar/ocultar opciones según selección de versión
    const radios = document.querySelectorAll('input[name="accion_version"]');
    const documentLabel = document.querySelector('label[for="documento"]');
    
    function actualizarEtiquetaDocumento() {
        const radioSeleccionado = document.querySelector('input[name="accion_version"]:checked');
        if (radioSeleccionado) {
            if (radioSeleccionado.value === 'actualizar') {
                documentLabel.innerHTML = 'Reemplazar documento actual (opcional)';
            } else {
                documentLabel.innerHTML = 'Nuevo documento para versión adicional (opcional)';
            }
        }
    }
    
    radios.forEach(radio => {
        radio.addEventListener('change', actualizarEtiquetaDocumento);
    });
    
    actualizarEtiquetaDocumento();
});
</script>

@endsection