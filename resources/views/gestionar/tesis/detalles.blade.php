@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/usuario/detalles.css'])

<div class="detalle-container">

    <div class="detalle-header">
        <h1>📚 Detalles de la Tesis</h1>
        <div class="detalle-acciones-superiores">
            <a href="{{ route('gestionarTesis') }}" class="btn-volver-detalle">← Volver a la lista</a>
            <a href="{{ route('gestionarTesis', ['accion' => 'editar', 'id' => $tesis->id]) }}"
               class="btn-editar-detalle">✏️ Editar Tesis</a>
        </div>
    </div>

    @if (session('error'))
        <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    <!-- =============== INFO DE LA TESIS =============== -->
    <div class="detalle-seccion">
        <h2>📝 Información de la Tesis</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>ID de la Tesis:</label>
                <span>{{ $tesis->id }}</span>
            </div>
            <div class="detalle-campo detalle-campo-full">
                <label>Nombre del Trabajo:</label>
                <span>{{ $tesis->Nombre_trabajo }}</span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Creación:</label>
                <span>{{ $tesis->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Última Actualización:</label>
                <span>{{ $tesis->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- =============== INFO DEL ESTUDIANTE =============== -->
    @if($tesis->estudiante)
    <div class="detalle-seccion">
        <h2>🎓 Información del Estudiante</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>Nombre Completo:</label>
                <span>
                    {{ $tesis->estudiante->Nombre_estudiante }}
                    {{ $tesis->estudiante->Apellido1 }}
                    {{ $tesis->estudiante->Apellido2 }}
                </span>
            </div>
            <div class="detalle-campo">
                <label>CI del Estudiante:</label>
                <span>{{ $tesis->estudiante->CI_estudiante }}</span>
            </div>
            <div class="detalle-campo">
                <label>Sexo:</label>
                <span>{{ $tesis->estudiante->sexo }}</span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Ingreso:</label>
                <span>{{ \Carbon\Carbon::parse($tesis->estudiante->Fecha_ingreso)->format('d/m/Y') }}</span>
            </div>
            @if($tesis->estudiante->grupo)
                <div class="detalle-campo">
                    <label>Grupo:</label>
                    <span>{{ $tesis->estudiante->grupo->número }}</span>
                </div>
            @endif
            @if($tesis->estudiante->modalidad)
                <div class="detalle-campo">
                    <label>Modalidad:</label>
                    <span>{{ $tesis->estudiante->modalidad->Nombre_modalidad }}</span>
                </div>
            @endif
            @if($tesis->estudiante->carrera)
                <div class="detalle-campo">
                    <label>Carrera:</label>
                    <span>{{ $tesis->estudiante->carrera->Nombre_carrera }}</span>
                </div>
            @endif
            @if($tesis->estudiante->carrera && $tesis->estudiante->carrera->facultad)
                <div class="detalle-campo">
                    <label>Facultad:</label>
                    <span>{{ $tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                </div>
            @endif

            <div class="detalle-campo">
                <label>Tutor:</label>
                <span>
                    @if($tutor && $tutor->profesor)
                        {{ $tutor->profesor->Nombre_profesor }}
                        {{ $tutor->profesor->Apellido1 }}
                        {{ $tutor->profesor->Apellido2 }}
                    @else
                        <em style="color:#6c757d;">No asignado</em>
                    @endif
                </span>
            </div>
        </div>
    </div>
    @endif

    <!-- =============== FUNDAMENTACIÓN =============== -->
    <div class="detalle-seccion">
        <h2>📖 Fundamentación de la Tesis</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px;">
            @if($tesis->fundamentacion)
                <a href="{{ route('editarFundamentación', $tesis->fundamentacion->id_fundamentacion) }}"
                   class="btn-editar-detalle">✏️ Gestionar Versiones</a>
                <button type="button" class="btn-eliminar-detalle"
                        onclick="eliminarFundamentacion({{ $tesis->fundamentacion->id_fundamentacion }})">
                    🗑️ Eliminar Fundamentación
                </button>
            @else
                <a href="{{ route('crearFundamentación', ['tesis_id' => $tesis->id]) }}"
                   class="btn-editar-detalle">➕ Agregar Fundamentación</a>
            @endif
        </div>

        @if($tesis->fundamentacion)
            <div class="detalle-grid">
                <div class="detalle-campo">
                    <label>ID Fundamentación:</label>
                    <span>{{ $tesis->fundamentacion->id_fundamentacion }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Estado:</label>
                    <span>
                        @if($tesis->fundamentacion->aprobada)
                            <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobada</span>
                        @elseif($tesis->fundamentacion->desaprobada)
                            <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobada</span>
                        @else
                            <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                        @endif
                    </span>
                </div>
                <div class="detalle-campo">
                    <label>Fecha de Creación:</label>
                    <span>{{ $tesis->fundamentacion->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Total de Versiones:</label>
                    <span>{{ $tesis->fundamentacion->versiones->count() }}</span>
                </div>
                @if($tesis->fundamentacion->recomendacion && !empty($tesis->fundamentacion->recomendacion->recomendacion))
                    <div class="detalle-campo detalle-campo-full">
                        <label>Recomendación:</label>
                        <span>{{ $tesis->fundamentacion->recomendacion->recomendacion }}</span>
                    </div>
                @endif
            </div>

            <!-- Botones de estado -->
            <div class="detalle-subseccion">
                <h3>Acciones de estado</h3>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <form action="{{ route('aprobarFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-editar-detalle"
                                @if($tesis->fundamentacion->aprobada) disabled @endif>✅ Aprobar</button>
                    </form>
                    <form action="{{ route('desaprobarFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-eliminar-detalle"
                                @if($tesis->fundamentacion->desaprobada) disabled @endif>❌ Desaprobar</button>
                    </form>
                    <form action="{{ route('revertirFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-volver-detalle"
                                @if(!$tesis->fundamentacion->aprobada && !$tesis->fundamentacion->desaprobada) disabled @endif>↩️ Revertir</button>
                    </form>
                </div>
            </div>

            <!-- Versiones de la fundamentación -->
            @if($tesis->fundamentacion->versiones && $tesis->fundamentacion->versiones->count() > 0)
                <div class="detalle-subseccion">
                    <h3>📄 Versiones de la Fundamentación</h3>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                            <thead>
                                <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                                    <th style="padding:12px 14px; text-align:left;">Versión</th>
                                    <th style="padding:12px 14px; text-align:left;">Archivo</th>
                                    <th style="padding:12px 14px; text-align:left;">Tamaño</th>
                                    <th style="padding:12px 14px; text-align:left;">Descripción</th>
                                    <th style="padding:12px 14px; text-align:left;">Fecha</th>
                                    <th style="padding:12px 14px; text-align:center;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tesis->fundamentacion->versiones as $version)
                                    <tr style="border-bottom:1px solid #e8f5e9;">
                                        <td style="padding:10px 14px;"><strong>v{{ $version->version_numero }}</strong></td>
                                        <td style="padding:10px 14px;">{{ $version->nombre_archivo }}</td>
                                        <td style="padding:10px 14px;">
                                            @if($version->tamanio >= 1048576)
                                                {{ number_format($version->tamanio / 1048576, 2) }} MB
                                            @elseif($version->tamanio >= 1024)
                                                {{ number_format($version->tamanio / 1024, 2) }} KB
                                            @else
                                                {{ $version->tamanio }} bytes
                                            @endif
                                        </td>
                                        <td style="padding:10px 14px;">
                                            {{ $version->descripcion ? Str::limit($version->descripcion, 50) : '—' }}
                                        </td>
                                        <td style="padding:10px 14px;">{{ $version->created_at->format('d/m/Y H:i') }}</td>
                                        <td style="padding:10px 14px; text-align:center;">
                                            <a href="{{ route('ver-documento-version', $version->id) }}"
                                               style="text-decoration:none; font-size:20px;" title="Descargar">📥</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <p class="sin-datos">Esta tesis no tiene una fundamentación asociada.</p>
        @endif
    </div>

    <!-- =============== CORTES =============== -->
    @if($tesis->fundamentacion && $tesis->fundamentacion->aprobada)
    <div class="detalle-seccion">
        <h2>✂️ Cortes de Tesis</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
            <a href="{{ route('crearCorte', ['tesis_id' => $tesis->id]) }}" class="btn-editar-detalle">
                ➕ Agregar Corte
            </a>
        </div>

        @if($tesis->cortes && $tesis->cortes->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px;">#</th>
                            <th style="padding:12px 14px;">Corte</th>
                            <th style="padding:12px 14px;">Estado</th>
                            <th style="padding:12px 14px;">Versiones</th>
                            <th style="padding:12px 14px;">Fecha</th>
                            <th style="padding:12px 14px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tesis->cortes as $corte)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">{{ $corte->idCortes_de_tesis }}</td>
                                <td style="padding:10px 14px;"><strong>Corte #{{ $corte->Numero_corte }}</strong></td>
                                <td style="padding:10px 14px;">
                                    @if($corte->aprobado)
                                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobado</span>
                                    @elseif($corte->desaprobado)
                                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobado</span>
                                    @else
                                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                                    @endif
                                </td>
                                <td style="padding:10px 14px;">
                                    @if($corte->versiones && $corte->versiones->count() > 0)
                                        @foreach($corte->versiones as $version)
                                            <div style="margin-bottom:6px;">
                                                <strong>v{{ $version->version_numero }}</strong>
                                                <a href="{{ route('ver-documento-version-corte', $version->id) }}"
                                                   style="text-decoration:none; margin-left:6px;" title="Descargar">📥</a>
                                                @if($version->Enlace_Github)
                                                    <a href="{{ $version->Enlace_Github }}" target="_blank"
                                                       style="margin-left:6px; color:#0366d6;">🔗</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="sin-datos" style="padding:0; background:none; border:none;">Sin versiones</span>
                                    @endif
                                </td>
                                <td style="padding:10px 14px;">{{ $corte->created_at->format('d/m/Y') }}</td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <div style="display:flex; gap:6px; justify-content:center; flex-wrap:wrap;">
                                        <a href="{{ route('verCorte', $corte->idCortes_de_tesis) }}"
                                           style="text-decoration:none; font-size:20px;" title="Ver">👁️</a>
                                        @if(!$corte->aprobado && !$corte->desaprobado)
                                            <a href="{{ route('editarCorte', $corte->idCortes_de_tesis) }}"
                                               style="text-decoration:none; font-size:20px;" title="Editar">✏️</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="sin-datos">Esta tesis no tiene cortes registrados.</p>
        @endif
    </div>
    @elseif($tesis->fundamentacion && !$tesis->fundamentacion->aprobada)
    <div class="detalle-seccion">
        <h2>✂️ Cortes de Tesis</h2>
        <div class="sin-datos" style="border-left-color:#f59e0b;">
            ⚠️ No se pueden crear cortes hasta que la fundamentación sea aprobada.
        </div>
    </div>
    @endif

    <!-- =============== ACCIONES =============== -->
    <div class="detalle-acciones-inferiores">
        <a href="{{ route('gestionarTesis') }}" class="btn-volver-detalle">← Volver</a>
        <a href="{{ route('gestionarTesis', ['accion' => 'editar', 'id' => $tesis->id]) }}"
           class="btn-editar-detalle">✏️ Editar Tesis</a>
        <button type="button" class="btn-eliminar-detalle" onclick="confirmarEliminacion()">
            🗑️ Eliminar Tesis
        </button>
    </div>
</div>

<!-- Formularios ocultos -->
<form id="formEliminarTesis" method="POST" action="{{ route('eliminarTesis') }}" style="display:none;">
    @csrf
    <input type="hidden" name="id" value="{{ $tesis->id }}">
</form>

@if($tesis->fundamentacion)
<form id="formEliminarFundamentacion" method="POST" action="/eliminarFundamentación" style="display:none;">
    @csrf
    <input type="hidden" name="id" value="{{ $tesis->fundamentacion->id_fundamentacion }}">
</form>
@endif

<script>
    function confirmarEliminacion() {
        if (confirm('¿Está seguro de que desea eliminar esta tesis?')) {
            document.getElementById('formEliminarTesis').submit();
        }
    }

    function eliminarFundamentacion(id) {
        if (confirm('¿Está seguro de que desea eliminar esta fundamentación?')) {
            document.getElementById('formEliminarFundamentacion').submit();
        }
    }
</script>

@endsection