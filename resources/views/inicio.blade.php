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
                $rolNombre = strtolower($rol->rol);
                
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
            <div class="stats-container" id="statsContainer">
                <h2>Estadísticas del Sistema</h2>
                
                <!-- Mensaje cuando no hay estadísticas -->
                <div id="noStatsMessage" class="no-stats-message" style="display: none;">
                    <div class="no-stats-icon">📊</div>
                    <h3>No hay estadísticas disponibles</h3>
                    <p>Actualmente no hay datos suficientes para mostrar estadísticas del sistema.</p>
                    <p>Las estadísticas aparecerán automáticamente cuando los estudiantes comiencen a:</p>
                    <ul>
                        <li>Registrar sus tesis</li>
                        <li>Subir fundamentaciones</li>
                        <li>Entregar cortes de trabajo</li>
                    </ul>
                </div>
                
                <!-- Contenedor de estadísticas (se oculta si no hay datos) -->
                <div id="statsContent" class="stats-content">
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
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    try {
                        // Referencias a los elementos del DOM
                        const statsContainer = document.getElementById('statsContainer');
                        const noStatsMessage = document.getElementById('noStatsMessage');
                        const statsContent = document.getElementById('statsContent');
                        
                        // Obtener estadísticas del servidor
                        fetch('{{ route("estadisticas") }}')
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Error al cargar estadísticas');
                                }
                                return response.json();
                            })
                            .then(data => {
                                // Función para verificar si hay estadísticas significativas
                                function hasStatistics(data) {
                                    // Verificar si existe al menos un dato relevante
                                    const hasFundamentaciones = data.fundamentaciones?.total > 0;
                                    const hasCortes = data.cortes?.total > 0;
                                    const hasEstudiantes = data.estudiantes?.total > 0;
                                    
                                    // También considerar si el total de fundamentaciones o cortes es > 0
                                    const totalItems = (data.fundamentaciones?.total || 0) + 
                                                      (data.cortes?.total || 0);
                                    
                                    return totalItems > 0;
                                }
                                
                                // Verificar si hay estadísticas
                                if (!hasStatistics(data)) {
                                    // Mostrar mensaje de "no hay estadísticas"
                                    noStatsMessage.style.display = 'block';
                                    statsContent.style.display = 'none';
                                    return;
                                }
                                
                                // Mostrar estadísticas
                                noStatsMessage.style.display = 'none';
                                statsContent.style.display = 'block';
                                
                                // Actualizar valores en la interfaz
                                if (data.fundamentaciones) {
                                    document.getElementById('fundAprobadas').textContent = data.fundamentaciones.aprobadas || 0;
                                    document.getElementById('fundDesaprobadas').textContent = data.fundamentaciones.desaprobadas || 0;
                                    document.getElementById('fundPendientes').textContent = data.fundamentaciones.pendientes || 0;
                                }
                                
                                if (data.cortes) {
                                    document.getElementById('cortesAprobados').textContent = data.cortes.aprobados || 0;
                                    document.getElementById('cortesDesaprobados').textContent = data.cortes.desaprobados || 0;
                                    document.getElementById('cortesPendientes').textContent = data.cortes.pendientes || 0;
                                }
                                
                                if (data.estudiantes) {
                                    document.getElementById('totalEstudiantes').textContent = data.estudiantes.total || 0;
                                    document.getElementById('estudiantesSinTutor').textContent = data.estudiantes.sin_tutor || 0;
                                }

                                // Crear gráfico de Fundamentaciones (solo si hay datos)
                                const ctxFund = document.getElementById('fundamentacionesChart');
                                if (ctxFund && (data.fundamentaciones?.total || 0) > 0) {
                                    new Chart(ctxFund.getContext('2d'), {
                                        type: 'doughnut',
                                        data: {
                                            labels: ['Aprobadas', 'Desaprobadas', 'Pendientes'],
                                            datasets: [{
                                                data: [
                                                    data.fundamentaciones.aprobadas || 0,
                                                    data.fundamentaciones.desaprobadas || 0,
                                                    data.fundamentaciones.pendientes || 0
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
                                                },
                                                tooltip: {
                                                    callbacks: {
                                                        label: function(context) {
                                                            const label = context.label || '';
                                                            const value = context.raw || 0;
                                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                            return `${label}: ${value} (${percentage}%)`;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else if (ctxFund) {
                                    // Ocultar canvas si no hay datos
                                    ctxFund.style.display = 'none';
                                    document.querySelector('#fundamentacionesChart').closest('.stat-card').querySelector('.chart-container').innerHTML = 
                                        '<div class="no-data-message">No hay fundamentaciones registradas</div>';
                                }

                                // Crear gráfico de Cortes (solo si hay datos)
                                const ctxCortes = document.getElementById('cortesChart');
                                if (ctxCortes && (data.cortes?.total || 0) > 0) {
                                    new Chart(ctxCortes.getContext('2d'), {
                                        type: 'doughnut',
                                        data: {
                                            labels: ['Aprobados', 'Desaprobados', 'Pendientes'],
                                            datasets: [{
                                                data: [
                                                    data.cortes.aprobados || 0,
                                                    data.cortes.desaprobados || 0,
                                                    data.cortes.pendientes || 0
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
                                                },
                                                tooltip: {
                                                    callbacks: {
                                                        label: function(context) {
                                                            const label = context.label || '';
                                                            const value = context.raw || 0;
                                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                            return `${label}: ${value} (${percentage}%)`;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                } else if (ctxCortes) {
                                    // Ocultar canvas si no hay datos
                                    ctxCortes.style.display = 'none';
                                    document.querySelector('#cortesChart').closest('.stat-card').querySelector('.chart-container').innerHTML = 
                                        '<div class="no-data-message">No hay cortes registrados</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Error al cargar estadísticas:', error);
                                // Mostrar mensaje de error
                                noStatsMessage.innerHTML = `
                                    <div class="no-stats-icon">⚠️</div>
                                    <h3>Error al cargar estadísticas</h3>
                                    <p>No se pudieron cargar las estadísticas del sistema. Por favor, intente más tarde.</p>
                                    <p class="error-details">Detalles: ${error.message}</p>
                                `;
                                noStatsMessage.style.display = 'block';
                                statsContent.style.display = 'none';
                            });
                    } catch (error) {
                        console.error('Error en la inicialización del script:', error);
                        document.getElementById('statsContainer').innerHTML = `
                            <div class="alert alert-danger">
                                <h3>Error en la aplicación</h3>
                                <p>Ocurrió un error al cargar las estadísticas.</p>
                                <p><small>${error.message}</small></p>
                            </div>
                        `;
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

<style>
    /* Estilos para el mensaje de "no hay estadísticas" */
    .no-stats-message {
        text-align: center;
        padding: 40px 20px;
        background-color: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
        margin: 20px 0;
    }

    .no-stats-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.6;
    }

    .no-stats-message h3 {
        color: #6c757d;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .no-stats-message p {
        color: #6c757d;
        margin-bottom: 10px;
        line-height: 1.5;
    }

    .no-stats-message ul {
        text-align: left;
        display: inline-block;
        margin: 15px auto;
        color: #6c757d;
    }

    .no-stats-message li {
        margin-bottom: 5px;
    }

    .error-details {
        font-size: 12px;
        color: #dc3545;
        margin-top: 10px;
        font-family: monospace;
    }

    .no-data-message {
        text-align: center;
        padding: 30px 15px;
        color: #6c757d;
        font-style: italic;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    /* Estilo para el contenedor de estadísticas */
    .stats-content {
        transition: all 0.3s ease;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
</style>