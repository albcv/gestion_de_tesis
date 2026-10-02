@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/index.css'])
@vite(['resources/css/gestionar/usuario/filtros.css'])

@php
    $esEstudiante = $posible->rol_solicitado === 'estudiante';

    $nombreCompleto = trim(
        ($posible->nombre_persona ?? '') . ' ' .
        ($posible->apellido1 ?? '') . ' ' .
        ($posible->apellido2 ?? '')
    );
@endphp

<div class="contenido-principal">
    <div class="contenedor-facultades">

        <div class="botones-superiores">
            <a href="{{ route('posiblesUsuarios') }}" class="btn-crear">
                ← Volver a Posibles Usuarios
            </a>
        </div>

        <h1>Detalle de la Solicitud</h1>
        <p style="color:#000; margin-top:-8px;">
            Revisa la información antes de aceptar o rechazar la solicitud.
        </p>

        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        {{-- ============================
             CABECERA CON ROL
             ============================ --}}
        <div class="detalle-card detalle-header-card">
            <div class="detalle-header">
                <div class="detalle-header-left">
                    @if($esEstudiante)
                        <span class="badge-rol-grande" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                            🎓 Solicitud como Estudiante
                        </span>
                    @else
                        <span class="badge-rol-grande" style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                            👨‍🏫 Solicitud como Profesor
                        </span>
                    @endif
                    <h2 class="detalle-nombre">{{ $nombreCompleto ?: $posible->name }}</h2>
                    <p class="detalle-fecha">
                        Solicitado el {{ $posible->created_at->format('d/m/Y \a \l\a\s H:i') }}
                    </p>
                </div>
                <div class="detalle-id">#{{ $posible->id }}</div>
            </div>
        </div>

        {{-- ============================
             DATOS DE LA CUENTA
             ============================ --}}
        <div class="detalle-card">
            <h3 class="detalle-section-title">🔑 Datos de la cuenta</h3>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Nombre de usuario:</span>
                    <span class="detalle-value">{{ $posible->name }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Email:</span>
                    <span class="detalle-value">{{ $posible->email }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Contraseña:</span>
                    <span class="detalle-value detalle-muted">•••••••• (hasheada)</span>
                </div>
            </div>
        </div>

        {{-- ============================
             DATOS PERSONALES
             ============================ --}}
        <div class="detalle-card">
            <h3 class="detalle-section-title">👤 Datos personales</h3>
            <div class="detalle-grid">
                <div class="detalle-item">
                    <span class="detalle-label">Carnet de Identidad:</span>
                    <span class="detalle-value">{{ $posible->ci ?? '—' }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Nombre:</span>
                    <span class="detalle-value">{{ $posible->nombre_persona ?? '—' }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Primer apellido:</span>
                    <span class="detalle-value">{{ $posible->apellido1 ?? '—' }}</span>
                </div>
                <div class="detalle-item">
                    <span class="detalle-label">Segundo apellido:</span>
                    <span class="detalle-value">{{ $posible->apellido2 ?? '—' }}</span>
                </div>
            </div>
        </div>

        {{-- ============================
             DATOS ESPECÍFICOS DEL ROL
             ============================ --}}
        @if($esEstudiante)
            <div class="detalle-card">
                <h3 class="detalle-section-title">🎓 Datos académicos (Estudiante)</h3>
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <span class="detalle-label">Sexo:</span>
                        <span class="detalle-value">{{ $posible->sexo ?? '—' }}</span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Año académico:</span>
                        <span class="detalle-value">
                            {{ $posible->year_academico ? $posible->year_academico . '° año' : '—' }}
                        </span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Grupo:</span>
                        <span class="detalle-value">
                            {{ $posible->grupo->número ? 'Grupo ' . $posible->grupo->número : '—' }}
                        </span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Modalidad:</span>
                        <span class="detalle-value">
                            {{ $posible->modalidad->Nombre_modalidad ?? '—' }}
                        </span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Carrera:</span>
                        <span class="detalle-value">
                            {{ $posible->carrera->Nombre_carrera ?? '—' }}
                        </span>
                    </div>
                </div>
            </div>
        @else
            <div class="detalle-card">
                <h3 class="detalle-section-title">👨‍🏫 Datos académicos (Profesor)</h3>
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <span class="detalle-label">Departamento:</span>
                        <span class="detalle-value">
                            {{ $posible->departamento->Nombre_departamento ?? '—' }}
                        </span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Categoría docente:</span>
                        <span class="detalle-value">{{ $posible->categoria_docente ?? '—' }}</span>
                    </div>
                    <div class="detalle-item">
                        <span class="detalle-label">Categoría científica:</span>
                        <span class="detalle-value">{{ $posible->categoria_cientifica ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================
             ACCIONES
             ============================ --}}
        <div class="detalle-card detalle-acciones">
            <h3 class="detalle-section-title">⚡ Acciones</h3>

            <div class="acciones-detalle">
                {{-- Aceptar --}}
                <form action="{{ route('aceptarPosibleUsuario', $posible->id) }}"
                      method="POST"
                      class="accion-form">
                    @csrf
                    <button type="submit" class="btn-accion btn-aceptar">
                        ✅ Aceptar y crear usuario
                    </button>
                </form>

                {{-- Rechazar --}}
                <form action="{{ route('rechazarPosibleUsuario', $posible->id) }}"
                      method="POST"
                      class="accion-form">
                    @csrf
                    <button type="submit" class="btn-accion btn-rechazar">
                        ❌ Rechazar solicitud
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
    /* ---------- Tarjetas de detalle ---------- */
    .detalle-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
    }

    .detalle-header-card {
        border-left: 4px solid #2563eb;
    }

    .detalle-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }

    .detalle-header-left { display: flex; flex-direction: column; gap: 6px; }

    .badge-rol-grande {
        display: inline-block;
        color: #fff;
        font-size: .78rem;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 999px;
        letter-spacing: .03em;
        text-transform: uppercase;
        align-self: flex-start;
    }

    .detalle-nombre {
        margin: 4px 0 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #1f2937;
    }

    .detalle-fecha {
        margin: 0;
        color: #6b7280;
        font-size: .85rem;
    }

    .detalle-id {
        font-family: monospace;
        color: #9ca3af;
        font-size: 1rem;
        font-weight: 600;
        background: #f3f4f6;
        padding: 4px 12px;
        border-radius: 6px;
    }

    .detalle-section-title {
        margin: 0 0 16px;
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        padding-bottom: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .detalle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .detalle-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .detalle-label {
        font-size: .78rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .detalle-value {
        font-size: .96rem;
        color: #1f2937;
        font-weight: 500;
        word-break: break-word;
    }

    .detalle-muted {
        color: #9ca3af;
        font-style: italic;
        font-weight: 400;
        font-size: .9rem;
    }

    /* ---------- Aviso del rol a asignar ---------- */
    .rol-asignado-aviso {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #eff6ff;
        border-left: 4px solid #2563eb;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 18px;
        color: #1e40af;
        font-size: .93rem;
        line-height: 1.5;
    }

    .rol-asignado-icono {
        font-size: 1.15rem;
        flex-shrink: 0;
        line-height: 1.4;
    }

    .rol-asignado-texto strong {
        font-weight: 700;
        color: #1d4ed8;
    }

    /* ---------- Acciones ---------- */
    .acciones-detalle {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
    }

    .accion-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .btn-accion {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 22px;
        font-size: .93rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s, transform .1s, box-shadow .15s;
        line-height: 1;
        white-space: nowrap;
    }

    .btn-aceptar {
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: #fff;
        box-shadow: 0 1px 3px rgba(22,163,74,.3);
    }
    .btn-aceptar:hover {
        background: linear-gradient(135deg, #15803d, #14532d);
        box-shadow: 0 6px 16px rgba(22,163,74,.35);
        transform: translateY(-1px);
    }

    .btn-rechazar {
        background: linear-gradient(135deg, #dc2626, #991b1b);
        color: #fff;
        box-shadow: 0 1px 3px rgba(220,38,38,.3);
    }
    .btn-rechazar:hover {
        background: linear-gradient(135deg, #b91c1c, #7f1d1d);
        box-shadow: 0 6px 16px rgba(220,38,38,.35);
        transform: translateY(-1px);
    }

    .btn-accion:active { transform: translateY(0) scale(.98); }

    /* ---------- Botón "Ver" del índice ---------- */
    .btn-ver-solicitud {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        font-size: .82rem;
        font-weight: 600;
        color: #1e40af;
        background: #dbeafe;
        border: 1px solid rgba(37,99,235,.35);
        border-radius: 6px;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s, transform .1s, box-shadow .15s;
    }
    .btn-ver-solicitud:hover {
        background: #bfdbfe;
        box-shadow: 0 2px 8px rgba(37,99,235,.2);
        transform: translateY(-1px);
    }
    .btn-ver-solicitud:active { transform: scale(.97); }

    @media (max-width: 640px) {
        .detalle-grid { grid-template-columns: 1fr; }
        .acciones-detalle { flex-direction: column; align-items: stretch; }
        .accion-form { width: 100%; }
        .btn-accion { width: 100%; }
    }
</style>

@endsection