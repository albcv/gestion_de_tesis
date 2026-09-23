<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistema de Gestión de Tesis</title>
    @vite(['resources/css/app.css'])
    @vite(['resources/css/sidebar.css'])
</head>
<body>
    @php
        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\Route;

        if (!Auth::check()) {
            abort(403, 'Acceso denegado');
        }

        $usuario = Auth::user();
        $ruta = Route::currentRouteName();
        if (!$usuario->tienePermiso($ruta)) {
            abort(403, 'Acceso denegado');
        }

        // Menús del sidebar (gestión)
        $sidebarMenus = [
            ['nombre' => 'Facultad',                'url' => route('gestionarFacultad'),         'permiso' => 'gestionarFacultad',         'icono' => '🏛️'],
            ['nombre' => 'Carrera',                 'url' => route('gestionarCarrera'),          'permiso' => 'gestionarCarrera',          'icono' => '🎓'],
            ['nombre' => 'Modalidad',               'url' => route('gestionarModalidad'),        'permiso' => 'gestionarModalidad',        'icono' => '📚'],
            ['nombre' => 'Grupo',                   'url' => route('gestionarGrupos'),           'permiso' => 'gestionarGrupos',           'icono' => '👥'],
            ['nombre' => 'Departamento',            'url' => route('gestionarDepartamento'),     'permiso' => 'gestionarDepartamento',     'icono' => '🏢'],
            ['nombre' => 'Trabajo de diploma',      'url' => route('gestionarTesis'),            'permiso' => 'gestionarTesis',            'icono' => '📝'],
            ['nombre' => 'Fundamentación de tesis', 'url' => route('gestionarFundamentaciones'), 'permiso' => 'gestionarFundamentaciones', 'icono' => '📖'],
            ['nombre' => 'Cortes de tesis',         'url' => route('gestionarCortes'),           'permiso' => 'gestionarCortes',           'icono' => '📝'],
            ['nombre' => 'No conformidades',        'url' => route('gestionarNoConformidades'),  'permiso' => 'gestionarNoConformidades',  'icono' => '⚠️'],
            ['nombre' => 'Fechas de entrega',       'url' => route('fechaEntrega'),              'permiso' => 'fechaEntrega',              'icono' => '📅'],
            ['nombre' => 'Subir Fundamentación',    'url' => route('subirFundamentación'),       'permiso' => 'subirFundamentación',       'icono' => '⬆️'],
            ['nombre' => 'Subir Corte',             'url' => route('subirCorte'),                'permiso' => 'subirCorte',                'icono' => '⬆️'],
            ['nombre' => 'Revisar Fundamentación',  'url' => route('revisarFundamentación'),     'permiso' => 'revisarFundamentación',     'icono' => '🔍'],
            ['nombre' => 'Revisar Corte',           'url' => route('revisarCorte'),              'permiso' => 'revisarCorte',              'icono' => '🔍'],
            ['nombre' => 'Estudiantes tutorados',   'url' => route('estudiantesTutorados'),      'permiso' => 'estudiantesTutorados',      'icono' => '🧑‍🎓'],
        ];

        // Menús del header (navegación general)
        $headerMenus = [
            ['nombre' => 'Inicio',    'url' => route('inicio'),            'permiso' => 'inicio',            'icono' => '🏠'],
            ['nombre' => 'Usuarios',  'url' => route('gestionarUsuarios'),  'permiso' => 'gestionarUsuarios', 'icono' => '👤'],
            ['nombre' => 'Consultas', 'url' => route('consultas'),          'permiso' => 'consultas',         'icono' => '🔎'],
            ['nombre' => 'Roles',     'url' => route('gestionarRoles'),     'permiso' => 'gestionarRoles',    'icono' => '🛡️'],
            ['nombre' => 'Permisos',  'url' => route('gestionarPermisos'),  'permiso' => 'gestionarPermisos', 'icono' => '🔑'],
        ];

        $sidebarMenusFiltrados = array_filter($sidebarMenus, function ($menu) use ($usuario) {
            return is_array($menu['permiso'])
                ? $usuario->tieneAlgunPermiso($menu['permiso'])
                : $usuario->tienePermiso($menu['permiso']);
        });

        $headerMenusFiltrados = array_filter($headerMenus, function ($menu) use ($usuario) {
            return is_array($menu['permiso'])
                ? $usuario->tieneAlgunPermiso($menu['permiso'])
                : $usuario->tienePermiso($menu['permiso']);
        });
    @endphp

    <!-- ==============================
         Header: barra superior a ancho completo
         ============================== -->
    <header class="top-nav">
        <div class="nav-container">

            <!-- Izquierda: hamburguesa + marca -->
            <div class="nav-left">
                @if(count($sidebarMenusFiltrados) > 0)
                    <button id="menuToggle"
                            class="menu-toggle"
                            aria-label="Abrir menú de gestión"
                            aria-expanded="false"
                            aria-controls="sidebar">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                @endif

                <a href="{{ route('inicio') }}" class="brand">
                    <img src="{{ asset('img/UNICA_logo.png') }}" alt="UNICA" class="brand-logo">
                    <span class="brand-name">SGT</span>
                </a>
            </div>

            <!-- Derecha: navegación escritorio + perfil -->
            <div class="nav-right">
                <ul class="nav-links">
                    @foreach($headerMenusFiltrados as $menu)
                        <li>
                            <a href="{{ $menu['url'] }}">
                                <span class="nav-icon">{{ $menu['icono'] ?? '' }}</span>
                                <span>{{ $menu['nombre'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    @if($usuario)
                        <li>
                            <a href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <span class="nav-icon">🚪</span>
                                <span>Cerrar sesión</span>
                            </a>
                        </li>
                    @endif
                </ul>

                @if($usuario && $usuario->tienePermiso('perfil'))
                    <div id="perfil"
                         onclick="window.location.href='{{ route('perfil') }}'"
                         title="Perfil">Perfil</div>
                @endif
            </div>
        </div>

        <!-- Formulario único de logout (POST con CSRF) -->
        @if($usuario)
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endif
    </header>

    <!-- Overlay del sidebar -->
    <div id="sidebarOverlay" class="sidebar-overlay" aria-hidden="true"></div>

    <!-- ==============================
         Sidebar (drawer)
         ============================== -->
    <nav class="sidebar" id="sidebar" aria-hidden="true">
        <button class="sidebar-close" id="sidebarClose" aria-label="Cerrar menú">×</button>

        <div class="sidebar-menu-container">

            @if(count($sidebarMenusFiltrados) > 0)
                <h2 class="sidebar-section-title">Gestionar</h2>
                <ul class="sidebar-menu">
                    @foreach($sidebarMenusFiltrados as $menu)
                        <li class="menu_item">
                            <a class="menu_link" href="{{ $menu['url'] }}" title="{{ $menu['nombre'] }}">
                                <span class="menu_icon">{{ $menu['icono'] ?? '📄' }}</span>
                                <span class="menu_text">{{ $menu['nombre'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            <!-- Navegación general: solo visible en móvil -->
            @if(count($headerMenusFiltrados) > 0)
                <div class="sidebar-mobile-only">
                    <h2 class="sidebar-section-title">Navegación</h2>
                    <ul class="sidebar-menu">
                        @foreach($headerMenusFiltrados as $menu)
                            <li class="menu_item">
                                <a class="menu_link" href="{{ $menu['url'] }}" title="{{ $menu['nombre'] }}">
                                    <span class="menu_icon">{{ $menu['icono'] ?? '📄' }}</span>
                                    <span class="menu_text">{{ $menu['nombre'] }}</span>
                                </a>
                            </li>
                        @endforeach

                        @if($usuario)
                            <li class="menu_item">
                                <a class="menu_link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <span class="menu_icon">🚪</span>
                                    <span class="menu_text">Cerrar sesión</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif

        </div>
    </nav>

    <!-- ==============================
         Contenido principal
         ============================== -->
    <main class="main-content">
        <div class="content">
            @yield('content')
        </div>
    </main>

    <!-- ==============================
         Notificaciones flotantes
         ============================== -->
    <div id="notificaciones-container" class="notificaciones-container"></div>

    <script>
        /**
         * Muestra una notificación flotante en la esquina superior derecha.
         * @param {string} mensaje - Texto a mostrar
         * @param {string} tipo - 'success' | 'error' | 'info' | 'warning'
         * @param {number} duracion - ms antes de desaparecer (por defecto 4000)
         */
        function mostrarNotificacion(mensaje, tipo = 'info', duracion = 4000) {
            const contenedor = document.getElementById('notificaciones-container');
            if (!contenedor) return;

            const iconos = {
                success: '✅',
                error:   '❌',
                info:    'ℹ️',
                warning: '⚠️',
            };

            const noti = document.createElement('div');
            noti.className = `notificacion notificacion-${tipo}`;
            noti.innerHTML = `
                <span class="notificacion-icono">${iconos[tipo] || 'ℹ️'}</span>
                <span class="notificacion-mensaje">${mensaje}</span>
                <button type="button" class="notificacion-cerrar" aria-label="Cerrar">×</button>
            `;

            contenedor.appendChild(noti);

            // Forzar reflow para que la animación de entrada funcione
            requestAnimationFrame(() => noti.classList.add('visible'));

            // Botón cerrar
            noti.querySelector('.notificacion-cerrar').addEventListener('click', () => {
                noti.classList.remove('visible');
                setTimeout(() => noti.remove(), 300);
            });

            // Auto-cierre
            if (duracion > 0) {
                setTimeout(() => {
                    noti.classList.remove('visible');
                    setTimeout(() => noti.remove(), 300);
                }, duracion);
            }
        }

        // ==============================
        // Notificaciones desde Laravel
        // ==============================
        document.addEventListener('DOMContentLoaded', function () {

            @if(session('success'))
                mostrarNotificacion(@json(session('success')), 'success');
            @endif

            @if(session('error'))
                mostrarNotificacion(@json(session('error')), 'error');
            @endif

            @if(session('status'))
                mostrarNotificacion(@json(session('status')), 'info');
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    mostrarNotificacion(@json($error), 'error', 6000);
                @endforeach
            @endif
        });
    </script>

    <!-- ==============================
         JS: toggle del sidebar
         ============================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const sidebarClose = document.getElementById('sidebarClose');

            if (!menuToggle || !sidebar || !overlay) return;

            function openSidebar() {
                sidebar.classList.add('sidebar-open');
                overlay.classList.add('active');
                menuToggle.classList.add('active');
                menuToggle.setAttribute('aria-expanded', 'true');
                sidebar.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('sidebar-open');
                overlay.classList.remove('active');
                menuToggle.classList.remove('active');
                menuToggle.setAttribute('aria-expanded', 'false');
                sidebar.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            }

            menuToggle.addEventListener('click', function () {
                if (sidebar.classList.contains('sidebar-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            });

            overlay.addEventListener('click', closeSidebar);

            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }

            sidebar.querySelectorAll('.menu_link').forEach(function (link) {
                link.addEventListener('click', closeSidebar);
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && sidebar.classList.contains('sidebar-open')) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>