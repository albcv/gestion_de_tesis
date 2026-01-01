@extends('layouts.app')

@section('content')

@vite(['resources/css/app.css'])
@vite(['resources/css/sidebar.css'])
@vite(['resources/css/gestionar/gestionarUsuarios/verUsuario.css'])


<div class="detalle-container">
    <h1>Detalles del Usuario</h1>
    
    <div class="botones-superiores">
        <a href="/gestionarUsuarios" class="btn-volver">← Volver a la lista</a>
        <a href="/editarUsuario/{{ $usuario->id }}" class="btn-editar">✏️ Editar</a>
    </div>
    
    <!-- Información básica del usuario -->
    <div class="seccion-datos">
        <h2>Información de Cuenta</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>ID:</label>
                <span>{{ $usuario->id }}</span>
            </div>
            <div class="campo-dato">
                <label>Nombre de Usuario:</label>
                <span>{{ $usuario->name }}</span>
            </div>
            <div class="campo-dato">
                <label>Email:</label>
                <span>{{ $usuario->email }}</span>
            </div>
            <div class="campo-dato">
                <label>Rol:</label>
                <span class="badge-rol">{{ $usuario->rol->rol }}</span>
            </div>
            <div class="campo-dato">
                <label>Fecha de Creación:</label>
                <span>{{ $usuario->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="campo-dato">
                <label>Última Actualización:</label>
                <span>{{ $usuario->updated_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
    </div>
    
    <!-- Si el usuario es Estudiante -->
    @if($usuario->estudiante)
    <div class="seccion-datos">
        <h2>Información del Estudiante</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Carnet de Identidad:</label>
                <span>{{ $usuario->estudiante->CI_estudiante }}</span>
            </div>
            <div class="campo-dato">
                <label>Nombre:</label>
                <span>{{ $usuario->estudiante->Nombre_estudiante }}</span>
            </div>
            <div class="campo-dato">
                <label>Apellido 1:</label>
                <span>{{ $usuario->estudiante->Apellido1 }}</span>
            </div>
            <div class="campo-dato">
                <label>Apellido 2:</label>
                <span>{{ $usuario->estudiante->Apellido2 }}</span>
            </div>
            <div class="campo-dato">
                <label>Número:</label>
                <span>{{ $usuario->estudiante->número }}</span>
            </div>
            <div class="campo-dato">
                <label>Sexo:</label>
                <span>{{ $usuario->estudiante->sexo }}</span>
            </div>
            <div class="campo-dato">
                <label>Fecha de Ingreso:</label>
                <span>{{ date('d/m/Y', strtotime($usuario->estudiante->Fecha_ingreso)) }}</span>
            </div>
            <div class="campo-dato">
                <label>Año Académico:</label>
                <span>{{ $usuario->estudiante->year_academico }}</span>
            </div>
            
            @if($usuario->estudiante->grupo)
            <div class="campo-dato">
                <label>Grupo:</label>
                <span>{{ $usuario->estudiante->grupo->número }}</span>
            </div>
            @endif
            
            @if($usuario->estudiante->modalidad)
            <div class="campo-dato">
                <label>Modalidad:</label>
                <span>{{ $usuario->estudiante->modalidad->Nombre_modalidad }}</span>
            </div>
            @endif
            
            <!-- Facultad -->
            @if($usuario->estudiante->carrera && $usuario->estudiante->carrera->facultad)
            <div class="campo-dato">
                <label>Facultad:</label>
                <span>{{ $usuario->estudiante->carrera->facultad->Nombre_facultad }}</span>
            </div>
            @endif
            
            <!-- Carrera -->
            @if($usuario->estudiante->carrera)
            <div class="campo-dato">
                <label>Carrera:</label>
                <span>{{ $usuario->estudiante->carrera->Nombre_carrera }}</span>
            </div>
            @endif
            
            <!-- Tutores asignados -->
            <div class="campo-dato tutor-info">
                <label>Tutores Asignados ({{ $usuario->estudiante->tutores->count() }}/2):</label>
                @if($usuario->estudiante->tutores && $usuario->estudiante->tutores->count() > 0)
                    <div class="lista-tutores">
                        @foreach($usuario->estudiante->tutores as $tutor)
                        <div class="tutor-item">
                            <span class="tutor-nombre">
                                {{ $tutor->profesor->Nombre_profesor }} 
                                {{ $tutor->profesor->Apellido1 }} 
                                {{ $tutor->profesor->Apellido2 }}
                            </span>
                            <form action="{{ route('eliminarTutorEstudiante') }}" method="POST" class="form-desvincular">
                                @csrf
                                <input type="hidden" name="id" value="{{ $tutor->id }}">
                                <button type="submit" class="btn-desvincular" onclick="return confirm('¿Estás seguro de desvincular este tutor?')" title="Desvincular tutor">
                                    🗑️
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @else
                    <span class="no-tutor">No tiene tutores asignados</span>
                @endif
            </div>
            
            <!-- Acciones para vincular tutor -->
            <div class="campo-dato acciones-tutor">
                <label>Acciones Tutor:</label>
                <div class="botones-tutor">
                    @if($usuario->estudiante->tutores->count() < 2)
                        <a href="{{ route('asignarTutor', $usuario->estudiante->id) }}" class="btn-vincular">
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
    </div>
    @endif
    
    <!-- Si el usuario es Profesor -->
    @if($usuario->profesor)
    <div class="seccion-datos">
        <h2>Información del Profesor</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Carnet de Identidad:</label>
                <span>{{ $usuario->profesor->CI_profesor }}</span>
            </div>
            <div class="campo-dato">
                <label>Nombre:</label>
                <span>{{ $usuario->profesor->Nombre_profesor }}</span>
            </div>
            <div class="campo-dato">
                <label>Apellido 1:</label>
                <span>{{ $usuario->profesor->Apellido1 }}</span>
            </div>
            <div class="campo-dato">
                <label>Apellido 2:</label>
                <span>{{ $usuario->profesor->Apellido2 }}</span>
            </div>
            
            @if($usuario->profesor->departamento)
            <div class="campo-dato">
                <label>Departamento:</label>
                <span>{{ $usuario->profesor->departamento->Nombre_departamento }}</span>
            </div>
            @endif
            
            <div class="campo-dato">
                <label>Categoría Docente:</label>
                <span>{{ $usuario->profesor->Categoria_docente }}</span>
            </div>
            <div class="campo-dato">
                <label>Categoría Científica:</label>
                <span>{{ $usuario->profesor->Categoria_cientifica }}</span>
            </div>
            
            <!-- Mostrar estudiantes tutoreados si es profesor -->
            @if($usuario->profesor->tutorados && $usuario->profesor->tutorados->count() > 0)
            <div class="campo-dato tutorados-info">
                <label>Estudiantes Tutoreados ({{ $usuario->profesor->tutorados->count() }}):</label>
                <ul class="lista-tutorados">
                    @foreach($usuario->profesor->tutorados as $tutorado)
                    <li>
                        {{ $tutorado->estudiante->Nombre_estudiante }} 
                        {{ $tutorado->estudiante->Apellido1 }} 
                        {{ $tutorado->estudiante->Apellido2 }}
                        <span class="carrera-tutorado">
                            ({{ $tutorado->estudiante->carrera->Nombre_carrera ?? 'Sin carrera' }})
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif
    
    <!-- Si el usuario es Administrador u otro rol -->
    @if(!$usuario->estudiante && !$usuario->profesor)
    <div class="seccion-datos">
        <h2>Información Adicional</h2>
        <div class="datos-grid">
            <div class="campo-dato">
                <label>Tipo de Usuario:</label>
                <span>{{ $usuario->rol->rol }}</span>
            </div>
            <div class="campo-dato">
                <label>Sin perfil específico asociado</label>
                <span>Este usuario solo tiene cuenta de acceso al sistema</span>
            </div>
        </div>
    </div>
    @endif
    
    <div class="botones-inferiores">
        <a href="/gestionarUsuarios" class="btn-volver">← Volver</a>
        <a href="/editarUsuario/{{ $usuario->id }}" class="btn-editar">✏️ Editar Usuario</a>
        <form action="/eliminarUsuario" method="POST" style="display: inline;">
            @csrf
            <input type="hidden" name="id" value="{{ $usuario->id }}">
            <button type="submit" class="btn-eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                🗑️ Eliminar Usuario
            </button>
        </form>
    </div>
</div>



@endsection