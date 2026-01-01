@extends('layouts.app')


@vite(['resources/css/estudiante/subirCorte.css'])

@section('content')
<div class="cortes-container">
    <div class="cortes-content">
        <h2 class="page-title">Cortes de Tesis</h2>

        <!-- Mensajes de sesión -->
        @if (session('success'))
            <div class="alert-message success-message">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-message error-message">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div class="alert-message info-message">
                {{ session('info') }}
            </div>
        @endif

        @if (!$tesis)
            <div class="alert-message warning-message">
                No tienes una tesis registrada. Contacta con el administrador.
            </div>
        @elseif (!$tesis->fundamentacion || !$tesis->fundamentacion->aprobada)
            <div class="alert-message warning-message">
                Debes tener la fundamentación aprobada para poder subir cortes de tesis.
            </div>
        @else
            <!-- Información de la Tesis -->
            <div class="card-container">
                <div class="card-header primary-header">
                    <h3 class="card-title">Información de la Tesis</h3>
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="info-column">
                            <p class="info-label">
                                <strong>Título:</strong>
                            </p>
                            <p class="info-content">{{ $tesis->Nombre_trabajo }}</p>
                        </div>
                        <div class="info-column">
                            <p class="info-label">
                                <strong>Estudiante:</strong>
                            </p>
                            <p class="info-content">{{ $tesis->estudiante->Nombre_estudiante }} {{ $tesis->estudiante->Apellido1 }} {{ $tesis->estudiante->Apellido2 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestión de Cortes -->
            <div class="card-container">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Cortes</h3>
                </div>
                <div class="card-body">
                    <div class="cortes-grid">
                        @for ($i = 1; $i <= 4; $i++)
                            @php
                                $corte = $cortes->where('Numero_corte', $i)->first();
                                $fechaEntrega = $fechasEntrega->get($i);
                                $headerClass = 'sin-estado';
                                
                                if ($corte && $corte->aprobado) {
                                    $headerClass = 'aprobado';
                                } elseif ($corte && $corte->desaprobado) {
                                    $headerClass = 'desaprobado';
                                } elseif ($corte) {
                                    $headerClass = 'pendiente';
                                }
                            @endphp
                            
                            <div class="corte-card">
                                <div class="corte-header {{ $headerClass }}">
                                    <span>Corte {{ $i }}</span>
                                    @if ($corte && $corte->aprobado)
                                        <span class="status-badge badge-aprobado">Aprobado</span>
                                    @elseif ($corte && $corte->desaprobado)
                                        <span class="status-badge badge-desaprobado">Desaprobado</span>
                                    @elseif ($corte)
                                        <span class="status-badge badge-pendiente">Pendiente</span>
                                    @else
                                        <span class="status-badge badge-pendiente">Sin entregar</span>
                                    @endif
                                </div>
                                <div class="corte-body">
                                    <div class="corte-info">
                                        <!-- Fecha de entrega -->
                                        <div class="corte-info-item">
                                            <p class="corte-label">
                                                <strong>Fecha de entrega:</strong>
                                            </p>
                                            <p class="corte-value">
                                                {{ $fechaEntrega ? $fechaEntrega->fecha_entrega->format('d/m/Y') : 'No establecida' }}
                                            </p>
                                        </div>

                                        <!-- Tiempo restante -->
                                        @if ($fechaEntrega)
                                            <div class="corte-info-item">
                                                <p class="corte-label">
                                                    <strong>Tiempo restante:</strong>
                                                </p>
                                                <p class="corte-value">
                                                    @if (now()->greaterThan($fechaEntrega->fecha_entrega))
                                                        <span class="text-danger">Fecha de entrega vencida</span>
                                                    @else
                                                        <span class="text-success">{{ (int) now()->diffInDays($fechaEntrega->fecha_entrega) }} días restantes</span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endif

                                        <!-- No conformidades -->
                                        @if ($corte && $corte->noConformidades && $corte->noConformidades->count() > 0)
                                            <div class="corte-info-item">
                                                <p class="corte-label">
                                                    <strong>No conformidades:</strong>
                                                </p>
                                                <ul class="corte-list">
                                                    @foreach ($corte->noConformidades as $noConformidad)
                                                        <li class="corte-list-item no-conformidad">
                                                            {{ $noConformidad->Deficiencias_detectadas }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <!-- Opinión del Tutor -->
                                        @if ($corte && $corte->opinionTutor)
                                            <div class="corte-info-item">
                                                <p class="corte-label">
                                                    <strong>Opinión del Tutor:</strong>
                                                </p>
                                                <p class="corte-value">
                                                    {{ $corte->opinionTutor->opinion }}
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Versiones -->
                                        @if ($corte && $corte->versiones && $corte->versiones->count() > 0)
                                            <div class="corte-info-item">
                                                <p class="corte-label">
                                                    <strong>Versiones:</strong>
                                                </p>
                                                <ul class="corte-list">
                                                    @foreach ($corte->versiones as $version)
                                                        <li class="corte-list-item version">
                                                            v{{ $version->version_numero }} - 
                                                            <a href="{{ route('ver-documento-version-corte', $version->id) }}" 
                                                               class="version-link">
                                                                {{ $version->nombre_archivo }}
                                                            </a>
                                                            ({{ $version->created_at->format('d/m/Y') }})
                                                            @if ($version->descripcion)
                                                                <br>
                                                                <small class="text-muted">{{ $version->descripcion }}</small>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Formulario para subir versión -->
                                    @if ((!$corte || !$corte->aprobado) && $fechaEntrega && now()->lte($fechaEntrega->fecha_entrega))
                                        <div class="separator"></div>
                                        <form action="{{ route('subirVersionCorte', $i) }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                            @csrf
                                            <div class="form-group">
                                                <label for="documento{{ $i }}" class="form-label">
                                                    Documento (PDF, DOC, DOCX)
                                                </label>
                                                <input type="file" class="form-control form-control-file" 
                                                       id="documento{{ $i }}" name="documento" 
                                                       required accept=".pdf,.doc,.docx">
                                                <small class="form-hint">Máximo 10MB</small>
                                            </div>
                                            <div class="form-group">
                                                <label for="enlace{{ $i }}" class="form-label">
                                                    Enlace GitHub 
                                                </label>
                                                <input type="url" class="form-control" 
                                                       id="enlace{{ $i }}" name="enlace" 
                                                       placeholder="https://github.com/usuario/repositorio">
                                            </div>
                                            <div class="form-group">
                                                <label for="descripcion{{ $i }}" class="form-label">
                                                    Descripción (opcional)
                                                </label>
                                                <textarea class="form-control form-textarea" 
                                                          id="descripcion{{ $i }}" name="descripcion" 
                                                          rows="3" placeholder="Breve descripción de los cambios"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Subir Corte {{ $i }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Validación del tamaño del archivo
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (file && file.size > maxSize) {
                alert('El archivo excede el tamaño máximo de 10MB');
                e.target.value = '';
            }
        });
    });
</script>
@endpush
@endsection