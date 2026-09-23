@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/usuario/detalles.css'])

<div class="detalle-container">
    <div class="detalle-header">
        <h1>👤 Detalles del Usuario</h1>
        <div class="detalle-acciones-superiores">
            <a href="{{ route('gestionarUsuarios') }}" class="btn-volver-detalle">← Volver a la lista</a>
            <a href="{{ route('gestionarUsuarios', ['accion' => 'editar', 'id' => $usuario->id]) }}"
               class="btn-editar-detalle">✏️ Editar Usuario</a>
        </div>
    </div>

    @if (session('error'))
        <div class="alerta alerta-error">{{ session('error') }}</div>
    @endif

    <!-- ============== INFORMACIÓN DE CUENTA ============== -->
    <div class="detalle-seccion">
        <h2>🔐 Información de Cuenta</h2>
        <div class="detalle-grid">
            <div class="detalle-campo">
                <label>ID:</label>
                <span>{{ $usuario->id }}</span>
            </div>
            <div class="detalle-campo">
                <label>Nombre de Usuario:</label>
                <span>{{ $usuario->name }}</span>
            </div>
            <div class="detalle-campo">
                <label>Email:</label>
                <span>{{ $usuario->email }}</span>
            </div>
            <div class="detalle-campo">
                <label>Rol:</label>
                <span class="badge-rol-detalle">{{ $usuario->rol->rol ?? 'Sin rol' }}</span>
            </div>
            <div class="detalle-campo">
                <label>Fecha de Creación:</label>
                <span>{{ $usuario->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="detalle-campo">
                <label>Última Actualización:</label>
                <span>{{ $usuario->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>

    <!-- ============== INFORMACIÓN DE ESTUDIANTE ============== -->
    @if($usuario->estudiante)
        <div class="detalle-seccion">
            <h2>🎓 Información del Estudiante</h2>
            <div class="detalle-grid">
                <div class="detalle-campo">
                    <label>Carnet de Identidad:</label>
                    <span>{{ $usuario->estudiante->CI_estudiante }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Nombre:</label>
                    <span>{{ $usuario->estudiante->Nombre_estudiante }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Primer Apellido:</label>
                    <span>{{ $usuario->estudiante->Apellido1 }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Segundo Apellido:</label>
                    <span>{{ $usuario->estudiante->Apellido2 }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Número del Estudiante:</label>
                    <span>{{ $usuario->estudiante->número }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Sexo:</label>
                    <span>{{ $usuario->estudiante->sexo }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Fecha de Ingreso:</label>
                    <span>{{ date('d/m/Y', strtotime($usuario->estudiante->Fecha_ingreso)) }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Año Académico:</label>
                    <span>{{ $usuario->estudiante->year_academico }}</span>
                </div>

                @if($usuario->estudiante->grupo)
                    <div class="detalle-campo">
                        <label>Grupo:</label>
                        <span>{{ $usuario->estudiante->grupo->número }}</span>
                    </div>
                @endif

                @if($usuario->estudiante->modalidad)
                    <div class="detalle-campo">
                        <label>Modalidad:</label>
                        <span>{{ $usuario->estudiante->modalidad->Nombre_modalidad }}</span>
                    </div>
                @endif

                @if($usuario->estudiante->carrera)
                    <div class="detalle-campo">
                        <label>Carrera:</label>
                        <span>{{ $usuario->estudiante->carrera->Nombre_carrera }}</span>
                    </div>
                    @if($usuario->estudiante->carrera->facultad)
                        <div class="detalle-campo">
                            <label>Facultad:</label>
                            <span>{{ $usuario->estudiante->carrera->facultad->Nombre_facultad }}</span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Tutores asignados -->
            <div class="detalle-subseccion">
                <h3>👨‍🏫 Tutores Asignados ({{ $usuario->estudiante->tutores->count() }}/2)</h3>
                @if($usuario->estudiante->tutores && $usuario->estudiante->tutores->count() > 0)
                    <div class="tutores-lista">
                        @foreach($usuario->estudiante->tutores as $tutor)
                            <div class="tutor-item-detalle">
                                <span class="tutor-nombre-detalle">
                                    {{ $tutor->profesor->Nombre_profesor }}
                                    {{ $tutor->profesor->Apellido1 }}
                                    {{ $tutor->profesor->Apellido2 }}
                                </span>
                                <form action="{{ route('eliminarTutorEstudiante') }}" method="POST"
                                      class="form-desvincular-tutor" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $tutor->id }}">
                                    <button type="submit" class="btn-desvincular-tutor"
                                            onclick="return confirm('¿Estás seguro de desvincular este tutor?')"
                                            title="Desvincular tutor">🗑️</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="sin-datos">No tiene tutores asignados</p>
                @endif

                <div class="acciones-tutor-detalle">
                    @if($usuario->estudiante->tutores->count() < 2)
                        <a href="{{ route('asignarTutor', $usuario->estudiante->id) }}" class="btn-vincular-tutor">
                            @if($usuario->estudiante->tutores->count() > 0)
                                ✏️ Agregar Tutor Adicional
                            @else
                                ➕ Asignar Tutor
                            @endif
                        </a>
                    @else
                        <span class="max-tutores">Máximo de tutores alcanzado (2/2)</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- ============== INFORMACIÓN DE PROFESOR ============== -->
    @if($usuario->profesor)
        <div class="detalle-seccion">
            <h2>👨‍🏫 Información del Profesor</h2>
            <div class="detalle-grid">
                <div class="detalle-campo">
                    <label>Carnet de Identidad:</label>
                    <span>{{ $usuario->profesor->CI_profesor }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Nombre:</label>
                    <span>{{ $usuario->profesor->Nombre_profesor }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Primer Apellido:</label>
                    <span>{{ $usuario->profesor->Apellido1 }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Segundo Apellido:</label>
                    <span>{{ $usuario->profesor->Apellido2 }}</span>
                </div>

                @if($usuario->profesor->departamento)
                    <div class="detalle-campo">
                        <label>Departamento:</label>
                        <span>{{ $usuario->profesor->departamento->Nombre_departamento }}</span>
                    </div>
                @endif

                <div class="detalle-campo">
                    <label>Categoría Docente:</label>
                    <span>{{ $usuario->profesor->Categoria_docente }}</span>
                </div>
                <div class="detalle-campo">
                    <label>Categoría Científica:</label>
                    <span>{{ $usuario->profesor->Categoria_cientifica }}</span>
                </div>
            </div>

            @if($usuario->profesor->tutorados && $usuario->profesor->tutorados->count() > 0)
                <div class="detalle-subseccion">
                    <h3>👥 Estudiantes Tutoreados ({{ $usuario->profesor->tutorados->count() }})</h3>
                    <ul class="lista-tutorados-detalle">
                        @foreach($usuario->profesor->tutorados as $tutorado)
                            <li>
                                {{ $tutorado->Nombre_estudiante }}
                                {{ $tutorado->Apellido1 }}
                                {{ $tutorado->Apellido2 }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    <!-- ============== SIN PERFIL ESPECÍFICO ============== -->
    @if(!$usuario->estudiante && !$usuario->profesor)
        <div class="detalle-seccion">
            <h2>ℹ️ Información Adicional</h2>
            <div class="detalle-grid">
                <div class="detalle-campo">
                    <label>Tipo de Usuario:</label>
                    <span>{{ $usuario->rol->rol ?? 'Sin rol' }}</span>
                </div>
                <div class="detalle-campo detalle-campo-full">
                    <label>Perfil asociado:</label>
                    <span>Este usuario solo tiene cuenta de acceso al sistema</span>
                </div>
            </div>
        </div>
    @endif

    <!-- ============== ACCIONES FINALES ============== -->
    <div class="detalle-acciones-inferiores">
        <a href="{{ route('gestionarUsuarios') }}" class="btn-volver-detalle">← Volver</a>
        <a href="{{ route('gestionarUsuarios', ['accion' => 'editar', 'id' => $usuario->id]) }}"
           class="btn-editar-detalle">✏️ Editar Usuario</a>

        <form action="{{ route('eliminarUsuario') }}" method="POST" style="display: inline;">
            @csrf
            <input type="hidden" name="id" value="{{ $usuario->id }}">
            <button type="submit" class="btn-eliminar-detalle"
                    onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                🗑️ Eliminar Usuario
            </button>
        </form>
    </div>
</div>

@endsection