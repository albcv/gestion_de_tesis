@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/gestionar/gestionarCortes/crearEditarCorte.css'])
@vite(['resources/css/gestionar/gestionarCortes/editarCorte.css'])
@vite(['resources/js/gestionarCortes/validarCorte.js'])


<h1 id="editar">Editar Corte de Tesis</h1>

<form action="{{ route('modificarCorte') }}" method="post" id="formulario_corte" enctype="multipart/form-data">
@csrf
<input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">

<div class="select" id="campo_id_tesis">
    <label for="id_tesis">Tesis</label>
    <select name="id_tesis" id="id_tesis" class="atributo" required>
        <option value=""></option>
        @foreach ($tesis as $tesisItem)
            <option value="{{ $tesisItem->id }}" 
                {{ old('id_tesis', $corte->id_tesis) == $tesisItem->id ? 'selected' : '' }}>
                Tesis #{{ $tesisItem->id }}: {{ $tesisItem->Nombre_trabajo }} - 
                @if ($tesisItem->estudiante)
                    {{ $tesisItem->estudiante->Nombre_estudiante }} {{ $tesisItem->estudiante->Apellido1 }}
                @endif
            </option>
        @endforeach
    </select>
</div>

<div class="select" id="campo_número_corte">
    <label for="número_corte">Número de Corte</label>
    <select name="número_corte" id="número_corte" class="atributo" required>
        <option value=""></option>
        <option value="1" {{ old('número_corte', $corte->Numero_corte) == 1 ? 'selected' : '' }}>Corte 1</option>
        <option value="2" {{ old('número_corte', $corte->Numero_corte) == 2 ? 'selected' : '' }}>Corte 2</option>
        <option value="3" {{ old('número_corte', $corte->Numero_corte) == 3 ? 'selected' : '' }}>Corte 3</option>
        <option value="4" {{ old('número_corte', $corte->Numero_corte) == 4 ? 'selected' : '' }}>Corte 4</option>
    </select>
</div>

<!-- Sección de gestión de versiones -->
<div class="seccion-versiones">
    <h3 style="color: #fff">Gestión de Versiones</h3>
    
    @if($ultimaVersion)
    <div class="version-actual">
        <h4>Última versión disponible (v{{ $ultimaVersion->version_numero }})</h4>
        <div class="info-version">
            <p><strong>Archivo:</strong> {{ $ultimaVersion->nombre_archivo }}</p>
            <p><strong>Enlace GitHub:</strong> 
                @if($ultimaVersion->Enlace_Github)
                    <a href="{{ $ultimaVersion->Enlace_Github }}" target="_blank">{{ $ultimaVersion->Enlace_Github }}</a>
                @else
                    <span style="color: #555;">Sin enlace</span>
                @endif
            </p>
            <p><strong>Tamaño:</strong> {{ number_format($ultimaVersion->tamanio / 1024, 2) }} KB</p>
            <p><strong>Fecha de subida:</strong> {{ $ultimaVersion->created_at->format('d/m/Y H:i') }}</p>
            @if($ultimaVersion->descripcion)
                <p><strong>Descripción:</strong> {{ $ultimaVersion->descripcion }}</p>
            @endif
            
            <a href="{{ route('ver-documento-version-corte', $ultimaVersion->id) }}" 
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
                <small class="help-text">Reemplazará el archivo, enlace y/o descripción de la versión actual</small>
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
        <p style="color:#fff">⚠️ Este corte no tiene versiones. Se creará la versión 1.</p>
        <input type="hidden" name="accion_version" value="crear">
    </div>
    @endif
    
    <div class="campo" id="campo_enlace">
        <label for="enlace">Enlace a GitHub</label>
        <input type="url" id="enlace" name="enlace" class="atributo" 
               placeholder="https://github.com/usuario/repositorio" 
               value="{{ old('enlace', $ultimaVersion->Enlace_Github ?? '') }}" 
               pattern="https?://.+">
        <small class="mensaje-info">Actualizar el enlace de GitHub para esta versión</small>
    </div>

    <div class="campo" id="campo_documento">
        <label for="documento">Documento del Corte</label>
        <input type="file" id="documento" name="documento" class="atributo" 
               accept=".pdf,.doc,.docx">
        <small class="mensaje-info">Si no selecciona un archivo, solo se actualizará el enlace y descripción (si se proporcionan)</small>
    </div>

    <div class="campo" id="campo_descripcion">
        <label for="descripcion">Descripción de la versión (opcional)</label>
        <textarea id="descripcion" name="descripcion" class="atributo" rows="3" 
                  placeholder="Describe los cambios en esta versión...">{{ old('descripcion', $ultimaVersion->descripcion ?? '') }}</textarea>
        <small class="mensaje-info">Ej: "Corrección de errores en el código" o "Actualización de documentación"</small>
    </div>
    
    @if($corte->versiones && $corte->versiones->count() > 0)
    <div class="lista-versiones">
        <h4>Todas las versiones ({{ $corte->versiones->count() }})</h4>
        <div class="versiones-grid">
            @foreach($corte->versiones as $version)
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
                    @if($version->Enlace_Github)
                    <p class="version-enlace">🔗 GitHub</p>
                    @endif
                    @if($version->descripcion)
                    <p class="version-desc">{{ Str::limit($version->descripcion, 50) }}</p>
                    @endif
                </div>
                <div class="version-actions">
                    <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                       class="btn-descarga-mini" title="Descargar">📥</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<div class="botones-accion">
    <input type="submit" value="Actualizar Corte" id="crear_fundamentación">
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
            tesisInfo.innerHTML = `<strong>Información seleccionada:</strong><br>${texto}`;
        } else {
            tesisInfo.innerHTML = '';
        }
    }
    
    tesisSelect.addEventListener('change', mostrarInfoTesis);
    mostrarInfoTesis();
    
    // Mostrar/ocultar opciones según selección de versión
    const radios = document.querySelectorAll('input[name="accion_version"]');
    const documentLabel = document.querySelector('label[for="documento"]');
    const enlaceLabel = document.querySelector('label[for="enlace"]');
    
    function actualizarEtiquetas() {
        const radioSeleccionado = document.querySelector('input[name="accion_version"]:checked');
        if (radioSeleccionado) {
            if (radioSeleccionado.value === 'actualizar') {
                documentLabel.innerHTML = 'Reemplazar documento actual (opcional)';
                enlaceLabel.innerHTML = 'Actualizar enlace GitHub';
            } else {
                documentLabel.innerHTML = 'Nuevo documento para versión adicional (opcional)';
                enlaceLabel.innerHTML = 'Nuevo enlace GitHub para versión adicional';
            }
        }
    }
    
    radios.forEach(radio => {
        radio.addEventListener('change', actualizarEtiquetas);
    });
    
    actualizarEtiquetas();
});
</script>

@endsection