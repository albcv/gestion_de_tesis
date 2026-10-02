@extends('layouts.app')

@vite(['resources/css/estudiante/cambiarTesis.css'])

@section('content')
<div class="cambiar-tesis-container">
    <div class="cambiar-tesis-content">

        <h2 class="page-title">
            @if ($tieneTesis)
                Cambiar Nombre de la Tesis
            @else
                Crear Tesis
            @endif
        </h2>

        <!-- Mensajes de sesión -->
        @if (session('success'))
            <div class="alert-message success-message">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert-message error-message">{{ session('error') }}</div>
        @endif

        @if (session('info'))
            <div class="alert-message info-message">{{ session('info') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-message error-message">
                <ul style="margin:0; padding-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Aviso si la fundamentación ya está aprobada --}}
        @if ($fundamentacionAprobada)
            <div class="alert-message warning-message">
                <strong>⚠️ Atención:</strong> Tu fundamentación ya está aprobada.
                Cambiar el nombre de la tesis ahora puede afectar los documentos ya registrados.
                Si necesitas cambiarlo, contacta primero con tu tutor o administrador.
            </div>
        @endif

        {{-- ============================
             INFO DEL ESTUDIANTE
             ============================ --}}
        <div class="card-container">
            <div class="card-header primary-header">
                <h3 class="card-title">Información del Estudiante</h3>
            </div>
            <div class="card-body">
                <div class="card-row">
                    <div class="card-column">
                        <p class="label-text"><strong>Nombre:</strong></p>
                        <p class="content-text">
                            {{ $estudiante->Nombre_estudiante }}
                            {{ $estudiante->Apellido1 }}
                            {{ $estudiante->Apellido2 }}
                        </p>
                    </div>
                    <div class="card-column">
                        <p class="label-text"><strong>CI:</strong></p>
                        <p class="content-text">{{ $estudiante->CI_estudiante }}</p>
                    </div>
                </div>
                <div class="card-row">
                    <div class="card-column">
                        <p class="label-text"><strong>Carrera:</strong></p>
                        <p class="content-text">
                            {{ $estudiante->carrera->Nombre_carrera ?? 'No especificada' }}
                        </p>
                    </div>
                    <div class="card-column">
                        <p class="label-text"><strong>Año académico:</strong></p>
                        <p class="content-text">{{ $estudiante->year_academico }}° año</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================
             TESIS ACTUAL (si existe)
             ============================ --}}
        @if ($tieneTesis)
            <div class="card-container">
                <div class="card-header">
                    <h3 class="card-title">Tesis Actual</h3>
                </div>
                <div class="card-body">
                    <p class="label-text"><strong>Nombre actual:</strong></p>
                    <div class="tesis-actual-box">
                        {{ $tesis->Nombre_trabajo }}
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================
             FORMULARIO
             ============================ --}}
        <div class="card-container">
            <div class="card-header">
                <h3 class="card-title">
                    @if ($tieneTesis)
                        Nuevo Nombre de la Tesis
                    @else
                        Nombre de la Tesis
                    @endif
                </h3>
            </div>
            <div class="card-body">

                <form action="{{ route('guardarCambiarTesis') }}"
                      method="POST"
                      class="form-cambiar-tesis"
                      id="formCambiarTesis">
                    @csrf

                    <div class="form-group">
                        <label for="nombre_tesis" class="form-label">
                            Nombre del Trabajo de Diploma *
                        </label>

                        <div class="textarea-container">
                            <textarea id="nombre_tesis"
                                      name="nombre_tesis"
                                      rows="4"
                                      required
                                      minlength="10"
                                      maxlength="300"
                                      placeholder="Escribe aquí el nombre completo de tu trabajo de diploma..."
                                      autofocus>{{ old('nombre_tesis', $tesis->Nombre_trabajo ?? '') }}</textarea>
                            <div class="textarea-footer">
                                <span class="char-count" id="charCount">0 / 300</span>
                            </div>
                        </div>

                       
                    </div>

                    <div class="form-buttons">
                        <a href="{{ route('inicio') }}" class="btn-cancelar">
                            ← Cancelar
                        </a>
                        <button type="submit" class="btn-guardar">
                            @if ($tieneTesis)
                                💾 Actualizar Nombre
                            @else
                                ➕ Crear Tesis
                            @endif
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea  = document.getElementById('nombre_tesis');
    const charCount = document.getElementById('charCount');

    if (!textarea || !charCount) return;

    function actualizar() {
        const length = textarea.value.length;
        charCount.textContent = `${length} / 300`;

        if (length > 280) {
            charCount.style.color = '#dc2626';
            charCount.style.fontWeight = 'bold';
        } else if (length > 250) {
            charCount.style.color = '#d97706';
            charCount.style.fontWeight = 'bold';
        } else {
            charCount.style.color = '';
            charCount.style.fontWeight = '';
        }
    }

    textarea.addEventListener('input', actualizar);
    actualizar(); // inicializar
});
</script>
@endpush