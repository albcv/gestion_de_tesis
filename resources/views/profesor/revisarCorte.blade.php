@extends('layouts.app')

@vite(['resources/css/profesor/revisar.css'])

@section('content')
<div class="container-fluid">
    <div class="content-panel">
        <!-- Breadcrumb -->
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="{{ route('revisarCorte') }}">
                        ← Volver a Cortes Asignados
                    </a>
                </li>
            </ul>
        </div>

        <!-- Encabezado de la página -->
        <div class="page-title-container">
            <h1 class="page-title">Revisar Corte de Tesis</h1>
            <p class="page-subtitle">Corte {{ $corte->Numero_corte }} - {{ $corte->tesis->estudiante->Nombre_estudiante }} {{ $corte->tesis->estudiante->Apellido1 }}</p>
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
                            {{ $corte->tesis->estudiante->Nombre_estudiante }} 
                            {{ $corte->tesis->estudiante->Apellido1 }} 
                            {{ $corte->tesis->estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">🪪 Carnet de Identidad:</span>
                        <p class="info-value">{{ $corte->tesis->estudiante->CI_estudiante }}</p>
                    </div>
                    <div class="info-item">
                        <span class="info-label">📄 Tesis:</span>
                        <p class="info-value">{{ $corte->tesis->Nombre_trabajo }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado del corte -->
        <div class="info-card">
            <div class="card-header">
                <h3>📋 Estado del Corte {{ $corte->Numero_corte }}</h3>
            </div>
            <div class="card-body">
                <div class="status-section">
                    <div class="status-container">
                        <div class="status-info">
                            <span class="status-label">Estado actual:</span>
                            @if ($corte->aprobado)
                                <span class="status-badge status-approved">✅ Aprobado</span>
                            @elseif ($corte->desaprobado)
                                <span class="status-badge status-rejected">❌ Desaprobado</span>
                            @else
                                <span class="status-badge status-pending">⏰ Pendiente</span>
                            @endif
                        </div>
                        
                        <div class="actions-container">
                            @if (!$corte->aprobado && !$corte->desaprobado)
                                <form action="{{ route('corte.aprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                    <button type="submit" class="action-button button-success">
                                        ✅ Aprobar
                                    </button>
                                </form>
                                <form action="{{ route('corte.desaprobar') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                    <button type="submit" class="action-button button-danger">
                                        ❌ Desaprobar
                                    </button>
                                </form>
                            @elseif ($corte->aprobado || $corte->desaprobado)
                                <form action="{{ route('corte.revertir') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
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
                <h3>🕐 Versiones del Corte</h3>
            </div>
            <div class="card-body">
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
                                </div>
                                <div class="version-footer">
                                    <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                       class="action-button button-outline">
                                        📥 Descargar
                                    </a>
                                    <span class="version-time">
                                        ⏰ {{ $version->created_at->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">ℹ️</div>
                        <h4 class="empty-state-title">No hay versiones subidas</h4>
                        <p class="empty-state-text">
                            El estudiante aún no ha subido versiones para este corte.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- No Conformidades -->
        <div class="info-card">
            <div class="card-header">
                <h3>⚠️ No Conformidades</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        @if ($corte->noConformidades && $corte->noConformidades->count() > 0)
                            <ul class="list-group mb-3">
                                @foreach ($corte->noConformidades as $noConformidad)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $noConformidad->Deficiencias_detectadas }}
                                        <form action="{{ route('corte.eliminarNoConformidad') }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                            <input type="hidden" name="no_conformidad_id" value="{{ $noConformidad->idNoConformidades }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                🗑️
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No hay no conformidades registradas.</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <!-- Sección para agregar no conformidad existente -->
                        <div class="mb-4">
                            <h3>Agregar No Conformidad Existente:</h3>
                            <form action="{{ route('corte.agregarNoConformidad') }}" method="POST" class="mb-3">
                                @csrf
                                <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                <div class="mb-3">
                                    <select class="form-select" name="no_conformidad_id" required>
                                        <option value="">Seleccionar no conformidad existente</option>
                                        @foreach ($noConformidadesLista as $noConformidad)
                                            <option value="{{ $noConformidad->idNoConformidades }}">
                                                {{ $noConformidad->Deficiencias_detectadas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit" class="action-button button-primary w-100">
                                    ➕ Agregar Existente
                                </button>
                            </form>
                        </div>

                        <!-- Separador -->
                        <hr class="my-4">

                        <!-- Sección para crear nueva no conformidad -->
                        <div>
                            <h3>Crear Nueva No Conformidad:</h3>
                            <form action="{{ route('corte.crearNuevaNoConformidad') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id_corte" value="{{ $corte->idCortes_de_tesis }}">
                                <div class="mb-3">
                                    <textarea class="form-control" id="nueva_no_conformidad" name="nueva_no_conformidad" 
                                              rows="4" placeholder="Describe la no conformidad detectada..." 
                                              required></textarea>
                                </div>
                                <button type="submit" class="action-button button-success w-100">
                                    ➕ Crear y Asignar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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

        const nuevaNoConformidadForm = document.querySelector('form[action*="crearNuevaNoConformidad"]');
        if (nuevaNoConformidadForm) {
            nuevaNoConformidadForm.addEventListener('submit', function(e) {
                const textarea = this.querySelector('#nueva_no_conformidad');
                if (textarea.value.trim().length < 5) {
                    e.preventDefault();
                    alert('La descripción de la no conformidad debe tener al menos 5 caracteres.');
                    textarea.focus();
                }
            });
        }
    });
</script>
@endsection