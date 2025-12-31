@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
<link rel="stylesheet" href="{{ asset('css/profesor/revisarEstudianteTutorado.css') }}">

@section('content')
<div class="container-fluid">
    <div class="content-panel">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="{{ route('estudiantesTutorados') }}">
                        <i class="fas fa-arrow-left"></i> Volver a Estudiantes Tutorados
                    </a>
                </li>
            </ul>
        </div>

        <!-- Encabezado de la página -->
        <div class="page-title-container">
            <h1 class="page-title">Estudiante Tutorado</h1>
            <p class="page-subtitle">{{ $estudiante->Nombre_estudiante }} {{ $estudiante->Apellido1 }} {{ $estudiante->Apellido2 }}</p>
        </div>

        @if (session('success'))
            <div class="alert-message alert-success alert-dismissible">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-message alert-error alert-dismissible">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
        @endif

        <!-- Información del Estudiante -->
        <div class="info-card">
            <div class="card-header">
                <h3><i class="fas fa-user-graduate"></i> Información del Estudiante</h3>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Nombre completo:</span>
                        <p class="info-value">
                            {{ $estudiante->Nombre_estudiante }} 
                            {{ $estudiante->Apellido1 }} 
                            {{ $estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> Carnet de Identidad:</span>
                        <p class="info-value">{{ $estudiante->CI_estudiante }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-venus-mars"></i> Sexo:</span>
                        <p class="info-value">{{ $estudiante->sexo }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-calendar-alt"></i> Fecha de Ingreso:</span>
                        <p class="info-value">{{ \Carbon\Carbon::parse($estudiante->Fecha_ingreso)->format('d/m/Y') }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-graduation-cap"></i> Año Académico:</span>
                        <p class="info-value">{{ $estudiante->year_academico }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-file-alt"></i> Tesis:</span>
                        <p class="info-value">
                            @if ($estudiante->tesis)
                                {{ $estudiante->tesis->Nombre_trabajo }}
                            @else
                                <span class="text-muted">Sin tesis registrada</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Fundamentación -->
        @if ($estudiante->tesis && $estudiante->tesis->fundamentacion)
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-check"></i> Fundamentación</h3>
                </div>
                <div class="card-body">
                    <!-- Estado de la fundamentación -->
                    <div class="status-section">
                        <div class="status-container">
                            <div class="status-info">
                                <span class="status-label">Estado:</span>
                                @if ($estudiante->tesis->fundamentacion->aprobada)
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-check-circle"></i> Aprobada
                                    </span>
                                @elseif ($estudiante->tesis->fundamentacion->desaprobada)
                                    <span class="status-badge status-rejected">
                                        <i class="fas fa-times-circle"></i> Desaprobada
                                    </span>
                                @else
                                    <span class="status-badge status-pending">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Versiones de la fundamentación -->
                    <div class="mt-4">
                        <h5><i class="fas fa-history"></i> Versiones de la Fundamentación</h5>
                        @if ($estudiante->tesis->fundamentacion->versiones && $estudiante->tesis->fundamentacion->versiones->count() > 0)
                            <div class="versions-grid">
                                @foreach ($estudiante->tesis->fundamentacion->versiones as $version)
                                    <div class="version-card">
                                        <div class="version-header">
                                            <span class="version-title">
                                                <i class="fas fa-code-branch"></i>
                                                Versión {{ $version->version_numero }}
                                            </span>
                                            <span class="version-date">
                                                {{ $version->created_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                        <div class="version-info">
                                            <p>
                                                <strong><i class="fas fa-file"></i> Archivo:</strong>
                                                {{ $version->nombre_archivo }}
                                            </p>
                                            @if($version->descripcion)
                                                <p>
                                                    <strong><i class="fas fa-align-left"></i> Descripción:</strong>
                                                    {{ $version->descripcion }}
                                                </p>
                                            @endif
                                            <p>
                                                <strong><i class="fas fa-hdd"></i> Tamaño:</strong>
                                                {{ round($version->tamanio / 1024, 2) }} KB
                                            </p>
                                        </div>
                                        <div class="version-footer">
                                            <a href="{{ route('ver-documento-version', $version->id) }}" 
                                               class="action-button button-outline">
                                                <i class="fas fa-download"></i> Descargar
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h4 class="empty-state-title">No hay versiones subidas</h4>
                                <p class="empty-state-text">
                                    El estudiante aún no ha subido versiones para esta fundamentación.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Opinión del tutor sobre fundamentación -->
                    <div class="mt-5">
                        <h5><i class="fas fa-comment-dots"></i> Tu Opinión sobre la Fundamentación</h5>
                        <form class="recommendation-form" action="{{ route('tutor.guardarOpinionFundamentacion') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_fundamentacion" value="{{ $estudiante->tesis->fundamentacion->id_fundamentacion }}">
                            <div class="form-group">
                                <label for="opinion_fundamentacion" class="form-label">
                                    <i class="fas fa-edit"></i> Escribe tu opinión sobre la fundamentación:
                                </label>
                                <textarea class="form-textarea" id="opinion_fundamentacion" name="opinion" 
                                          placeholder="Escribe aquí tu opinión, observaciones o comentarios sobre la fundamentación...">{{ $opinionFundamentacion->opinion ?? '' }}</textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="action-button button-primary">
                                    <i class="fas fa-save"></i> Guardar Opinión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="info-card">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-check"></i> Fundamentación</h3>
                </div>
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h4 class="empty-state-title">Fundamentación no disponible</h4>
                        <p class="empty-state-text">
                            El estudiante aún no ha registrado una fundamentación para su tesis.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Sección de Cortes de Tesis -->
        <div class="info-card">
            <div class="card-header">
                <h3><i class="fas fa-layer-group"></i> Cortes de Tesis</h3>
            </div>
            <div class="card-body">
                @if ($estudiante->tesis && $estudiante->tesis->cortes && $estudiante->tesis->cortes->count() > 0)
                    @foreach ($estudiante->tesis->cortes as $corte)
                        <div class="corte-section mb-5">
                            <div class="corte-header">
                                <h4><i class="fas fa-hashtag"></i> Corte {{ $corte->Numero_corte }}</h4>
                                <div class="corte-status">
                                    @if ($corte->aprobado)
                                        <span class="badge badge-success">Aprobado</span>
                                    @elseif ($corte->desaprobado)
                                        <span class="badge badge-danger">Desaprobado</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Versiones del corte -->
                            <div class="mt-3">
                                <h5><i class="fas fa-history"></i> Versiones del Corte</h5>
                                @if ($corte->versiones && $corte->versiones->count() > 0)
                                    <div class="versions-grid">
                                        @foreach ($corte->versiones as $version)
                                            <div class="version-card">
                                                <div class="version-header">
                                                    <span class="version-title">
                                                        <i class="fas fa-code-branch"></i>
                                                        Versión {{ $version->version_numero }}
                                                    </span>
                                                    <span class="version-date">
                                                        {{ $version->created_at->format('d/m/Y') }}
                                                    </span>
                                                </div>
                                                <div class="version-info">
                                                    <p>
                                                        <strong><i class="fas fa-file"></i> Archivo:</strong>
                                                        {{ $version->nombre_archivo }}
                                                    </p>
                                                    @if($version->Enlace_Github)
                                                        <p>
                                                            <strong><i class="fab fa-github"></i> GitHub:</strong>
                                                            <a href="{{ $version->Enlace_Github }}" target="_blank" class="github-link">
                                                                Ver repositorio
                                                            </a>
                                                        </p>
                                                    @endif
                                                    @if($version->descripcion)
                                                        <p>
                                                            <strong><i class="fas fa-align-left"></i> Descripción:</strong>
                                                            {{ $version->descripcion }}
                                                        </p>
                                                    @endif
                                                    <p>
                                                        <strong><i class="fas fa-hdd"></i> Tamaño:</strong>
                                                        {{ round($version->tamanio / 1024, 2) }} KB
                                                    </p>
                                                </div>
                                                <div class="version-footer">
                                                    <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                                       class="action-button button-outline">
                                                        <i class="fas fa-download"></i> Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        No hay versiones subidas para este corte.
                                    </div>
                                @endif
                            </div>

                            <!-- No Conformidades del corte -->
                            @if ($corte->noConformidades && $corte->noConformidades->count() > 0)
                                <div class="mt-3">
                                    <h5><i class="fas fa-exclamation-triangle"></i> No Conformidades</h5>
                                    <ul class="list-group">
                                        @foreach ($corte->noConformidades as $noConformidad)
                                            <li class="list-group-item">
                                                <i class="fas fa-exclamation-circle text-warning"></i>
                                                {{ $noConformidad->Deficiencias_detectadas }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Opinión del tutor sobre el corte -->
                            <div class="mt-4">
                                <h5><i class="fas fa-comment-dots"></i> Tu Opinión sobre el Corte</h5>
                                <form class="recommendation-form" action="{{ route('tutor.guardarOpinionCorte') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                    <div class="form-group">
                                        <label for="opinion_corte_{{ $corte->idCortes_de_tesis }}" class="form-label">
                                            <i class="fas fa-edit"></i> Escribe tu opinión sobre el corte {{ $corte->Numero_corte }}:
                                        </label>
                                        <textarea class="form-textarea" id="opinion_corte_{{ $corte->idCortes_de_tesis }}" name="opinion" 
                                                  placeholder="Escribe aquí tu opinión, observaciones o comentarios sobre este corte...">{{ $opinionesCortes[$corte->idCortes_de_tesis]->opinion ?? '' }}</textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="action-button button-primary">
                                            <i class="fas fa-save"></i> Guardar Opinión
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <hr class="my-4">
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <h4 class="empty-state-title">No hay cortes de tesis</h4>
                        <p class="empty-state-text">
                            El estudiante aún no ha registrado cortes para su tesis.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(function() {
            document.querySelectorAll('.alert-message').forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Botón para cerrar alertas
        document.querySelectorAll('.alert-close').forEach(function(button) {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert-message');
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        });

        // Efecto de carga suave
        const cards = document.querySelectorAll('.info-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>
@endsection