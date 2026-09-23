@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/usuario/detalles.css'])

<div class="detalle-container">

    <div class="detalle-header">
        <h1>📖 Detalles de la Fundamentación</h1>
        <div class="detalle-acciones-superiores">
            <a href="{{ route('gestionarFundamentaciones') }}" class="btn-volver-detalle">← Volver a la lista</a>
            @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                <a href="{{ route('gestionarFundamentaciones', ['accion' => 'editar', 'id' => $fundamentacion->id_fundamentacion]) }}"
                   class="btn-editar-detalle">✏️ Editar Fundamentación</a>
            @endif
        </div>
    </div>

    @if (session('error'))
        <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    <!-- ============ INFO FUNDAMENTACIÓN ============ -->
    <div class="detalle-seccion">
        <h2>📋 Información de la Fundamentación</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>ID:</label>
                <span>{{ $fundamentacion->id_fundamentacion }}</span>
            </div>
            <div class="detalle-campo">
                <label>Estado:</label>
                <span>
                    @if($fundamentacion->aprobada)
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobada</span>
                    @elseif($fundamentacion->desaprobada)
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobada</span>
                    @else
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                    @endif
                </span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Creación:</label>
                <span>{{ $fundamentacion->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Última Actualización:</label>
                <span>{{ $fundamentacion->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Total de Versiones:</label>
                <span>{{ $fundamentacion->versiones->count() }}</span>
            </div>
        </div>

        <!-- Botones de estado -->
        <div class="detalle-subseccion">
            <h3>Acciones de estado</h3>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
                    <form action="{{ route('aprobarFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-editar-detalle">✅ Aprobar</button>
                    </form>
                    <form action="{{ route('desaprobarFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-eliminar-detalle">❌ Desaprobar</button>
                    </form>
                @else
                    <form action="{{ route('revertirFundamentación') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
                        <button type="submit" class="btn-volver-detalle">↩️ Revertir a pendiente</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- ============ INFO TESIS ============ -->
    @if($fundamentacion->tesis)
        <div class="detalle-seccion">
            <h2>📚 Información de la Tesis</h2>
            <div class="detalle-grid">
                <div class="detalle-campo detalle-campo-full">
                    <label>Trabajo de Diploma:</label>
                    <span>{{ $fundamentacion->tesis->Nombre_trabajo }}</span>
                </div>

                @if($fundamentacion->tesis->estudiante)
                    <div class="detalle-campo">
                        <label>Estudiante:</label>
                        <span>
                            {{ $fundamentacion->tesis->estudiante->Nombre_estudiante }}
                            {{ $fundamentacion->tesis->estudiante->Apellido1 }}
                            {{ $fundamentacion->tesis->estudiante->Apellido2 }}
                        </span>
                    </div>
                    <div class="detalle-campo">
                        <label>CI:</label>
                        <span>{{ $fundamentacion->tesis->estudiante->CI_estudiante }}</span>
                    </div>

                    @php
                        $tutor = App\Models\Profesor::join('tutor_estudiante', 'profesor.id', '=', 'tutor_estudiante.id_profesor')
                            ->where('tutor_estudiante.id_estudiante', $fundamentacion->tesis->estudiante->id)
                            ->first();
                    @endphp

                    <div class="detalle-campo">
                        <label>Tutor:</label>
                        <span>
                            @if($tutor)
                                {{ $tutor->Nombre_profesor }} {{ $tutor->Apellido1 }} {{ $tutor->Apellido2 }}
                            @else
                                <em style="color:#6c757d;">No asignado</em>
                            @endif
                        </span>
                    </div>

                    @if($fundamentacion->tesis->estudiante->grupo)
                        <div class="detalle-campo">
                            <label>Grupo:</label>
                            <span>{{ $fundamentacion->tesis->estudiante->grupo->número }}</span>
                        </div>
                    @endif

                    @if($fundamentacion->tesis->estudiante->carrera)
                        <div class="detalle-campo">
                            <label>Carrera:</label>
                            <span>{{ $fundamentacion->tesis->estudiante->carrera->Nombre_carrera }}</span>
                        </div>
                        @if($fundamentacion->tesis->estudiante->carrera->facultad)
                            <div class="detalle-campo">
                                <label>Facultad:</label>
                                <span>{{ $fundamentacion->tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- ============ VERSIONES ============ -->
    <div class="detalle-seccion">
        <h2>📄 Versiones de la Fundamentación</h2>

        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
            <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
                <a href="{{ route('gestionarFundamentaciones', ['accion' => 'editar', 'id' => $fundamentacion->id_fundamentacion]) }}"
                   class="btn-editar-detalle">✏️ Gestionar Versiones</a>
            </div>
        @endif

        @if($fundamentacion->versiones && $fundamentacion->versiones->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px; text-align:left;">Versión</th>
                            <th style="padding:12px 14px; text-align:left;">Archivo</th>
                            <th style="padding:12px 14px; text-align:left;">Tamaño</th>
                            <th style="padding:12px 14px; text-align:left;">Descripción</th>
                            <th style="padding:12px 14px; text-align:left;">Fecha</th>
                            <th style="padding:12px 14px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundamentacion->versiones as $version)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">
                                    <strong>v{{ $version->version_numero }}</strong>
                                    @if($loop->first)
                                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#2563eb,#1e40af); font-size:11px;">Actual</span>
                                    @endif
                                </td>
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
                                    {{ $version->descripcion ? Str::limit($version->descripcion, 60) : '—' }}
                                </td>
                                <td style="padding:10px 14px;">{{ $version->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <div style="display:flex; gap:8px; justify-content:center;">
                                        <a href="{{ route('ver-documento-version', $version->id) }}"
                                           style="text-decoration:none; font-size:20px;" title="Descargar">📥</a>

                                        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada && $fundamentacion->versiones->count() > 1)
                                            <form method="POST"
                                                  action="{{ route('eliminar-version-fundamentacion', $version->id) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('¿Está seguro de eliminar esta versión?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        style="background:none; border:none; cursor:pointer; font-size:20px; padding:0;"
                                                        title="Eliminar">🗑️</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="sin-datos">Esta fundamentación no tiene versiones registradas.</p>
        @endif
    </div>

    <!-- ============ RECOMENDACIÓN ============ -->
    <div class="detalle-seccion">
        <h2>💡 Recomendación</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap;">
            @if($fundamentacion->recomendacion)
                <a href="/editarRecomendacionFundamentacion/{{ $fundamentacion->recomendacion->id_recomendaciones_fundamentacion }}"
                   class="btn-editar-detalle">✏️ Modificar</a>
                <button type="button" class="btn-eliminar-detalle"
                        onclick="eliminarRecomendacion({{ $fundamentacion->recomendacion->id_recomendaciones_fundamentacion }})">
                    🗑️ Eliminar
                </button>
            @else
                <a href="/agregarRecomendacionFundamentacion/{{ $fundamentacion->id_fundamentacion }}"
                   class="btn-editar-detalle">➕ Agregar Recomendación</a>
            @endif
        </div>

        @if($fundamentacion->recomendacion && !empty($fundamentacion->recomendacion->recomendacion))
            <div class="detalle-campo detalle-campo-full">
                <label>Contenido:</label>
                <span style="white-space:pre-wrap;">{{ $fundamentacion->recomendacion->recomendacion }}</span>
            </div>
            <div class="detalle-grid" style="margin-top:14px;">
                <div class="detalle-campo">
                    <label>Fecha de Creación:</label>
                    <span>{{ $fundamentacion->recomendacion->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Última Actualización:</label>
                    <span>{{ $fundamentacion->recomendacion->updated_at->format('d/m/Y') }}</span>
                </div>
            </div>
        @else
            <p class="sin-datos">Esta fundamentación no tiene una recomendación asociada.</p>
        @endif
    </div>

    <!-- ============ PROFESORES OPONENTES ============ -->
    <div class="detalle-seccion">
        <h2>👨‍🏫 Profesores Oponentes</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
            <a href="{{ route('vincularProfesorFundamentación', ['id' => $fundamentacion->id_fundamentacion]) }}"
               class="btn-editar-detalle">➕ Vincular Profesor</a>
        </div>

        @if($fundamentacion->profesores && $fundamentacion->profesores->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px; text-align:left;">#</th>
                            <th style="padding:12px 14px; text-align:left;">Nombre</th>
                            <th style="padding:12px 14px; text-align:left;">Departamento</th>
                            <th style="padding:12px 14px; text-align:left;">Categoría</th>
                            <th style="padding:12px 14px; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($fundamentacion->profesores as $index => $profesor)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">{{ $index + 1 }}</td>
                                <td style="padding:10px 14px;">
                                    <strong>{{ $profesor->Nombre_profesor }}</strong><br>
                                    {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                </td>
                                <td style="padding:10px 14px;">
                                    {{ $profesor->departamento->Nombre_departamento ?? '—' }}
                                </td>
                                <td style="padding:10px 14px;">{{ $profesor->Categoria_docente }}</td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <form method="POST"
                                          action="{{ route('desvincularProfesorFundamentación') }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Desvincular este profesor?')">
                                        @csrf
                                        <input type="hidden" name="fundamentacion_id" value="{{ $fundamentacion->id_fundamentacion }}">
                                        <input type="hidden" name="profesor_id" value="{{ $profesor->id }}">
                                        <button type="submit"
                                                style="background:none; border:none; cursor:pointer; font-size:20px; padding:0;"
                                                title="Desvincular">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="sin-datos">Esta fundamentación no tiene profesores oponentes asignados.</p>
        @endif
    </div>

    <!-- ============ HISTORIAL DE ESTADOS ============ -->
    <div class="detalle-seccion">
        <h2>📅 Historial de Estados</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>Fecha de Aprobación:</label>
                <span>
                    @if($fundamentacion->aprobada && $fundamentacion->aprobada->created_at)
                        {{ $fundamentacion->aprobada->created_at->format('d/m/Y H:i:s') }}
                    @else
                        <em style="color:#6c757d;">No aprobada</em>
                    @endif
                </span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Desaprobación:</label>
                <span>
                    @if($fundamentacion->desaprobada && $fundamentacion->desaprobada->created_at)
                        {{ $fundamentacion->desaprobada->created_at->format('d/m/Y H:i:s') }}
                    @else
                        <em style="color:#6c757d;">No desaprobada</em>
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- ============ ACCIONES FINALES ============ -->
    <div class="detalle-acciones-inferiores">
        <a href="{{ route('gestionarFundamentaciones') }}" class="btn-volver-detalle">← Volver</a>

        @if(!$fundamentacion->aprobada && !$fundamentacion->desaprobada)
            <a href="{{ route('gestionarFundamentaciones', ['accion' => 'editar', 'id' => $fundamentacion->id_fundamentacion]) }}"
               class="btn-editar-detalle">✏️ Editar Fundamentación</a>
        @endif

        <button type="button" class="btn-eliminar-detalle" onclick="confirmarEliminacion()">
            🗑️ Eliminar Fundamentación
        </button>
    </div>
</div>

<form id="formEliminar" method="POST" action="{{ route('eliminarFundamentación') }}" style="display:none;">
    @csrf
    <input type="hidden" name="id" value="{{ $fundamentacion->id_fundamentacion }}">
</form>

<form id="formEliminarRecomendacion" method="POST" action="/eliminarRecomendacionFundamentacion" style="display:none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminarRecomendacion">
</form>

<script>
    function confirmarEliminacion() {
        if (confirm('¿Está seguro de que desea eliminar esta fundamentación? Esta acción no se puede deshacer.')) {
            document.getElementById('formEliminar').submit();
        }
    }

    function eliminarRecomendacion(id) {
        if (confirm('¿Está seguro de que desea eliminar esta recomendación?')) {
            document.getElementById('inputIdEliminarRecomendacion').value = id;
            document.getElementById('formEliminarRecomendacion').submit();
        }
    }
</script>

@endsection