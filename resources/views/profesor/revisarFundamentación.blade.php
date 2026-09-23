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
                <form class="recommendation-form" action="{{ route('fundamentacion.guardarRecomendacion') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_fundamentacion" value="{{ $fundamentacion->id_fundamentacion }}">
                    <div class="form-group">
                        <label for="recomendacion" class="form-label">
                            ✏️ Escribe tus recomendaciones para el estudiante:
                        </label>
                        <textarea class="form-textarea" id="recomendacion" name="recomendacion" 
                                  placeholder="Escribe aquí las recomendaciones, observaciones o comentarios sobre la fundamentación...">{{ $fundamentacion->recomendacion->recomendacion ?? '' }}</textarea>
                    </div>
                    <div class="form-actions">
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
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.alert-message').forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

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

        const cards = document.querySelectorAll('.info-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    });
</script>
@endsection