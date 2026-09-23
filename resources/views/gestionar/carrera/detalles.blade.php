@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/usuario/detalles.css'])

<div class="detalle-container">

    <div class="detalle-header">
        <h1>🎓 Detalles de la Carrera</h1>
        <div class="detalle-acciones-superiores">
            <a href="{{ route('gestionarCarrera') }}" class="btn-volver-detalle">← Volver al listado</a>
            <a href="{{ route('gestionarCarrera', ['accion' => 'editar', 'id' => $carrera->id]) }}"
               class="btn-editar-detalle">✏️ Editar Carrera</a>
        </div>
    </div>

    @if (session('error'))
        <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    <!-- ============ INFO PRINCIPAL ============ -->
    <div class="detalle-seccion">
        <h2>📋 Información de la Carrera</h2>
        <div class="detalle-grid">
            <div class="detalle-campo detalle-campo-full">
                <label>Nombre de la Carrera:</label>
                <span><strong>{{ $carrera->Nombre_carrera }}</strong></span>
            </div>
            <div class="detalle-campo">
                <label>Facultad:</label>
                <span>
                    @if($carrera->facultad)
                        <span class="badge-rol-detalle">{{ $carrera->facultad->Siglas }}</span>
                        {{ $carrera->facultad->Nombre_facultad }}
                    @else
                        <em style="color:#6c757d;">Sin facultad</em>
                    @endif
                </span>
            </div>
            <div class="detalle-campo">
                <label>ID de la Carrera:</label>
                <span>{{ $carrera->id }}</span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Creación:</label>
                <span>{{ $carrera->created_at ? $carrera->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
            </div>
            <div class="detalle-campo">
                <label>Última Actualización:</label>
                <span>{{ $carrera->updated_at ? $carrera->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
            </div>
        </div>
    </div>

    <!-- ============ ESTADÍSTICAS ============ -->
    <div class="detalle-seccion">
        <h2>📊 Estadísticas</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>Total de Estudiantes:</label>
                <span><strong style="font-size:22px; color:#030;">{{ $carrera->cantidad_estudiantes }}</strong></span>
            </div>
            <div class="detalle-campo">
                <label>Total de Modalidades:</label>
                <span><strong style="font-size:22px; color:#030;">{{ count($modalidades_carrera) }}</strong></span>
            </div>
            <div class="detalle-campo">
                <label>Año Académico Mayor:</label>
                <span>
                    @if($carrera->estudiantes_por_ano->count() > 0)
                        {{ $carrera->estudiantes_por_ano->keys()->max() }}
                    @else
                        —
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- ============ MODALIDADES ============ -->
    <div class="detalle-seccion">
        <h2>📚 Modalidades de Estudio</h2>

        @if(count($modalidades_carrera) > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px; text-align:left;">#</th>
                            <th style="padding:12px 14px; text-align:left;">Modalidad</th>
                            <th style="padding:12px 14px; text-align:left;">Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modalidades_carrera as $index => $modalidad)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">{{ $index + 1 }}</td>
                                <td style="padding:10px 14px;"><strong>{{ $modalidad->Nombre_modalidad }}</strong></td>
                                <td style="padding:10px 14px;">
                                    <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#030,#0a3);">
                                        {{ $modalidad->cantidad_years }} año(s)
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="sin-datos">Esta carrera no tiene modalidades configuradas.</p>
        @endif
    </div>

    <!-- ============ ESTUDIANTES POR AÑO ============ -->
    @if($carrera->cantidad_estudiantes > 0)
        <div class="detalle-seccion">
            <h2>👥 Distribución de Estudiantes por Año Académico</h2>

            @foreach($carrera->estudiantes_por_ano as $ano => $estudiantes)
                <div class="detalle-subseccion">
                    <h3>Año Académico {{ $ano }} — {{ count($estudiantes) }} estudiantes</h3>

                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                            <thead>
                                <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                                    <th style="padding:10px 12px; text-align:left;">#</th>
                                    <th style="padding:10px 12px; text-align:left;">Nombre Completo</th>
                                    <th style="padding:10px 12px; text-align:left;">CI</th>
                                    <th style="padding:10px 12px; text-align:left;">Grupo</th>
                                    <th style="padding:10px 12px; text-align:left;">Modalidad</th>
                                    <th style="padding:10px 12px; text-align:left;">Fecha Ingreso</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estudiantes as $index => $est)
                                    <tr style="border-bottom:1px solid #e8f5e9;">
                                        <td style="padding:8px 12px;">{{ $index + 1 }}</td>
                                        <td style="padding:8px 12px;">
                                            {{ $est->Nombre_estudiante }}
                                            {{ $est->Apellido1 }}
                                            {{ $est->Apellido2 }}
                                        </td>
                                        <td style="padding:8px 12px;">{{ $est->CI_estudiante }}</td>
                                        <td style="padding:8px 12px;">
                                            @if($est->grupo)
                                                <span class="permiso-badge">Grupo {{ $est->grupo->número }}</span>
                                            @else
                                                <em style="color:#6c757d;">—</em>
                                            @endif
                                        </td>
                                        <td style="padding:8px 12px;">
                                            @if($est->modalidad)
                                                <span class="permiso-badge">{{ $est->modalidad->Nombre_modalidad }}</span>
                                            @else
                                                <em style="color:#6c757d;">—</em>
                                            @endif
                                        </td>
                                        <td style="padding:8px 12px;">
                                            {{ \Carbon\Carbon::parse($est->Fecha_ingreso)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ============ ACCIONES FINALES ============ -->
    <div class="detalle-acciones-inferiores">
        <a href="{{ route('gestionarCarrera') }}" class="btn-volver-detalle">← Volver</a>
        <a href="{{ route('gestionarCarrera', ['accion' => 'editar', 'id' => $carrera->id]) }}"
           class="btn-editar-detalle">✏️ Editar Carrera</a>

        <button type="button" class="btn-eliminar-detalle" onclick="confirmarEliminacion()">
            🗑️ Eliminar Carrera
        </button>
    </div>
</div>

<form id="formEliminar" method="POST" action="{{ route('eliminarCarrera') }}" style="display:none;">
    @csrf
    <input type="hidden" name="id" value="{{ $carrera->id }}">
</form>

<script>
    function confirmarEliminacion() {
        if (confirm('¿Está seguro de que desea eliminar esta carrera? Esta acción no se puede deshacer.')) {
            document.getElementById('formEliminar').submit();
        }
    }
</script>

@endsection