@auth
    <script>
        try {
            let nombre = '{{ Auth::user()->name ?? "" }}';
            if (nombre && nombre.trim() !== '') {
                alert('Bienvenido ' + nombre);
            }
        } catch (error) {
            console.error('Error al mostrar bienvenida:', error);
        }
    </script>
@endauth

@extends('layouts.app')

@section('content')
    @vite(['resources/css/inicio.css'])
    @vite(['resources/css/stats.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <img src="{{ asset('img/UNICA.png') }}" alt="Imagen de la UNICA" id="i1">

    @php
        use Illuminate\Support\Facades\Auth;
        use Illuminate\Support\Facades\DB;
        
        $user = Auth::user();
        $rolNombre = null;
        $hasRoleAccess = false;
        
        if ($user) {
            // Obtener el nombre del rol desde la base de datos
            $rol = DB::table('roles')->where('id', $user->id_rol)->first();
            
            if ($rol) {
                $rolNombre = strtolower($rol->rol); // Convertir a minúsculas
                
                // Verificar si tiene acceso a esta vista
                $allowedRoles = ['administrador', 'profesor', 'estudiante'];
                $hasRoleAccess = in_array($rolNombre, $allowedRoles);
                
                // Mostrar ícono solo para roles específicos
                if ($rolNombre === 'profesor' || $rolNombre === 'estudiante') {
                    echo '<img src="' . asset('img/tesis2.png') . '" alt="Ícono de tesis" id="i4">';
                }
            }
        }
    @endphp

    <h1>Gestión de tesis</h1>

    @if ($hasRoleAccess)
        @if ($rolNombre === 'administrador')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás administrar información sobre las facultades, estudiantes, carreras así como los cortes de tesis y profesores oponentes.</p>
            </div>

            <!-- Estadísticas para administrador -->
            <div class="stats-container">
                <h2>Estadísticas del Sistema</h2>
                
                <div class="stats-grid">
                    <!-- Gráfico de Fundamentaciones -->
                    <div class="stat-card">
                        <h3>Estado de Fundamentaciones</h3>
                        <div class="chart-container">
                            <canvas id="fundamentacionesChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #4CAF50;"></span>
                                <span class="legend-text">Aprobadas: <span id="fundAprobadas">0</span></span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #F44336;"></span>
                                <span class="legend-text">Desaprobadas: <span id="fundDesaprobadas">0</span></span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #FFC107;"></span>
                                <span class="legend-text">Pendientes: <span id="fundPendientes">0</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Cortes -->
                    <div class="stat-card">
                        <h3>Estado de Cortes</h3>
                        <div class="chart-container">
                            <canvas id="cortesChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #4CAF50;"></span>
                                <span class="legend-text">Aprobados: <span id="cortesAprobados">0</span></span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #F44336;"></span>
                                <span class="legend-text">Desaprobados: <span id="cortesDesaprobados">0</span></span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #FFC107;"></span>
                                <span class="legend-text">Pendientes: <span id="cortesPendientes">0</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Estudiantes -->
                    <div class="stat-card">
                        <h3>Estudiantes</h3>
                        <div class="students-stats">
                            <div class="student-stat-item">
                                <div class="stat-icon">👨‍🎓</div>
                                <div class="stat-info">
                                    <div class="stat-value" id="totalEstudiantes">0</div>
                                    <div class="stat-label">Total de Estudiantes</div>
                                </div>
                            </div>
                            <div class="student-stat-item">
                                <div class="stat-icon">❌</div>
                                <div class="stat-info">
                                    <div class="stat-value" id="estudiantesSinTutor">0</div>
                                    <div class="stat-label">Estudiantes sin Tutor</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           <script>
    document.addEventListener('DOMContentLoaded', function() {
        function showAlertError(title, error) {
            let errorMessage = title;
            
            if (error) {
                if (error.message) errorMessage += "\n\nMensaje: " + error.message;
                if (error.name) errorMessage += "\nTipo: " + error.name;
                if (error.status) errorMessage += "\nEstado: " + error.status;
                if (error.statusText) errorMessage += "\nTexto del estado: " + error.statusText;
                if (error.url) errorMessage += "\nURL: " + error.url;
                
                // Para errores de red o fetch
                if (error.type) errorMessage += "\nTipo de error: " + error.type;
                
                // Para errores de respuesta HTTP
                if (error.response) {
                    try {
                        errorMessage += "\nRespuesta: " + JSON.stringify(error.response);
                    } catch (e) {
                        errorMessage += "\nRespuesta: [No se puede serializar]";
                    }
                }
                
                // Stack trace si está disponible
                if (error.stack) {
                    errorMessage += "\n\nStack trace:";
                    // Tomamos solo las primeras 5 líneas del stack para no hacer el alert demasiado grande
                    const stackLines = error.stack.split('\n').slice(0, 5);
                    errorMessage += "\n" + stackLines.join('\n');
                }
                
                // Si es un objeto complejo, tratamos de convertirlo a string
                if (typeof error === 'object' && !error.message && !error.stack) {
                    try {
                        errorMessage += "\n\nObjeto error: " + JSON.stringify(error, null, 2);
                    } catch (e) {
                        errorMessage += "\n\n[Error no se puede serializar]";
                    }
                }
            }
            
            alert("❌ ERROR: " + errorMessage);
            console.error(title, error);
        }

        try {
            // Obtener estadísticas del servidor
            fetch('{{ route("estadisticas") }}')
                .then(response => {
                    if (!response.ok) {
                        const httpError = new Error(`Error HTTP ${response.status}: ${response.statusText}`);
                        httpError.status = response.status;
                        httpError.statusText = response.statusText;
                        httpError.url = response.url;
                        
                        // Intentar obtener más información del cuerpo de la respuesta
                        return response.text().then(text => {
                            try {
                                const jsonError = JSON.parse(text);
                                httpError.response = jsonError;
                            } catch {
                                httpError.response = text || "Sin cuerpo de respuesta";
                            }
                            throw httpError;
                        }).catch(() => {
                            throw httpError;
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    try {
                        // Validar estructura de datos
                        if (!data) {
                            throw new Error("La respuesta del servidor está vacía");
                        }

                        // Actualizar valores en la interfaz
                        if (data.fundamentaciones) {
                            document.getElementById('fundAprobadas').textContent = data.fundamentaciones.aprobadas || 0;
                            document.getElementById('fundDesaprobadas').textContent = data.fundamentaciones.desaprobadas || 0;
                            document.getElementById('fundPendientes').textContent = data.fundamentaciones.pendientes || 0;
                        } else {
                            console.warn('Estructura de datos de fundamentaciones no encontrada');
                        }
                        
                        if (data.cortes) {
                            document.getElementById('cortesAprobados').textContent = data.cortes.aprobados || 0;
                            document.getElementById('cortesDesaprobados').textContent = data.cortes.desaprobados || 0;
                            document.getElementById('cortesPendientes').textContent = data.cortes.pendientes || 0;
                        } else {
                            console.warn('Estructura de datos de cortes no encontrada');
                        }
                        
                        if (data.estudiantes) {
                            document.getElementById('totalEstudiantes').textContent = data.estudiantes.total || 0;
                            document.getElementById('estudiantesSinTutor').textContent = data.estudiantes.sin_tutor || 0;
                        } else {
                            console.warn('Estructura de datos de estudiantes no encontrada');
                        }

                        // Crear gráfico de Fundamentaciones
                        const ctxFund = document.getElementById('fundamentacionesChart');
                        if (ctxFund) {
                            try {
                                new Chart(ctxFund.getContext('2d'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Aprobadas', 'Desaprobadas', 'Pendientes'],
                                        datasets: [{
                                            data: [
                                                data.fundamentaciones?.aprobadas || 0,
                                                data.fundamentaciones?.desaprobadas || 0,
                                                data.fundamentaciones?.pendientes || 0
                                            ],
                                            backgroundColor: [
                                                '#4CAF50',
                                                '#F44336',
                                                '#FFC107'
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            } catch (chartError) {
                                showAlertError('Error al crear gráfico de fundamentaciones', chartError);
                            }
                        }

                        // Crear gráfico de Cortes
                        const ctxCortes = document.getElementById('cortesChart');
                        if (ctxCortes) {
                            try {
                                new Chart(ctxCortes.getContext('2d'), {
                                    type: 'doughnut',
                                    data: {
                                        labels: ['Aprobados', 'Desaprobados', 'Pendientes'],
                                        datasets: [{
                                            data: [
                                                data.cortes?.aprobados || 0,
                                                data.cortes?.desaprobados || 0,
                                                data.cortes?.pendientes || 0
                                            ],
                                            backgroundColor: [
                                                '#4CAF50',
                                                '#F44336',
                                                '#FFC107'
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            } catch (chartError) {
                                showAlertError('Error al crear gráfico de cortes', chartError);
                            }
                        }
                        
                        // Mostrar mensaje de éxito si todo fue bien
                        if (document.querySelectorAll('.stat-value').length > 0) {
                            console.log('Estadísticas cargadas correctamente');
                        }
                    } catch (processingError) {
                        showAlertError('Error al procesar los datos de estadísticas', processingError);
                    }
                })
                .catch(error => {
                    showAlertError('Error al cargar estadísticas desde el servidor', error);
                    
                    // También registrar en consola para más detalles
                    console.error('Error completo del fetch:', {
                        message: error.message,
                        name: error.name,
                        stack: error.stack,
                        status: error.status,
                        statusText: error.statusText,
                        url: error.url,
                        response: error.response
                    });
                });
        } catch (error) {
            showAlertError('Error en la inicialización del script de estadísticas', error);
        }
    });
</script>
        @endif

        @if ($rolNombre === 'estudiante')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás subir tu fundamentación y tus cortes de tesis.</p>
            </div>
        @endif

        @if ($rolNombre === 'profesor')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás revisar las fundamentaciones y los cortes de tesis de los estudiantes.</p>
            </div>
        @endif
    @else
        @if ($user && is_null($user->id_rol))
            <div class="alert alert-danger">
                <p>No tiene un rol asignado. Por favor, contacte al administrador del sistema.</p>
            </div>
        @else
            <div class="alert alert-warning">
                <p>No tiene permisos para acceder a esta sección. Si cree que esto es un error, contacte al administrador.</p>
            </div>
        @endif
    @endif


@endsection