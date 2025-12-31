@extends('layouts.app')

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profesor/revisarFundamentación.css') }}">

@section('content')
<div class="container-fluid">
    <div class="content-panel">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="{{ route('revisarFundamentación') }}">
                        <i class="fas fa-arrow-left"></i> Volver a Fundamentaciones Asignadas
                    </a>
                </li>
            </ul>
        </div>

        <!-- Encabezado de la página -->
        <div class="page-title-container">
            <h1 class="page-title">Revisar Fundamentación</h1>
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
                            {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                            {{ $fundamentacion->tesis->estudiante->Apellido1 }} 
                            {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-id-card"></i> Carnet de Identidad:</span>
                        <p class="info-value">{{ $fundamentacion->tesis->estudiante->CI_estudiante }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-file-alt"></i> Tesis:</span>
                        <p class="info-value">{{ $fundamentacion->tesis->Nombre_trabajo }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de la fundamentación -->
        <div class="info-card">
            <div class="card-header">
                <h3><i class="fas fa-tasks"></i> Estado de la fundamentación</h3>
            </div>
            <div class="card-body">
                <div class="status-section">
                    <div class="status-container">
                        <div class="status-info">
                            <span class="status-label">Estado actual:</span>
                            @if ($fundamentacion->aprobada)
                                <span class="status-badge status-approved">
                                    <i class="fas fa-check-circle"></i> Aprobada
                                </span>
                            @elseif ($fundamentacion->desaprobada)
                                <span class="status-badge status-rejected">
                                    <i class="fas fa-times-circle"></i> Desaprobada
                                </span>
                            @else
                                <span class="status-badge status-pending">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            @endif
                        </div>
                        
                        <div class="actions-container">
                            @if (!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                                <form action="{{ route('fundamentacion.aprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-success">
                                        <i class="fas fa-check"></i> Aprobar
                                    </button>
                                </form>
                                <form action="{{ route('fundamentacion.desaprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-danger">
                                        <i class="fas fa-times"></i> Desaprobar
                                    </button>
                                </form>
                            @elseif ($fundamentacion->aprobada || $fundamentacion->desaprobada)
                                <form action="{{ route('fundamentacion.revertir') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                                    <button type="submit" class="action-button button-warning">
                                        <i class="fas fa-undo"></i> Revertir a Pendiente
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
                <h3><i class="fas fa-history"></i> Versiones de la Fundamentación</h3>
            </div>
            <div class="card-body">
                @if ($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                    <div class="versions-grid">
                        @foreach ($fundamentacion->versiones as $version)
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
        </div>

        <!-- Recomendación -->
        <div class="info-card">
            <div class="card-header">
                <h3><i class="fas fa-comment-dots"></i> Recomendaciones</h3>
            </div>
            <div class="card-body">
                <form class="recommendation-form" action="{{ route('fundamentacion.guardarRecomendacion') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                    <div class="form-group">
                        <label for="recomendacion" class="form-label">
                            <i class="fas fa-edit"></i> Escribe tus recomendaciones para el estudiante:
                        </label>
                        <textarea class="form-textarea" id="recomendacion" name="recomendacion" 
                                  placeholder="Escribe aquí las recomendaciones, observaciones o comentarios sobre la fundamentación...">{{ $fundamentacion->recomendacion->recomendacion ?? '' }}</textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="action-button button-primary">
                            <i class="fas fa-save"></i> Guardar Recomendación
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