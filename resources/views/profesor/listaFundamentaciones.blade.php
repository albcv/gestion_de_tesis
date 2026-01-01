@extends('layouts.app')

@vite(['resources/css/profesor/listaFundamentaciones.css'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Fundamentaciones Asignadas para Revisión</h2>

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

            @if ($fundamentacionesAsignadas->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th><span class="icon"><i class="fas fa-user-graduate"></i> Estudiante</span></th>
                                <th><span class="icon"><i class="fas fa-id-card"></i> CI</span></th>
                                <th><span class="icon"><i class="fas fa-file-alt"></i> Tesis</span></th>
                                <th><span class="icon"><i class="fas fa-code-branch"></i> Versiones</span></th>
                                <th><span class="icon"><i class="fas fa-tag"></i> Estado</span></th>
                                <th><span class="icon"><i class="fas fa-cogs"></i> Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @foreach ($fundamentacionesAsignadas as $fundamentacion)
                                <tr>
                                    <td class="align-middle" data-label="Estudiante">
                                        {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }} 
                                        {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                                    </td>
                                    <td class="align-middle" data-label="CI">
                                        {{ $fundamentacion->tesis->estudiante->CI_estudiante }}
                                    </td>
                                    <td class="align-middle" data-label="Tesis">
                                        <div class="tema-tesis-texto">
                                            {{ $fundamentacion->tesis->Nombre_trabajo }}
                                        </div>
                                    </td>
                                    <td class="align-middle" data-label="Versiones">
                                        @if ($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
                                            <span class="badge badge-info">
                                                v{{ $fundamentacion->versiones->last()->version_numero }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Sin versiones</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Estado">
                                        @if ($fundamentacion->aprobada)
                                            <span class="badge badge-success">Aprobada</span>
                                        @elseif ($fundamentacion->desaprobada)
                                            <span class="badge badge-danger">Desaprobada</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                        
                                    <td class="align-middle" data-label="Acciones">
                                        <a href="{{ route('revisarFundamentaciónEstudiante', $fundamentacion->id_fundamentacion) }}" 
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
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <h4>No hay fundamentaciones asignadas</h4>
                    <p>Cuando te asignen fundamentaciones, aparecerán aquí.</p>
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