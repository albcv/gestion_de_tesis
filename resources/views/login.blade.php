@if($errors->any())
    <script>
        alert("{{ $errors->first() }}");
    </script>
@endif

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>

    @vite(['resources/css/login.css'])
    @vite(['resources/js/validaciones/validarLogin.js'])
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Cabecera con gradiente -->
            <div class="login-header">
                <h1>Gestión de Tesis 🎓</h1>
                <p>Inicia sesión en tu cuenta</p>
            </div>

            <!-- Cuerpo del formulario -->
            <div class="login-body">

                @if(session('error'))
                    <div class="login-alert">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('inicioSesion') }}" method="post" id="form_login">
                    @csrf

                    <!-- Campo Email -->
                    <div class="campo">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                </svg>
                            </span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                autocomplete="email"
                                placeholder="Ingresa tu correo">
                        </div>
                    </div>

                    <!-- Campo Password -->
                    <div class="campo" id="campo_password">
                        <label for="password">Contraseña</label>
                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Ingresa tu contraseña">
                            <button
                                type="button"
                                id="togglePassword"
                                class="toggle-password"
                                aria-label="Mostrar contraseña">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                    </div>

                    <!-- Botón -->
                    <button type="submit" id="btn_login" class="btn">
                        Aceptar
                    </button>

                </form>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            if (!toggle || !password) return;

            toggle.addEventListener('click', function () {
                const isHidden = password.type === 'password';
                password.type = isHidden ? 'text' : 'password';
                toggle.querySelector('.eye-icon').textContent = isHidden ? '🙈' : '👁️';
                toggle.setAttribute(
                    'aria-label',
                    isHidden ? 'Ocultar contraseña' : 'Mostrar contraseña'
                );
            });
        });
    </script>

</body>
</html>