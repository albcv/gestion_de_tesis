@extends('layouts.app')

@section('content')

@vite(['resources/css/acciones_recientes.css'])

<div class="acciones-container">

    {{-- ============================
         CABECERA
         ============================ --}}
    <div class="acciones-header">
        <div class="acciones-header-left">
            <h1>⚡ Acciones Recientes</h1>
            <p class="acciones-subtitle">
                Subidas y actualizaciones de tesis, fundamentaciones y cortes de los últimos
                <strong>{{ $dias }}</strong> día{{ $dias === 1 ? '' : 's' }}.
            </p>
        </div>
        <a href="{{ route('inicio') }}" class="btn-volver">← Volver al inicio</a>
    </div>

    {{-- ============================
         FILTRO DE DÍAS
         ============================ --}}
    <div class="acciones-filtro">
        <span class="filtro-label">Mostrar acciones de los últimos:</span>

        @foreach ([1, 3, 7, 15, 30] as $opcion)
            <a href="{{ route('accionesRecientes', ['dias' => $opcion]) }}"
               class="filtro-btn {{ (int) $dias === $opcion ? 'active' : '' }}">
                {{ $opcion }} día{{ $opcion === 1 ? '' : 's' }}
            </a>
        @endforeach
    </div>

    {{-- ============================
         RESUMEN
         ============================ --}}
    @php
        $totalTesis          = $acciones->where('tipo', 'tesis')->count();
        $totalFundamentaciones = $acciones->where('tipo', 'fundamentacion')->count();
        $totalCortes         = $acciones->where('tipo', 'corte')->count();
        $totalAcciones       = $acciones->count();
    @endphp

    <div class="acciones-resumen">
        <div class="resumen-card resumen-total">
            <div class="resumen-icono">📊</div>
            <div class="resumen-info">
                <div class="resumen-valor">{{ $totalAcciones }}</div>
                <div class="resumen-label">Total de acciones</div>
            </div>
        </div>

        <div class="resumen-card resumen-tesis">
            <div class="resumen-icono">📝</div>
            <div class="resumen-info">
                <div class="resumen-valor">{{ $totalTesis }}</div>
                <div class="resumen-label">Tesis</div>
            </div>
        </div>

        <div class="resumen-card resumen-fundamentacion">
            <div class="resumen-icono">📄</div>
            <div class="resumen-info">
                <div class="resumen-valor">{{ $totalFundamentaciones }}</div>
                <div class="resumen-label">Fundamentaciones</div>
            </div>
        </div>

        <div class="resumen-card resumen-corte">
            <div class="resumen-icono">📚</div>
            <div class="resumen-info">
                <div class="resumen-valor">{{ $totalCortes }}</div>
                <div class="resumen-label">Cortes</div>
            </div>
        </div>
    </div>

    {{-- ============================
         TABLA DE ACCIONES
         ============================ --}}
    @if ($acciones->count() > 0)
        <div class="acciones-table-wrapper">
            <table class="acciones-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Estudiante</th>
                        <th>CI</th>
                        <th>Trabajo de Diploma</th>
                        <th>Versión</th>
                        <th>Acción</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($acciones as $accion)
                        @php
                            $esFundamentacion = $accion->tipo === 'fundamentacion';
                            $esCorte          = $accion->tipo === 'corte';
                            $esTesis          = $accion->tipo === 'tesis';
                            $fecha = \Carbon\Carbon::createFromTimestamp($accion->fecha_accion);
                            $hace = $fecha->diffForHumans();
                        @endphp
                        <tr>
                            {{-- Tipo --}}
                            <td>
                                @if ($esTesis)
                                    <span class="badge-tipo badge-tesis">
                                        📝 Tesis
                                    </span>
                                @elseif ($esFundamentacion)
                                    <span class="badge-tipo badge-fundamentacion">
                                        📄 Fundamentación
                                    </span>
                                @else
                                    <span class="badge-tipo badge-corte">
                                        📚 Corte {{ $accion->numero_corte }}
                                    </span>
                                @endif
                            </td>

                            {{-- Estudiante --}}
                            <td class="td-estudiante">
                                <strong>{{ $accion->nombre_completo ?: '—' }}</strong>
                            </td>

                            {{-- CI --}}
                            <td class="td-ci">{{ $accion->CI_estudiante ?? '—' }}</td>

                            {{-- Trabajo --}}
                            <td class="td-trabajo">
                                {{ \Illuminate\Support\Str::limit($accion->Nombre_trabajo ?? '—', 60) }}
                            </td>

                            {{-- Versión --}}
                            <td class="td-version">
                                @if ($esTesis)
                                    <span class="version-tag version-na">—</span>
                                @else
                                    <span class="version-tag">v{{ $accion->version_numero }}</span>
                                @endif
                            </td>

                            {{-- Acción --}}
                            <td>
                                @if ($accion->es_creacion)
                                    <span class="accion-badge accion-subio">
                                        @if ($esTesis)
                                            ✨ Creó
                                        @else
                                            ⬆️ Subió
                                        @endif
                                    </span>
                                @else
                                    <span class="accion-badge accion-actualizo">
                                        🔄 Actualizó
                                    </span>
                                @endif
                            </td>

                          

                            {{-- Fecha --}}
                            <td class="td-fecha">
                                <div class="fecha-principal">{{ $fecha->format('d/m/Y') }}</div>
                                <div class="fecha-hora">{{ $fecha->format('H:i') }}</div>
                                <div class="fecha-hace">{{ $hace }}</div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="acciones-vacio">
            <div class="vacio-icono">📭</div>
            <h3>No hay acciones recientes</h3>
            <p>
                No se han creado ni actualizado tesis, fundamentaciones ni cortes en los
                últimos {{ $dias }} día{{ $dias === 1 ? '' : 's' }}.
            </p>
            <a href="{{ route('accionesRecientes', ['dias' => 7]) }}" class="btn-ampliar">
                Ver últimos 7 días
            </a>
        </div>
    @endif

</div>

@endsection