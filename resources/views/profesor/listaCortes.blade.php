@extends('layouts.app')

@vite(['resources/css/profesor/listado.css'])

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Cortes de Tesis Asignados para Revisión</h2>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    <div class="icon">
                        ✅ {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    <div class="icon">
                        ❌ {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
            @endif

            @if ($cortesAsignados->count() > 0)
                <div class="table-container">
                    <table class="table">
                        <thead class="table-header">
                            <tr>
                                <th><span class="icon">🎓 Estudiante</span></th>
                                <th><span class="icon"># Corte</span></th>
                                <th><span class="icon">📄 Tesis</span></th>
                                <th><span class="icon">🔀 Versiones</span></th>
                                <th><span class="icon">🏷️ Estado</span></th>
                                <th><span class="icon">📅 Fecha Creación</span></th>
                                <th><span class="icon">⚙️ Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @foreach ($cortesAsignados as $corte)
                                <tr>
                                    <td class="align-middle" data-label="Estudiante">
                                        {{ $corte->tesis->estudiante->Nombre_estudiante }} 
                                        {{ $corte->tesis->estudiante->Apellido1 }}
                                    </td>
                                    <td class="align-middle" data-label="Corte">
                                        <span class="badge badge-primary">
                                            Corte {{ $corte->Numero_corte }}
                                        </span>
                                    </td>
                                    <td class="align-middle" data-label="Tesis">
                                        <div class="tema-tesis-texto">
                                            {{ $corte->tesis->Nombre_trabajo }}
                                        </div>
                                    </td>
                                    <td class="align-middle" data-label="Versiones">
                                        @if ($corte->versiones && $corte->versiones->count() > 0)
                                            <span class="badge badge-info">
                                                v{{ $corte->versiones->last()->version_numero }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">Sin versiones</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Estado">
                                        @if ($corte->aprobado)
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif ($corte->desaprobado)
                                            <span class="badge badge-danger">Desaprobado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="align-middle" data-label="Fecha Creación">
                                        {{ $corte->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="align-middle" data-label="Acciones">
                                        <a href="{{ route('revisarCorteEstudiante', $corte->idCortes_de_tesis) }}" 
                                           class="btn btn-primary btn-sm">
                                            <span class="icon">👁️ Revisar</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info alert-text-center">
                    <div style="font-size:48px; margin-bottom:14px;">📥</div>
                    <h4>No hay cortes asignados</h4>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('.alert:not(.alert-info)').forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);

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