@extends('layouts.app')

<link rel="stylesheet" href="css/estudiante/subirFundamentación.css">

@section('content')
<div class="fundamentacion-container">
    <div class="fundamentacion-content">
        <h2 class="page-title">Fundamentación de Tesis</h2>

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
        @else
            <!-- Información de la Tesis -->
            <div class="card-container">
                <div class="card-header primary-header">
                    <h3 class="card-title">Información de la Tesis</h3>
                </div>
                <div class="card-body">
                    <div class="card-row">
                        <div class="card-column">
                            <p class="label-text"><strong>Título:</strong></p>
                            <p class="content-text">{{ $tesis->Nombre_trabajo }}</p>
                        </div>
                        <div class="card-column">
                            <p class="label-text"><strong>Estudiante:</strong></p>
                            <p class="content-text">{{ $tesis->estudiante->Nombre_estudiante }} {{ $tesis->estudiante->Apellido1 }} {{ $tesis->estudiante->Apellido2 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de la Fundamentación -->
            <div class="card-container">
                <div class="card-header">
                    <h3 class="card-title">Estado de la Fundamentación</h3>
                </div>
                <div class="card-body">
                    @if ($fundamentacion)
                        <div class="status-row">
                            <div class="status-column">
                                <p class="label-text"><strong>Estado:</strong></p>
                                @if ($fundamentacion->aprobada)
                                    <span class="status-badge approved">Aprobada</span>
                                @elseif ($fundamentacion->desaprobada)
                                    <span class="status-badge rejected">Desaprobada</span>
                                @else
                                    <span class="status-badge pending">Pendiente</span>
                                @endif
                            </div>
                            <div class="status-column">
                                <p class="label-text"><strong>Fecha de entrega:</strong></p>
                                <p class="content-text">{{ $fechaEntrega ? $fechaEntrega->fecha_entrega->format('d/m/Y') : 'No establecida' }}</p>
                            </div>
                            <div class="status-column">
                                <p class="label-text"><strong>Tiempo restante:</strong></p>
                                @if ($fechaEntrega)
                                    @if (now()->greaterThan($fechaEntrega->fecha_entrega))
                                        <span class="time-remaining expired">Fecha de entrega vencida</span>
                                    @else
                                    <span class="time-remaining valid">{{ (int) now()->diffInDays($fechaEntrega->fecha_entrega) }} días restantes</span>
                                    @endif
                                @else
                                    <span class="time-remaining">Sin fecha límite</span>
                                @endif
                            </div>
                        </div>

                        <!-- Recomendaciones -->
                        @if ($fundamentacion->recomendacion)
                            <div class="section-container">
                                <h4 class="section-title">Recomendaciones:</h4>
                                <div class="alert-box info">
                                    {{ $fundamentacion->recomendacion->recomendacion }}
                                </div>
                            </div>
                        @endif

                        <!-- Opinión del Tutor -->
                        @if ($fundamentacion->opinionTutor)
                            <div class="section-container">
                                <h4 class="section-title">Opinión del Tutor:</h4>
                                <div class="alert-box secondary">
                                    {{ $fundamentacion->opinionTutor->opinion }}
                                </div>
                            </div>
                        @endif

                        <!-- Versiones -->
                        <div class="section-container">
                            <h4 class="section-title">Versiones Subidas:</h4>
                            @if ($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                                <div class="table-wrapper">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre del Archivo</th>
                                                <th>Fecha de Subida</th>
                                                <th>Descripción</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fundamentacion->versiones as $version)
                                                <tr>
                                                    <td>v{{ $version->version_numero }}</td>
                                                    <td>{{ $version->nombre_archivo }}</td>
                                                    <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>{{ $version->descripcion ?? 'Sin descripción' }}</td>
                                                    <td>
                                                        <a href="{{ route('ver-documento-version', $version->id) }}" 
                                                           class="action-btn download-btn" style="font-size: 45px;padding:20px;background: #0f4">
                                                            📥
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="empty-message">No hay versiones subidas aún.</p>
                            @endif
                        </div>
                    @endif

                    <!-- Formulario para subir nueva versión (o primera versión) -->
                    @php
                        $puedeSubir = true;
                        $mensajeError = '';
                        
                        if ($fundamentacion && $fundamentacion->aprobada) {
                            $puedeSubir = false;
                            $mensajeError = 'Tu fundamentación ya está aprobada. No puedes subir nuevas versiones.';
                        } elseif ($fechaEntrega && now()->greaterThan($fechaEntrega->fecha_entrega)) {
                            $puedeSubir = false;
                            $mensajeError = 'La fecha de entrega de la fundamentación ha pasado. No puedes subir nuevas versiones.';
                        } elseif (!$fechaEntrega) {
                            $puedeSubir = false;
                            $mensajeError = 'No hay fecha de entrega establecida. Contacta con el administrador.';
                        }
                    @endphp

                    @if ($puedeSubir)
                        <div class="upload-section">
                            <h4 class="section-title">
                                @if ($fundamentacion && $fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                                    Subir Nueva Versión
                                @else
                                    Subir Primera Versión
                                @endif
                            </h4>
                            <form action="{{ route('subirVersionFundamentación') }}" method="POST" enctype="multipart/form-data" class="upload-form">
                                @csrf
                                <div class="form-row">
                                    <div class="form-column wide">
                                        <div class="form-group">
                                            <label for="documento" class="form-label">Documento (PDF, DOC, DOCX) - Máx. 10MB</label>
                                            <input type="file" class="file-input" id="documento" name="documento" required accept=".pdf,.doc,.docx">
                                            <div class="form-hint">Solo se permiten archivos PDF, DOC y DOCX</div>
                                        </div>
                                    </div>
                                    <div class="form-column">
                                        <div class="form-group">
                                            <label for="descripcion" class="form-label">Descripción (opcional)</label>
                                            <textarea class="textarea-input" id="descripcion" name="descripcion" rows="2" maxlength="500" placeholder="Breve descripción de los cambios"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="submit-btn primary-btn">
                                    <i class="fas fa-upload"></i> 
                                    @if ($fundamentacion && $fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                                        Subir Nueva Versión
                                    @else
                                        Subir Primera Versión
                                    @endif
                                </button>
                            </form>
                        </div>
                    @else
                        @if ($mensajeError)
                            <div class="alert-message warning-message">
                                {{ $mensajeError }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Validación del tamaño del archivo
    document.getElementById('documento')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file && file.size > maxSize) {
            alert('El archivo excede el tamaño máximo de 10MB');
            e.target.value = '';
        }
    });
</script>
@endpush
@endsection