<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Registro</title>

    @vite(['resources/css/login.css'])

    <style>
        /* ---------- Extensión para el formulario de registro ---------- */
        .registro-card {
            max-width: 720px;
            width: 100%;
        }
        .login-body { padding: 24px 28px 28px; }

        .rol-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }
        .rol-option {
            position: relative;
            cursor: pointer;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 16px;
            text-align: center;
            transition: border-color .15s, background .15s, box-shadow .15s;
            background: #fff;
        }
        .rol-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .rol-option:hover {
            border-color: #93c5fd;
            background: #eff6ff;
        }
        .rol-option.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }
        .rol-option .rol-icon { font-size: 1.6rem; display: block; margin-bottom: 4px; }
        .rol-option .rol-title { font-weight: 700; color: #1f2937; font-size: .95rem; }
        .rol-option .rol-desc  { font-size: .78rem; color: #6b7280; margin-top: 2px; }

        .form-section-title {
            margin: 22px 0 12px;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #6b7280;
            font-weight: 700;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }

        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        select.form-select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d1d5db;
            border-radius: 8px;
            font-size: .95rem;
            background: #fff;
            color: #1f2937;
            cursor: pointer;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }
        select.form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }

        .login-alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .campo-oculto { display: none !important; }

        .registro-footer {
            text-align: center;
            margin-top: 20px;
            font-size: .9rem;
            color: #6b7280;
        }
        .registro-footer a {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        .registro-footer a:hover { text-decoration: underline; }

        .login-alert.success {
            background: #dcfce7;
            color: #14532d;
            border-left-color: #16a34a;
        }

        @media (max-width: 600px) {
            .rol-selector { grid-template-columns: 1fr; }
            .form-row-2   { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card registro-card">

            <div class="login-header">
                <h1>Solicitar Registro 🎓</h1>
                <p>Completa el formulario para solicitar tu cuenta</p>
            </div>

            <div class="login-body">

                @if(session('success'))
                    <div class="login-alert success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="login-alert">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="login-alert">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('registro.post') }}" method="POST" id="form_registro">
                    @csrf

                    {{-- =====================================================
                         SELECTOR DE ROL
                         ===================================================== --}}
                    <div class="rol-selector">
                        <label class="rol-option {{ old('rol_solicitado') === 'estudiante' ? 'selected' : '' }}"
                               data-rol="estudiante">
                            <input type="radio"
                                   name="rol_solicitado"
                                   value="estudiante"
                                   {{ old('rol_solicitado') === 'estudiante' ? 'checked' : '' }}
                                   required>
                            <span class="rol-icon">🎓</span>
                            <span class="rol-title">Estudiante</span>
                        </label>

                        <label class="rol-option {{ old('rol_solicitado') === 'profesor' ? 'selected' : '' }}"
                               data-rol="profesor">
                            <input type="radio"
                                   name="rol_solicitado"
                                   value="profesor"
                                   {{ old('rol_solicitado') === 'profesor' ? 'checked' : '' }}
                                   required>
                            <span class="rol-icon">👨‍🏫</span>
                            <span class="rol-title">Profesor</span>
                        </label>
                    </div>

                    {{-- =====================================================
                         DATOS DE LA CUENTA
                         ===================================================== --}}
                    <h4 class="form-section-title">Datos de la cuenta</h4>

                    <div class="campo">
                        <label for="name">Nombre de usuario</label>
                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input type="text" id="name" name="name" required minlength="3" maxlength="100"
                                   autocomplete="username"
                                   placeholder="User10"
                                   value="{{ old('name') }}">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="campo">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                </span>
                                <input type="email" id="email" name="email" required
                                       autocomplete="email"
                                       placeholder="tu@correo.com"
                                       value="{{ old('email') }}">
                            </div>
                        </div>

                        <div class="campo">
                            <label for="ci">Carnet de Identidad</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                        <path fill-rule="evenodd" d="M10 2a4 4 0 100 8 4 4 0 000-8zm-1 12a4 4 0 00-4 4v0h10v0a4 4 0 00-4-4h-2z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input type="text" id="ci" name="ci" required maxlength="11" minlength="11"
                                       pattern="\d{11}"
                                       title="11 dígitos numéricos"
                                       placeholder="04010145808"
                                       value="{{ old('ci') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="campo">
                            <label for="password">Contraseña</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input type="password" id="password" name="password" required minlength="6"
                                       autocomplete="new-password"
                                       placeholder="Mínimo 6 caracteres">
                                <button type="button" id="togglePassword" class="toggle-password" aria-label="Mostrar contraseña">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="password_confirmation">Confirmar contraseña</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       required minlength="6"
                                       autocomplete="new-password"
                                       placeholder="Repite la contraseña">
                            </div>
                        </div>
                    </div>

                    {{-- =====================================================
                         DATOS PERSONALES
                         ===================================================== --}}
                    <h4 class="form-section-title">Datos personales</h4>

                    <div class="campo">
                        <label for="nombre_persona">Nombre</label>
                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">👤</span>
                            <input type="text" id="nombre_persona" name="nombre_persona" required
                                   minlength="3" maxlength="40"
                                   value="{{ old('nombre_persona') }}">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="campo">
                            <label for="apellido1">Primer apellido</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">📝</span>
                                <input type="text" id="apellido1" name="apellido1" required
                                       minlength="3" maxlength="40"
                                       value="{{ old('apellido1') }}">
                            </div>
                        </div>
                        <div class="campo">
                            <label for="apellido2">Segundo apellido</label>
                            <div class="input-wrapper">
                                <span class="input-icon" aria-hidden="true">📝</span>
                                <input type="text" id="apellido2" name="apellido2" required
                                       minlength="3" maxlength="40"
                                       value="{{ old('apellido2') }}">
                            </div>
                        </div>
                    </div>

                    {{-- =====================================================
                         DATOS DE ESTUDIANTE
                         ===================================================== --}}
                    <div id="bloque_estudiante" class="{{ old('rol_solicitado') === 'profesor' ? 'campo-oculto' : '' }}">
                        <h4 class="form-section-title">Datos académicos del estudiante</h4>

                        <div class="form-row-2">
                            <div class="campo">
                                <label for="sexo">Sexo</label>
                                <select id="sexo" name="sexo" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino" {{ old('sexo') === 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino"  {{ old('sexo') === 'Femenino'  ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>

                            <div class="campo">
                                <label for="year_academico">Año académico</label>
                                <select id="year_academico" name="year_academico" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @for($y = 1; $y <= 6; $y++)
                                        <option value="{{ $y }}" {{ old('year_academico') == $y ? 'selected' : '' }}>
                                            {{ $y }}° año
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="campo">
                                <label for="id_grupo">Grupo</label>
                                <select id="id_grupo" name="id_grupo" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" {{ old('id_grupo') == $grupo->id ? 'selected' : '' }}>
                                            Grupo {{ $grupo->número }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="campo">
                                <label for="id_modalidad">Modalidad</label>
                                <select id="id_modalidad" name="id_modalidad" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach($modalidades as $modalidad)
                                        <option value="{{ $modalidad->idModalidad }}" {{ old('id_modalidad') == $modalidad->idModalidad ? 'selected' : '' }}>
                                            {{ $modalidad->Nombre_modalidad }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="campo">
                            <label for="id_carrera">Carrera</label>
                            <select id="id_carrera" name="id_carrera" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}" {{ old('id_carrera') == $carrera->id ? 'selected' : '' }}>
                                        {{ $carrera->Nombre_carrera }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- =====================================================
                         DATOS DE PROFESOR
                         ===================================================== --}}
                    <div id="bloque_profesor" class="{{ old('rol_solicitado') === 'profesor' ? '' : 'campo-oculto' }}">
                        <h4 class="form-section-title">Datos académicos del profesor</h4>

                        <div class="campo">
                            <label for="id_departamento">Departamento</label>
                            <select id="id_departamento" name="id_departamento" class="form-select">
                                <option value="">Seleccione...</option>
                                @foreach($departamentos as $departamento)
                                    <option value="{{ $departamento->idDepartamento }}"
                                        {{ old('id_departamento') == $departamento->idDepartamento ? 'selected' : '' }}>
                                        {{ $departamento->Nombre_departamento }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-row-2">
                            <div class="campo">
                                <label for="categoria_docente">Categoría docente</label>
                                <select id="categoria_docente" name="categoria_docente" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach(['Profesor Titular', 'Profesor Instructor', 'Profesor Auxiliar'] as $cat)
                                        <option value="{{ $cat }}" {{ old('categoria_docente') === $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="campo">
                                <label for="categoria_cientifica">Categoría científica</label>
                                <select id="categoria_cientifica" name="categoria_cientifica" class="form-select">
                                    <option value="">Seleccione...</option>
                                    @foreach(['Licenciado', 'Ingeniero', 'Máster en Ciencias', 'Doctor en Ciencias'] as $cat)
                                        <option value="{{ $cat }}" {{ old('categoria_cientifica') === $cat ? 'selected' : '' }}>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="margin-top:24px;">
                        Enviar solicitud
                    </button>

                </form>

                <div class="registro-footer">
                    ¿Ya tienes una cuenta?
                    <a href="{{ route('login') }}">Iniciar sesión</a>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* ---------- Toggle password ---------- */
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            if (toggle && password) {
                toggle.addEventListener('click', function () {
                    const isHidden = password.type === 'password';
                    password.type = isHidden ? 'text' : 'password';
                    toggle.querySelector('.eye-icon').textContent = isHidden ? '🙈' : '👁️';
                });
            }

            /* ---------- Selector de rol + mostrar/ocultar bloques ---------- */
            const rolOptions       = document.querySelectorAll('.rol-option');
            const bloqueEstudiante = document.getElementById('bloque_estudiante');
            const bloqueProfesor   = document.getElementById('bloque_profesor');

            // Habilitar/deshabilitar inputs de cada bloque
            function toggleRequeridos(bloque, activo) {
                if (!bloque) return;
                bloque.querySelectorAll('input, select, textarea').forEach(el => {
                    if (activo) {
                        el.removeAttribute('disabled');
                    } else {
                        el.setAttribute('disabled', 'disabled');
                    }
                });
            }

            function aplicarRol(rol) {
                rolOptions.forEach(opt => {
                    opt.classList.toggle('selected', opt.getAttribute('data-rol') === rol);
                });

                if (rol === 'estudiante') {
                    bloqueEstudiante?.classList.remove('campo-oculto');
                    bloqueProfesor?.classList.add('campo-oculto');
                    toggleRequeridos(bloqueEstudiante, true);
                    toggleRequeridos(bloqueProfesor, false);
                } else if (rol === 'profesor') {
                    bloqueProfesor?.classList.remove('campo-oculto');
                    bloqueEstudiante?.classList.add('campo-oculto');
                    toggleRequeridos(bloqueProfesor, true);
                    toggleRequeridos(bloqueEstudiante, false);
                } else {
                    bloqueEstudiante?.classList.add('campo-oculto');
                    bloqueProfesor?.classList.add('campo-oculto');
                    toggleRequeridos(bloqueEstudiante, false);
                    toggleRequeridos(bloqueProfesor, false);
                }
            }

            rolOptions.forEach(opt => {
                opt.addEventListener('click', function () {
                    const rol = this.getAttribute('data-rol');
                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                    aplicarRol(rol);
                });
            });

            // Aplicar estado inicial
            const marcado = document.querySelector('input[name="rol_solicitado"]:checked');
            aplicarRol(marcado ? marcado.value : null);
        });
    </script>

</body>
</html>