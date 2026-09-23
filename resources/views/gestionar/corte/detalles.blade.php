@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/usuario/detalles.css'])

<div class="detalle-container">

    <div class="detalle-header">
        <h1>📝 Detalles del Corte de Tesis</h1>
        <div class="detalle-acciones-superiores">
            <a href="{{ route('gestionarCortes') }}" class="btn-volver-detalle">← Volver a la lista</a>
            @if(!$corte->aprobado && !$corte->desaprobado)
                <a href="{{ route('gestionarCortes', ['accion' => 'editar', 'id' => $corte->idCortes_de_tesis]) }}"
                   class="btn-editar-detalle">✏️ Editar Corte</a>
            @endif
        </div>
    </div>

    @if (session('error'))
        <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    <!-- ============ INFO CORTE ============ -->
    <div class="detalle-seccion">
        <h2>📋 Información del Corte</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>ID:</label>
                <span>{{ $corte->idCortes_de_tesis }}</span>
            </div>
            <div class="detalle-campo">
                <label>Número de Corte:</label>
                <span><strong>Corte {{ $corte->Numero_corte }}</strong></span>
            </div>
            <div class="detalle-campo">
                <label>Estado:</label>
                <span>
                    @if($corte->aprobado)
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#16a34a,#15803d);">Aprobado</span>
                    @elseif($corte->desaprobado)
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#dc2626,#991b1b);">Desaprobado</span>
                    @else
                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#f59e0b,#d97706);">Pendiente</span>
                    @endif
                </span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Creación:</label>
                <span>{{ $corte->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Última Actualización:</label>
                <span>{{ $corte->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Total de Versiones:</label>
                <span>{{ $corte->versiones->count() }}</span>
            </div>
        </div>

        <!-- Acciones de estado -->
        <div class="detalle-subseccion">
            <h3>Acciones de estado</h3>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                @if(!$corte->aprobado && !$corte->desaprobado)
                    <form action="{{ route('aprobarCorte') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                        <button type="submit" class="btn-editar-detalle">✅ Aprobar</button>
                    </form>
                    <form action="{{ route('desaprobarCorte') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                        <button type="submit" class="btn-eliminar-detalle">❌ Desaprobar</button>
                    </form>
                @else
                    <form action="{{ route('revertirCorte') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
                        <button type="submit" class="btn-volver-detalle">↩️ Revertir a pendiente</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- ============ INFO TESIS ============ -->
    @if($corte->tesis)
        <div class="detalle-seccion">
            <h2>📚 Información de la Tesis</h2>
            <div class="detalle-grid">
                <div class="detalle-campo detalle-campo-full">
                    <label>Trabajo de Diploma:</label>
                    <span>{{ $corte->tesis->Nombre_trabajo }}</span>
                </div>

                @if($corte->tesis->estudiante)
                    <div class="detalle-campo">
                        <label>Estudiante:</label>
                        <span>
                            {{ $corte->tesis->estudiante->Nombre_estudiante }}
                            {{ $corte->tesis->estudiante->Apellido1 }}
                            {{ $corte->tesis->estudiante->Apellido2 }}
                        </span>
                    </div>
                    <div class="detalle-campo">
                        <label>CI:</label>
                        <span>{{ $corte->tesis->estudiante->CI_estudiante }}</span>
                    </div>

                    @php
                        $tutor = App\Models\Profesor::join('tutor_estudiante', 'profesor.id', '=', 'tutor_estudiante.id_profesor')
                            ->where('tutor_estudiante.id_estudiante', $corte->tesis->estudiante->id)
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

                    @if($corte->tesis->estudiante->grupo)
                        <div class="detalle-campo">
                            <label>Grupo:</label>
                            <span>{{ $corte->tesis->estudiante->grupo->número }}</span>
                        </div>
                    @endif

                    @if($corte->tesis->estudiante->carrera)
                        <div class="detalle-campo">
                            <label>Carrera:</label>
                            <span>{{ $corte->tesis->estudiante->carrera->Nombre_carrera }}</span>
                        </div>
                        @if($corte->tesis->estudiante->carrera->facultad)
                            <div class="detalle-campo">
                                <label>Facultad:</label>
                                <span>{{ $corte->tesis->estudiante->carrera->facultad->Nombre_facultad }}</span>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- ============ VERSIONES ============ -->
    <div class="detalle-seccion">
        <h2>📄 Versiones del Corte</h2>

        @if(!$corte->aprobado && !$corte->desaprobado)
            <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
                <a href="{{ route('gestionarCortes', ['accion' => 'editar', 'id' => $corte->idCortes_de_tesis]) }}"
                   class="btn-editar-detalle">✏️ Gestionar Versiones</a>
            </div>
        @endif

        @if($corte->versiones && $corte->versiones->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px; text-align:left;">Versión</th>
                            <th style="padding:12px 14px; text-align:left;">Archivo</th>
                            <th style="padding:12px 14px; text-align:left;">Enlace GitHub</th>
                            <th style="padding:12px 14px; text-align:left;">Tamaño</th>
                            <th style="padding:12px 14px; text-align:left;">Descripción</th>
                            <th style="padding:12px 14px; text-align:left;">Fecha</th>
                            <th style="padding:12px 14px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corte->versiones as $version)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">
                                    <strong>v{{ $version->version_numero }}</strong>
                                    @if($loop->first)
                                        <span class="badge-rol-detalle" style="background:linear-gradient(135deg,#2563eb,#1e40af); font-size:11px;">Actual</span>
                                    @endif
                                </td>
                                <td style="padding:10px 14px;">{{ $version->nombre_archivo }}</td>
                                <td style="padding:10px 14px;">
                                    @if($version->Enlace_Github)
                                        <a href="{{ $version->Enlace_Github }}" target="_blank" style="color:#0366d6; word-break:break-all;">
                                            🔗 Ver en GitHub
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
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
                                        <a href="{{ route('ver-documento-version-corte', $version->id) }}"
                                           style="text-decoration:none; font-size:20px;" title="Descargar">📥</a>

                                        @if(!$corte->aprobado && !$corte->desaprobado && $corte->versiones->count() > 1)
                                            <form method="POST"
                                                  action="{{ route('eliminar-version-corte', $version->id) }}"
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
            <p class="sin-datos">Este corte no tiene versiones registradas.</p>
        @endif
    </div>

    <!-- ============ NO CONFORMIDADES ============ -->
    <div class="detalle-seccion">
        <h2>⚠️ No Conformidades</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
            <a href="{{ route('agregarNoConformidadCorte', $corte->idCortes_de_tesis) }}"
               class="btn-editar-detalle">➕ Agregar No Conformidad</a>
        </div>

        @if($corte->noConformidades && $corte->noConformidades->count() > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden;">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#030 0%,#0a3 100%); color:#fff;">
                            <th style="padding:12px 14px; text-align:left;">#</th>
                            <th style="padding:12px 14px; text-align:left;">Deficiencias Detectadas</th>
                            <th style="padding:12px 14px; text-align:left;">Fecha</th>
                            <th style="padding:12px 14px; text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($corte->noConformidades as $index => $nc)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">{{ $index + 1 }}</td>
                                <td style="padding:10px 14px;">{{ $nc->Deficiencias_detectadas }}</td>
                                <td style="padding:10px 14px;">
                                    @if(isset($nc->pivot) && $nc->pivot->created_at)
                                        {{ date('d/m/Y', strtotime($nc->pivot->created_at)) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <div style="display:flex; gap:8px; justify-content:center;">
                                        <a href="{{ route('editarNoConformidadCorte', ['id_corte' => $corte->idCortes_de_tesis, 'id_nc' => $nc->idNoConformidades]) }}"
                                           style="text-decoration:none; font-size:20px;" title="Editar">✏️</a>
                                        <form method="POST"
                                              action="{{ route('desvincularNoConformidadCorte') }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('¿Desvincular esta no conformidad?')">
                                            @csrf
                                            <input type="hidden" name="corte_tesis_id" value="{{ $corte->idCortes_de_tesis }}">
                                            <input type="hidden" name="no_conformidad_id" value="{{ $nc->idNoConformidades }}">
                                            <button type="submit"
                                                    style="background:none; border:none; cursor:pointer; font-size:20px; padding:0;"
                                                    title="Desvincular">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="sin-datos">Este corte no tiene no conformidades asociadas.</p>
        @endif
    </div>

    <!-- ============ PROFESORES OPONENTES ============ -->
    <div class="detalle-seccion">
        <h2>👨‍🏫 Profesores Oponentes</h2>

        <div class="detalle-subseccion" style="border-top:none; padding-top:0; margin-top:0; margin-bottom:16px;">
            <a href="{{ route('vincularProfesorCorte', ['id' => $corte->idCortes_de_tesis]) }}"
               class="btn-editar-detalle">➕ Vincular Profesor</a>
        </div>

        @if($corte->profesores && $corte->profesores->count() > 0)
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
                        @foreach($corte->profesores as $index => $profesor)
                            <tr style="border-bottom:1px solid #e8f5e9;">
                                <td style="padding:10px 14px;">{{ $index + 1 }}</td>
                                <td style="padding:10px 14px;">
                                    <strong>{{ $profesor->Nombre_profesor }}</strong><br>
                                    {{ $profesor->Apellido1 }} {{ $profesor->Apellido2 }}
                                </td>
                                <td style="padding:10px 14px;">{{ $profesor->departamento->Nombre_departamento ?? '—' }}</td>
                                <td style="padding:10px 14px;">{{ $profesor->Categoria_docente }}</td>
                                <td style="padding:10px 14px; text-align:center;">
                                    <form method="POST"
                                          action="{{ route('desvincularProfesorCorte') }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('¿Desvincular este profesor?')">
                                        @csrf
                                        <input type="hidden" name="corte_tesis_id" value="{{ $corte->idCortes_de_tesis }}">
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
            <p class="sin-datos">Este corte no tiene profesores oponentes asignados.</p>
        @endif
    </div>

    <!-- ============ ACCIONES FINALES ============ -->
    <div class="detalle-acciones-inferiores">
        <a href="{{ route('gestionarCortes') }}" class="btn-volver-detalle">← Volver</a>

        @if(!$corte->aprobado && !$corte->desaprobado)
            <a href="{{ route('gestionarCortes', ['accion' => 'editar', 'id' => $corte->idCortes_de_tesis]) }}"
               class="btn-editar-detalle">✏️ Editar Corte</a>
        @endif

        <button type="button" class="btn-eliminar-detalle" onclick="confirmarEliminacion()">
            🗑️ Eliminar Corte
        </button>
    </div>
</div>

<form id="formEliminar" method="POST" action="{{ route('eliminarCorte') }}" style="display:none;">
    @csrf
    <input type="hidden" name="id" value="{{ $corte->idCortes_de_tesis }}">
</form>

<script>
    function confirmarEliminacion() {
        if (confirm('¿Está seguro de que desea eliminar este corte? Esta acción no se puede deshacer.')) {
            document.getElementById('formEliminar').submit();
        }
    }
</script>

@endsection