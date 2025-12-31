@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/profesor/listaEstudiantesTutorados.css') }}">

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Estudiantes Tutorados</h2>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <div class="icon">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            @if ($estudiantesTutorados->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th><span class="icon"><i class="fas fa-user-graduate"></i> Estudiante</span></th>
                                <th><span class="icon"><i class="fas fa-id-card"></i> CI</span></th>
                                <th><span class="icon"><i class="fas fa-file-alt"></i> Tesis</span></th>
                                <th><span class="icon"><i class="fas fa-clipboard-check"></i> Fundamentación</span></th>
                                <th><span class="icon"><i class="fas fa-layer-group"></i> Cortes</span></th>
                                <th><span class="icon"><i class="fas fa-cogs"></i> Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @foreach ($estudiantesTutorados as $estudiante)
                                <tr>
                                    <td class="align-middle" data-label="Estudiante">
                                        {{ $estudiante->Nombre_estudiante }} 
                                        {{ $estudiante->Apellido1 }}
                                        {{ $estudiante->Apellido2 }}
                                    </td>
                                    <td class="align-middle" data-label="CI">
                                        {{ $estudiante->CI_estudiante }}
                                    </td>
                                    <td class="align-middle" data-label="Tesis">
                                        @if ($estudiante->tesis)
                                            <div class="tema-tesis-texto">
                                                {{ $estudiante->tesis->Nombre_trabajo }}
                                            </div>
                                        @else
                                            <span class="text-muted">Sin tesis registrada</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Fundamentación">
                                        @if ($estudiante->tesis && $estudiante->tesis->fundamentacion)
                                            @if ($estudiante->tesis->fundamentacion->aprobada)
                                                <span class="badge badge-success">Aprobada</span>
                                            @elseif ($estudiante->tesis->fundamentacion->desaprobada)
                                                <span class="badge badge-danger">Desaprobada</span>
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">
                                                Versiones: {{ $estudiante->tesis->fundamentacion->versiones ? $estudiante->tesis->fundamentacion->versiones->count() : 0 }}
                                            </small>
                                        @else
                                            <span class="badge badge-secondary">No disponible</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Cortes">
                                        @if ($estudiante->tesis && $estudiante->tesis->cortes)
                                            <div class="cortes-info">
                                                @foreach ($estudiante->tesis->cortes as $corte)
                                                    <div class="corte-item">
                                                        <span class="corte-numero">Corte {{ $corte->Numero_corte }}</span>
                                                        @if ($corte->aprobado)
                                                            <span class="badge badge-sm badge-success">Aprobado</span>
                                                        @elseif ($corte->desaprobado)
                                                            <span class="badge badge-sm badge-danger">Desaprobado</span>
                                                        @else
                                                            <span class="badge badge-sm badge-warning">Pendiente</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <small class="text-muted">
                                                    Total: {{ $estudiante->tesis->cortes->count() }} cortes
                                                </small>
                                            </div>
                                        @else
                                            <span class="badge badge-secondary">No disponible</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Acciones">
                                        <a href="{{ route('revisarEstudianteTutorado', $estudiante->id) }}" 
                                           class="btn btn-primary btn-sm">
                                            <span class="icon"><i class="fas fa-eye"></i> Revisar</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info alert-text-center">
                    <i class="fas fa-user-graduate fa-3x mb-3"></i>
                    <h4>No tienes estudiantes tutorados</h4>
                    <p>Cuando se te asignen estudiantes como tutor, aparecerán aquí.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(function() {
            document.querySelectorAll('.alert:not(.alert-info)').forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

        // Botón para cerrar alertas
        document.querySelectorAll('.btn-close').forEach(function(button) {
            button.addEventListener('click', function() {
                const alert = this.closest('.alert');
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        });
    });
</script>
@endsection