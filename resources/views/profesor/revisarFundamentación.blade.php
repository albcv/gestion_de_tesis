@extends('layouts.app')

@vite(['resources/css/profesor/revisar.css'])

@section('content')
<div class="container-fluid">
    <div class="content-panel">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="{{ route('revisarFundamentación') }}">
                        ← Volver a Fundamentaciones Asignadas
                    </a>
                </li>
            </ul>
        </div>

        <!-- Encabezado de la página -->
        <div class="page-title-container">
            <h1 class="page-title">Revisar Fundamentación</h1>
        </div>

        {{-- Mensajes de sesión --}}
        @if (session('success'))
            <div class="alert-message alert-success alert-dismissible">
                ✅ <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-message alert-error alert-dismissible">
                ❌ <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        @endif

        {{-- Errores de validación --}}
        @if ($errors->any())
            <div class="alert-message alert-error alert-dismissible">
                ❌ <strong>Errores de validación:</strong>
                <ul style="margin: 8px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        @endif

        <!-- Información del Estudiante -->
        <div class="info-card">
            <div class="card-header">
                <h3>🎓 Información del Estudiante</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">👤 Nombre completo:</span>
                        <p class="info-value">
                            {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                            {{ $fundamentacion->tesis->estudiante->Apellido1 }} 
                            {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🪪 Carnet de Identidad:</span>
                        <p class="info-value">{{ $fundamentacion->tesis->estudiante->CI_estudiante }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📄 Tesis:</span>
                        <p class="info-value">{{ $fundamentacion->tesis->Nombre_trabajo }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de la fundamentación -->
        <div class="info-card">
            <div class="card-header">
                <h3>📋 Estado de la fundamentación</h3>
            </div>
            <div class="card-body">
                <div class="status-section">
                    <div class="status-container">
                        <div class="status-info">
                            <span class="status-label">Estado actual:</span>
                            @if ($fundamentacion->aprobada)
                                <span class="status-badge status-approved">✅ Aprobada</span>
                            @elseif ($fundamentacion->desaprobada)
                                <span class="status-badge status-rejected">❌ Desaprobada</span>
                            @else
                                <span class="status-badge status-pending">⏰ Pendiente</span>
                            @endif
                        </div>

                        <div class="actions-container">
                            @if (!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                                <form action="{{ route('fundamentacion.aprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-success">
                                        ✅ Aprobar
                                    </button>
                                </form>
                                <form action="{{ route('fundamentacion.desaprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-danger">
                                        ❌ Desaprobar
                                    </button>
                                </form>
                            @elseif ($fundamentacion->aprobada || $fundamentacion->desaprobada)
                                <form action="{{ route('fundamentacion.revertir') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-warning">
                                        ↩️ Revertir a Pendiente
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Versiones -->
        <div class="info-card">
            <div class="card-header">
                <h3>🕐 Versiones de la Fundamentación</h3>
            </div>
            <div class="card-body">
                @if ($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                    <div class="versions-grid">
                        @foreach ($fundamentacion->versiones as $version)
                            <div class="version-card">
                                <div class="version-header">
                                    <span class="version-title">🔀 Versión {{ $version->version_numero }}</span>
                                    <span class="version-date">{{ $version->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="version-info">
                                    <p>
                                        <strong>📄 Archivo:</strong>
                                        {{ $version->nombre_archivo }}
                                    </p>
                                    @if($version->descripcion)
                                        <p>
                                            <strong>📝 Descripción:</strong>
                                            {{ $version->descripcion }}
                                        </p>
                                    @endif
                                </div>
                                <div class="version-footer">
                                    <a href="{{ route('ver-documento-version', $version->id) }}"
                                       class="action-button button-outline">
                                        📥 Descargar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">ℹ️</div>
                        <h4 class="empty-state-title">No hay versiones subidas</h4>
                        <p class="empty-state-text">
                            El estudiante aún no ha subido versiones para esta fundamentación.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recomendación -->
        <div class="info-card">
            <div class="card-header">
                <h3>💬 Recomendaciones</h3>
            </div>
            <div class="card-body">
                <form class="recommendation-form"
                      action="{{ route('fundamentacion.guardarRecomendacion') }}"
                      method="POST"
                      enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">

                    <div class="form-group">
                        <label for="recomendacion" class="form-label">
                            ✏️ Escribe tus recomendaciones para el estudiante:
                        </label>
                        <textarea class="form-textarea" id="recomendacion" name="recomendacion"
                                  placeholder="Escribe aquí las recomendaciones, observaciones o comentarios sobre la fundamentación..."
                                  required>{{ old('recomendacion', $fundamentacion->recomendacion->recomendacion ?? '') }}</textarea>
                    </div>

                    {{-- ==================== DOCUMENTO DE REVISIÓN ==================== --}}
                    @php
                        $docActual  = $fundamentacion->recomendacion->documento_revision ?? null;
                        $existeDoc  = $docActual && \Illuminate\Support\Facades\Storage::disk('local')->exists($docActual);
                        $nombreDoc  = $existeDoc ? basename($docActual) : null;
                        $tamanioDoc = $existeDoc ? \Illuminate\Support\Facades\Storage::disk('local')->size($docActual) : 0;
                    @endphp

                    @if ($existeDoc)
                        <div class="form-group mt-3">
                            <span class="info-label">📎 Documento actual:</span>

                            <div class="documento-revision-box" style="margin-top: 8px;">
                                <span class="documento-revision-icono">📄</span>
                                <div class="documento-revision-texto">
                                    <strong>{{ $nombreDoc }}</strong>
                                    <small>
                                        Tamaño:
                                        {{ number_format($tamanioDoc / 1024, 2) }} KB
                                    </small>
                                </div>
                                <a href="{{ route('descargarRevisionRecomendacionFundamentacion', $fundamentacion->id_fundamentacion) }}"
                                   class="documento-revision-btn">
                                    📥 Descargar
                                </a>
                            </div>

                            <div class="form-check mt-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="eliminar_documento"
                                       name="eliminar_documento"
                                       value="1"
                                       {{ old('eliminar_documento') ? 'checked' : '' }}>
                                <label class="form-check-label" for="eliminar_documento">
                                    🗑️ Eliminar el documento actual al guardar
                                </label>
                                <small class="form-text text-muted d-block">
                                    Si marcas esta opción y no subes un archivo nuevo, el documento se eliminará.
                                </small>
                            </div>
                        </div>
                    @endif

                    <div class="form-group mt-3">
                        <label for="documento_revision_fundamentacion" class="form-label">
                            {{ $existeDoc ? '🔄 Reemplazar documento de revisión (opcional)' : '📎 Documento de revisión (opcional)' }}
                        </label>
                        <input type="file"
                               class="form-control"
                               id="documento_revision_fundamentacion"
                               name="documento_revision"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar">
                        <small class="form-text text-muted">
                            Formatos permitidos: PDF, Word, Excel, PowerPoint, ZIP, RAR (máx. 10 MB).
                            @if ($existeDoc)
                                <strong>Subir un nuevo archivo reemplazará al actual.</strong>
                            @endif
                        </small>
                    </div>

                    <div class="form-actions mt-3">
                        <button type="submit" class="action-button button-primary">
                            💾 Guardar Recomendación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Ocultar alertas automáticamente después de 8 segundos
        setTimeout(function () {
            document.querySelectorAll('.alert-message').forEach(function (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 8000);

        // Cerrar alertas al hacer clic en la X
        document.querySelectorAll('.alert-close').forEach(function (button) {
            button.addEventListener('click', function () {
                const alert = this.closest('.alert-message');
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    alert.style.display = 'none';
                }, 300);
            });
        });

        // Animación de entrada de las cards
        const cards = document.querySelectorAll('.info-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });

        // Validación en cliente del tamaño del archivo
        const inputArchivo = document.getElementById('documento_revision_fundamentacion');
        if (inputArchivo) {
            inputArchivo.addEventListener('change', function (e) {
                const file = e.target.files[0];
                const maxSize = 10 * 1024 * 1024; // 10 MB
                if (file && file.size > maxSize) {
                    alert('El archivo excede el tamaño máximo de 10 MB.');
                    e.target.value = '';
                }
            });
        }

        // ==================== Lógica de eliminación/reemplazo ====================
        const checkboxEliminar = document.getElementById('eliminar_documento');

        if (checkboxEliminar && inputArchivo) {
            // Si el usuario sube un archivo nuevo, desmarcar "Eliminar"
            // (porque el archivo nuevo tiene prioridad sobre el checkbox)
            inputArchivo.addEventListener('change', function () {
                if (this.files.length > 0 && checkboxEliminar.checked) {
                    checkboxEliminar.checked = false;
                }
            });

            // Si el usuario marca "Eliminar" y ya hay un archivo seleccionado,
            // preguntar si desea descartar el archivo nuevo
            checkboxEliminar.addEventListener('change', function () {
                if (this.checked && inputArchivo.files.length > 0) {
                    const confirmar = confirm(
                        'Has seleccionado un archivo nuevo. Si marcas "Eliminar", se ignorará el archivo nuevo. ¿Deseas continuar?'
                    );
                    if (!confirmar) {
                        this.checked = false;
                    } else {
                        inputArchivo.value = '';
                    }
                }
            });
        }
    });
</script>
@endsection