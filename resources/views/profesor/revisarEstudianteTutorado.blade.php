@extends('layouts.app')

@vite(['resources/css/profesor/revisar.css'])

@section('content')
<div class="container-fluid">
    <div class="content-panel">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="{{ route('estudiantesTutorados') }}">
                        ← Volver a Estudiantes Tutorados
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
                            {{ $estudiante->Nombre_estudiante }} 
                            {{ $estudiante->Apellido1 }} 
                            {{ $estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🪪 Carnet de Identidad:</span>
                        <p class="info-value">{{ $estudiante->CI_estudiante }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">⚥ Sexo:</span>
                        <p class="info-value">{{ $estudiante->sexo }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📅 Fecha de Ingreso:</span>
                        <p class="info-value">{{ \Carbon\Carbon::parse($estudiante->Fecha_ingreso)->format('d/m/Y') }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🎓 Año Académico:</span>
                        <p class="info-value">{{ $estudiante->year_academico }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📄 Tesis:</span>
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
                    <h3>✅ Fundamentación</h3>
                </div>
                <div class="card-body">
                    <div class="status-section">
                        <div class="status-container">
                            <div class="status-info">
                                <span class="status-label">Estado:</span>
                                @if ($estudiante->tesis->fundamentacion->aprobada)
                                    <span class="status-badge status-approved">✅ Aprobada</span>
                                @elseif ($estudiante->tesis->fundamentacion->desaprobada)
                                    <span class="status-badge status-rejected">❌ Desaprobada</span>
                                @else
                                    <span class="status-badge status-pending">⏰ Pendiente</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5>🕐 Versiones de la Fundamentación</h5>
                        @if ($estudiante->tesis->fundamentacion->versiones && $estudiante->tesis->fundamentacion->versiones->count() > 0)
                            <div class="versions-grid">
                                @foreach ($estudiante->tesis->fundamentacion->versiones as $version)
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
                                            <p>
                                                <strong>💾 Tamaño:</strong>
                                                {{ round($version->tamanio / 1024, 2) }} KB
                                            </p>
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

                    <div class="mt-5">
                        <h5>💬 Tu Opinión sobre la Fundamentación</h5>
                        <form class="recommendation-form" action="{{ route('tutor.guardarOpinionFundamentacion') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_fundamentacion" value="{{ $estudiante->tesis->fundamentacion->id_fundamentacion }}">
                            <div class="form-group">
                                <label for="opinion_fundamentacion" class="form-label">
                                    ✏️ Escribe tu opinión sobre la fundamentación:
                                </label>
                                <textarea class="form-textarea" id="opinion_fundamentacion" name="opinion" 
                                          placeholder="Escribe aquí tu opinión, observaciones o comentarios sobre la fundamentación...">{{ $opinionFundamentacion->opinion ?? '' }}</textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="action-button button-primary">
                                    💾 Guardar Opinión
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="info-card">
                <div class="card-header">
                    <h3>✅ Fundamentación</h3>
                </div>
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state-icon">ℹ️</div>
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
                <h3>📚 Cortes de Tesis</h3>
            </div>
            <div class="card-body">
                @if ($estudiante->tesis && $estudiante->tesis->cortes && $estudiante->tesis->cortes->count() > 0)
                    @foreach ($estudiante->tesis->cortes as $corte)
                        <div class="corte-section mb-5">
                            <div class="corte-header">
                                <h4># Corte {{ $corte->Numero_corte }}</h4>
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

                            <div class="mt-3">
                                <h5>🕐 Versiones del Corte</h5>
                                @if ($corte->versiones && $corte->versiones->count() > 0)
                                    <div class="versions-grid">
                                        @foreach ($corte->versiones as $version)
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
                                                    @if($version->Enlace_Github)
                                                        <p>
                                                            <strong>🐙 GitHub:</strong>
                                                            <a href="{{ $version->Enlace_Github }}" target="_blank" class="github-link">
                                                                Ver repositorio
                                                            </a>
                                                        </p>
                                                    @endif
                                                    @if($version->descripcion)
                                                        <p>
                                                            <strong>📝 Descripción:</strong>
                                                            {{ $version->descripcion }}
                                                        </p>
                                                    @endif
                                                    <p>
                                                        <strong>💾 Tamaño:</strong>
                                                        {{ round($version->tamanio / 1024, 2) }} KB
                                                    </p>
                                                </div>
                                                <div class="version-footer">
                                                    <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                                       class="action-button button-outline">
                                                        📥 Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        ℹ️ No hay versiones subidas para este corte.
                                    </div>
                                @endif
                            </div>

                            @if ($corte->noConformidades && $corte->noConformidades->count() > 0)
                                <div class="mt-3">
                                    <h5>⚠️ No Conformidades</h5>
                                    <ul class="list-group">
                                        @foreach ($corte->noConformidades as $noConformidad)
                                            <li class="list-group-item">
                                                ❌ {{ $noConformidad->Deficiencias_detectadas }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-4">
                                <h5>💬 Tu Opinión sobre el Corte</h5>
                                <form class="recommendation-form" action="{{ route('tutor.guardarOpinionCorte') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                    <div class="form-group">
                                        <label for="opinion_corte_{{ $corte->idCortes_de_tesis }}" class="form-label">
                                            ✏️ Escribe tu opinión sobre el corte {{ $corte->Numero_corte }}:
                                        </label>
                                        <textarea class="form-textarea" id="opinion_corte_{{ $corte->idCortes_de_tesis }}" name="opinion" 
                                                  placeholder="Escribe aquí tu opinión, observaciones o comentarios sobre este corte...">{{ $opinionesCortes[$corte->idCortes_de_tesis]->opinion ?? '' }}</textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="action-button button-primary">
                                            💾 Guardar Opinión
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <hr class="my-4">
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">ℹ️</div>
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