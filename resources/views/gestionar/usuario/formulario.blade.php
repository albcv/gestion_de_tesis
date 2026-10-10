@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/facultad/formulario.css'])

@php
    $esEdicion = isset($usuario) && $usuario !== null;
    $accion    = $esEdicion ? route('actualizarUsuario') : route('agregarUsuario');
    $titulo    = $esEdicion ? 'Editar Usuario' : 'Crear Usuario';

    // Roles seleccionados (edición o old())
    $rolesSeleccionados = old('roles');
    if ($rolesSeleccionados === null && $esEdicion) {
        $rolesSeleccionados = $usuario->roles->pluck('id')->toArray();
    }
    $rolesSeleccionados = array_map('intval', (array) $rolesSeleccionados);
@endphp

<div class="contenido-principal">
    <div class="contenedor-formulario">

        <div class="botones-superiores">
            <a href="{{ route('gestionarUsuarios') }}" class="btn-volver">← Volver a la lista</a>
        </div>

        <h1>{{ $titulo }}</h1>

        {{-- ============================
             MENSAJES DE SESIÓN
             ============================ --}}
        @if (session('error'))
            <div class="alerta alerta-error">{{ session('error') }}</div>
        @endif

        @if (session('success'))
            <div class="alerta alerta-exito">{{ session('success') }}</div>
        @endif

        {{-- ============================
             ERRORES DE VALIDACIÓN (RESUMEN)
             ============================ --}}
        @if ($errors->any())
            <div class="alerta alerta-error">
                <strong>⚠️ No se pudo guardar el usuario:</strong>
                <ul style="margin: 8px 0 0 0; padding-left: 22px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-formulario">
            <form action="{{ $accion }}" id="formulario_usuario" method="post">
                @csrf

                @if ($esEdicion)
                    <input type="hidden" name="id" value="{{ $usuario->id }}">
                @endif

                <!-- ===================== DATOS BÁSICOS ===================== -->
                <div class="seccion-formulario">
                    <h3>Datos Básicos del Usuario</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="name">Usuario *</label>
                            <input type="text" id="name" name="name"
                                   class="atributo @error('name') atributo-error @enderror"
                                   required minlength="3" maxlength="40" autocomplete="off"
                                   placeholder="Ej: jperez"
                                   value="{{ old('name', $esEdicion ? $usuario->name : '') }}">
                            @error('name') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="campo-formulario">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email"
                                   class="atributo @error('email') atributo-error @enderror"
                                   required autocomplete="off"
                                   placeholder="ejemplo@correo.com"
                                   value="{{ old('email', $esEdicion ? $usuario->email : '') }}">
                            @error('email') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <!-- ===================== ROLES (multi-selección) ===================== -->
                        <div class="campo-formulario campo-formulario-full">
                            <label>Roles * <small style="font-weight:400; color:#666;">(puede seleccionar varios)</small></label>

                            <div class="roles-checkbox-grid">
                                @foreach ($roles as $rol)
                                    @php
                                        $rolNombre = strtolower($rol->rol);
                                        $checked   = in_array((int) $rol->id, $rolesSeleccionados, true);
                                    @endphp
                                    <label class="rol-checkbox-item {{ $checked ? 'checked' : '' }}"
                                           data-rol="{{ $rolNombre }}">
                                        <input type="checkbox"
                                               name="roles[]"
                                               value="{{ $rol->id }}"
                                               {{ $checked ? 'checked' : '' }}>
                                        <span class="rol-checkbox-texto">
                                            @if($rolNombre === 'estudiante')
                                                🎓 {{ $rol->rol }}
                                            @elseif($rolNombre === 'profesor')
                                                👨‍🏫 {{ $rol->rol }}
                                            @elseif($rolNombre === 'administrador')
                                                🛡️ {{ $rol->rol }}
                                            @else
                                                📌 {{ $rol->rol }}
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            @error('roles') <small class="mensaje-error">{{ $message }}</small> @enderror
                            @error('roles.*') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="campo-formulario">
                            <label for="password">
                                Contraseña {{ $esEdicion ? '' : '*' }}
                                @if($esEdicion)
                                    <small style="font-weight:400; color:#666;">(dejar en blanco para no cambiar)</small>
                                @endif
                            </label>
                            <input type="password" id="password" name="password"
                                   class="atributo @error('password') atributo-error @enderror"
                                   {{ $esEdicion ? '' : 'required' }}
                                   minlength="6" maxlength="255" autocomplete="new-password"
                                   placeholder="{{ $esEdicion ? 'Dejar en blanco para no cambiar' : 'Mínimo 6 caracteres' }}">
                            @error('password') <small class="mensaje-error">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>

                <!-- ===================== DATOS DE ESTUDIANTE ===================== -->
                <div id="datos_estudiante" class="seccion-formulario seccion-condicional" style="display:none;">
                    <h3>Datos del Estudiante</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="id_carrera">Carrera *</label>
                            <select id="id_carrera" name="id_carrera" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}"
                                        {{ old('id_carrera', $esEdicion && $usuario->estudiante ? $usuario->estudiante->id_carrera : '') == $carrera->id ? 'selected' : '' }}>
                                        {{ $carrera->Nombre_carrera }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="id_modalidad">Modalidad *</label>
                            <select id="id_modalidad" name="id_modalidad" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach($modalidades as $modalidad)
                                    <option value="{{ $modalidad->idModalidad }}"
                                        {{ old('id_modalidad', $esEdicion && $usuario->estudiante ? $usuario->estudiante->id_modalidad : '') == $modalidad->idModalidad ? 'selected' : '' }}>
                                        {{ $modalidad->Nombre_modalidad }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="ci_estudiante">Carnet de Identidad *</label>
                            <input type="text" id="ci_estudiante" name="ci_estudiante" class="atributo"
                                   maxlength="11" autocomplete="off" placeholder="11 dígitos"
                                   value="{{ old('ci_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->CI_estudiante : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="nombre_estudiante">Nombre *</label>
                            <input type="text" id="nombre_estudiante" name="nombre_estudiante" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('nombre_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->Nombre_estudiante : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="apellido1_estudiante">Primer Apellido *</label>
                            <input type="text" id="apellido1_estudiante" name="apellido1_estudiante" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('apellido1_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->Apellido1 : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="apellido2_estudiante">Segundo Apellido *</label>
                            <input type="text" id="apellido2_estudiante" name="apellido2_estudiante" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('apellido2_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->Apellido2 : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="id_grupo">Grupo *</label>
                            <select id="id_grupo" name="id_grupo" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach($grupos as $grupo)
                                    <option value="{{ $grupo->id }}"
                                        {{ old('id_grupo', $esEdicion && $usuario->estudiante ? $usuario->estudiante->id_grupo : '') == $grupo->id ? 'selected' : '' }}>
                                        {{ $grupo->número }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="sexo_estudiante">Sexo *</label>
                            <select name="sexo_estudiante" id="sexo_estudiante" class="atributo">
                                <option value="">Seleccione</option>
                                <option value="Masculino"
                                    {{ old('sexo_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->sexo : '') == 'Masculino' ? 'selected' : '' }}>
                                    Masculino
                                </option>
                                <option value="Femenino"
                                    {{ old('sexo_estudiante', $esEdicion && $usuario->estudiante ? $usuario->estudiante->sexo : '') == 'Femenino' ? 'selected' : '' }}>
                                    Femenino
                                </option>
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="año_académico">Año Académico *</label>
                            <select name="año_académico" id="año_académico" class="atributo">
                                <option value="">Seleccione</option>
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}"
                                        {{ old('año_académico', $esEdicion && $usuario->estudiante ? $usuario->estudiante->year_academico : '') == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ===================== DATOS DE PROFESOR ===================== -->
                <div id="datos_profesor" class="seccion-formulario seccion-condicional" style="display:none;">
                    <h3>Datos del Profesor</h3>

                    <div class="form-grid">
                        <div class="campo-formulario">
                            <label for="id_departamento">Departamento *</label>
                            <select id="id_departamento" name="id_departamento" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach($departamentos as $departamento)
                                    <option value="{{ $departamento->idDepartamento }}"
                                        {{ old('id_departamento', $esEdicion && $usuario->profesor ? $usuario->profesor->id_departamento : '') == $departamento->idDepartamento ? 'selected' : '' }}>
                                        {{ $departamento->Nombre_departamento }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="ci_profesor">Carnet de Identidad *</label>
                            <input type="text" id="ci_profesor" name="ci_profesor" class="atributo"
                                   maxlength="11" autocomplete="off" placeholder="11 dígitos"
                                   value="{{ old('ci_profesor', $esEdicion && $usuario->profesor ? $usuario->profesor->CI_profesor : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="nombre_profesor">Nombre *</label>
                            <input type="text" id="nombre_profesor" name="nombre_profesor" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('nombre_profesor', $esEdicion && $usuario->profesor ? $usuario->profesor->Nombre_profesor : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="apellido1_profesor">Primer Apellido *</label>
                            <input type="text" id="apellido1_profesor" name="apellido1_profesor" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('apellido1_profesor', $esEdicion && $usuario->profesor ? $usuario->profesor->Apellido1 : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="apellido2_profesor">Segundo Apellido *</label>
                            <input type="text" id="apellido2_profesor" name="apellido2_profesor" class="atributo"
                                   autocomplete="off"
                                   value="{{ old('apellido2_profesor', $esEdicion && $usuario->profesor ? $usuario->profesor->Apellido2 : '') }}">
                        </div>

                        <div class="campo-formulario">
                            <label for="categoría_docente">Categoría Docente *</label>
                            <select id="categoría_docente" name="categoría_docente" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach(['Profesor Titular', 'Profesor Instructor', 'Profesor Auxiliar'] as $cat)
                                    <option value="{{ $cat }}"
                                        {{ old('categoría_docente', $esEdicion && $usuario->profesor ? $usuario->profesor->Categoria_docente : '') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="campo-formulario">
                            <label for="categoría_científica">Categoría Científica *</label>
                            <select id="categoría_científica" name="categoría_científica" class="atributo">
                                <option value="">Seleccione</option>
                                @foreach(['Licenciado', 'Ingeniero', 'Máster en Ciencias', 'Doctor en Ciencias'] as $cat)
                                    <option value="{{ $cat }}"
                                        {{ old('categoría_científica', $esEdicion && $usuario->profesor ? $usuario->profesor->Categoria_cientifica : '') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ===================== BOTONES ===================== -->
                <div class="seccion-acciones">

                    @if (!$esEdicion)
                        <button type="submit" name="accion" value="guardar" class="btn-guardar">
                            💾 Crear
                        </button>
                        <button type="submit" name="accion" value="continuar" class="btn-guardar btn-continuar">
                            ➕ Crear y continuar
                        </button>
                    @else
                        <button type="submit" name="accion" value="guardar" class="btn-guardar">
                            💾 Actualizar Usuario
                        </button>
                    @endif

                    <a href="{{ route('gestionarUsuarios') }}" class="btn-cancelar">Cancelar</a>
                </div>

            </form>
        </div>

    </div>
</div>

<style>
/* ============================================================
   ROLES CHECKBOX GRID
   ============================================================ */
.campo-formulario-full {
    grid-column: 1 / -1;
}

.roles-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 10px;
    margin-top: 8px;
}

.rol-checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: border-color .15s, background .15s, box-shadow .15s;
    user-select: none;
    font-size: .95rem;
    font-weight: 500;
}

.rol-checkbox-item:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}

.rol-checkbox-item.checked {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
}

.rol-checkbox-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #2563eb;
    flex-shrink: 0;
}

.rol-checkbox-texto {
    color: #1f2937;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes       = document.querySelectorAll('input[name="roles[]"]');
    const datosEstudiante  = document.getElementById('datos_estudiante');
    const datosProfesor    = document.getElementById('datos_profesor');

    function habilitarCampos(seccion, habilitar) {
        seccion.querySelectorAll('input, select, textarea').forEach(c => {
            c.disabled = !habilitar;
            c.required = habilitar;
        });
    }

    function rolEstaSeleccionado(nombreRol) {
        let seleccionado = false;
        checkboxes.forEach(cb => {
            if (cb.checked) {
                const label = cb.closest('.rol-checkbox-item');
                const texto = label ? label.textContent.toLowerCase() : '';
                if (texto.includes(nombreRol)) {
                    seleccionado = true;
                }
            }
        });
        return seleccionado;
    }

    function mostrarSeccionesSegunRoles() {
        // Actualizar clase visual del label
        checkboxes.forEach(cb => {
            const label = cb.closest('.rol-checkbox-item');
            if (label) {
                label.classList.toggle('checked', cb.checked);
            }
        });

        const esEstudiante = rolEstaSeleccionado('estudiante');
        const esProfesor   = rolEstaSeleccionado('profesor');

        // Ocultar y deshabilitar ambos
        datosEstudiante.style.display = 'none';
        datosProfesor.style.display = 'none';
        habilitarCampos(datosEstudiante, false);
        habilitarCampos(datosProfesor, false);

        // Mostrar según roles marcados
        if (esEstudiante) {
            datosEstudiante.style.display = 'block';
            habilitarCampos(datosEstudiante, true);
        }

        if (esProfesor) {
            datosProfesor.style.display = 'block';
            habilitarCampos(datosProfesor, true);
        }
    }

    checkboxes.forEach(cb => cb.addEventListener('change', mostrarSeccionesSegunRoles));

    mostrarSeccionesSegunRoles();
});
</script>

@endsection