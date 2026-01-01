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
        
        // Verificar autenticación
        if (!Auth::check()) {
            abort(403, 'Acceso denegado');
        }

        $usuario = Auth::user();

        $ruta = Route::currentRouteName(); 
            if (!$usuario->tienePermiso($ruta)) {
                abort(403, 'Acceso denegado');
            }
        
    

        // Definir menús del sidebar
        $sidebarMenus = [
            ['nombre' => 'Facultad', 'url' => route('gestionarFacultad'), 'permiso' => 'gestionarFacultad'],
            ['nombre' => 'Carrera', 'url' => route('gestionarCarrera'), 'permiso' => 'gestionarCarrera'],
            ['nombre' => 'Modalidad', 'url' => route('gestionarModalidad'), 'permiso' => 'gestionarModalidad'],
            ['nombre' => 'Grupo', 'url' => route('gestionarGrupos'), 'permiso' => 'gestionarGrupos'],
            ['nombre' => 'Departamento', 'url' => route('gestionarDepartamento'), 'permiso' => 'gestionarDepartamento'],
            ['nombre' => 'Trabajo de diploma', 'url' => route('gestionarTesis'), 'permiso' => 'gestionarTesis'],
            [
                'nombre' => 'Fundamentación de tesis', 
                'url' => route('gestionarFundamentaciones'), 
                'permiso' => 'gestionarFundamentaciones'
            ],
            ['nombre' => 'Cortes de tesis', 'url' => route('gestionarCortes'), 'permiso' => 'gestionarCortes'],
            ['nombre' => 'No conformidades', 'url' => route('gestionarNoConformidades'), 'permiso' => 'gestionarNoConformidades'],
            ['nombre' => 'Fechas de entrega', 'url' => route('fechaEntrega'), 'permiso' => 'fechaEntrega'],
            ['nombre' => 'Subir Fundamentación', 'url' => route('subirFundamentación'), 'permiso' => 'subirFundamentación'],
            ['nombre' => 'Subir Corte', 'url' => route('subirCorte'), 'permiso' => 'subirCorte'],
            ['nombre' => 'Revisar Fundamentación', 'url' => route('revisarFundamentación'), 'permiso' => 'revisarFundamentación'],
            ['nombre' => 'Revisar Corte', 'url' => route('revisarCorte'), 'permiso' => 'revisarCorte'],
            ['nombre' => 'Estudiantes tutorados', 'url' => route('estudiantesTutorados'), 'permiso' => 'estudiantesTutorados']
        ];

        // Definir menús del header
        $headerMenus = [
            ['nombre' => 'Inicio', 'url' => route('inicio'), 'permiso' => 'inicio'],
            ['nombre' => 'Usuarios', 'url' => route('gestionarUsuarios'), 'permiso' => 'gestionarUsuarios'],
            ['nombre' => 'Consultas', 'url' => route('consultas'), 'permiso' => 'consultas'],
            ['nombre' => 'Roles', 'url' => route('gestionarRoles'), 'permiso' => 'gestionarRoles'],
            ['nombre' => 'Permisos', 'url' => route('gestionarPermisos'), 'permiso' => 'gestionarPermisos'],
        ];
    @endphp

    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="{{ asset('img/UNICA_logo.png') }}" alt="Logo de la UNICA" id="logo_unica">
                <h2>SGT</h2>
            </div>

            <!-- Contenedor con scroll -->
            <div class="sidebar-menu-container">
                <ul class="sidebar-menu">
                    <h2>Gestionar</h2>

                    @foreach($sidebarMenus as $menu)
                        @php
                            $tienePermiso = false;
                            if (is_array($menu['permiso'])) {
                                $tienePermiso = $usuario->tieneAlgunPermiso($menu['permiso']);
                            } else {
                                $tienePermiso = $usuario->tienePermiso($menu['permiso']);
                            }
                        @endphp
                        
                        @if($tienePermiso)
                            <li class="menu_item">
                                <a class="menu_link" href="{{ $menu['url'] }}">{{ $menu['nombre'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <main class="main-content" id="mainContent">
            <header>
                <ul>
                    @foreach($headerMenus as $menu)
                        @php
                            $tienePermisoHeader = false;
                            if (is_array($menu['permiso'])) {
                                $tienePermisoHeader = $usuario->tieneAlgunPermiso($menu['permiso']);
                            } else {
                                $tienePermisoHeader = $usuario->tienePermiso($menu['permiso']);
                            }
                        @endphp
                        
                        @if($tienePermisoHeader)
                            <li><a href="{{ $menu['url'] }}">{{ $menu['nombre'] }}</a></li>
                        @endif
                    @endforeach
                    
                    <!-- Cerrar sesión siempre visible si el usuario está autenticado -->
                    @if($usuario)
                        <li>
                            <a href="{{ route('logout') }}">
                                Cerrar sesión
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </li>
                    @endif
                </ul>

                @if($usuario && $usuario->tienePermiso('perfil'))
                    <div id="perfil" onclick="window.location.href='{{ route('perfil') }}'">Perfil</div>
                @endif
            </header>

            <div class="content">
                @yield('content')
                
                <!-- Script para mostrar alertas de sesión -->
                <script>
                    @if(session('success'))
                        document.addEventListener('DOMContentLoaded', function() {
                            alert('{{ session('success') }}');
                        });
                    @endif
                    
                    @if(session('error'))
                        document.addEventListener('DOMContentLoaded', function() {
                            alert('{{ session('error') }}');
                        });
                    @endif
                    
                    @if($errors->any())
                        document.addEventListener('DOMContentLoaded', function() {
                            @foreach($errors->all() as $error)
                                alert('{{ $error }}');
                            @endforeach
                        });
                    @endif
                </script>
            </div>
        </main>
    </div>
</body>
</html>