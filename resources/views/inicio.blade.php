@extends('layouts.app')

@section('content')
    @vite(['resources/css/inicio.css'])
    @vite(['resources/css/stats.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <img src="{{ asset('img/UNICA.png') }}" alt="Imagen de la UNICA" id="i1">

    @php
        use Illuminate\Support\Facades\Auth;
        use App\Models\Estudiante;
        use App\Models\Profesor;

        $user = Auth::user();
        $rolNombre = null;
        $hasRoleAccess = false;

        // Variables específicas del estudiante
        $estudiante = null;
        $tesisEstudiante = null;
        $fundamentacionAprobada = false;

        // Variables específicas del profesor
        $profesor = null;
        $cantidadTutorados = 0;

        if ($user) {
            // Obtener el primer rol del usuario desde la relación many-to-many
            $primerRol = $user->roles()->first();

            if ($primerRol) {
                $rolNombre = strtolower($primerRol->rol); // Convertir a minúsculas

                // Verificar si tiene acceso a esta vista
                $allowedRoles = ['administrador', 'profesor', 'estudiante'];
                $hasRoleAccess = in_array($rolNombre, $allowedRoles);
            }

            // Datos extra solo para el estudiante
            if ($rolNombre === 'estudiante') {
                $estudiante = Estudiante::with([
                    'tesis.fundamentacion',
                ])->where('id_usuario', $user->id)->first();

                if ($estudiante && $estudiante->tesis) {
                    $tesisEstudiante = $estudiante->tesis;
                    $fundamentacionAprobada = $tesisEstudiante->fundamentacion
                        && $tesisEstudiante->fundamentacion->aprobada;
                }
            }

            // Datos extra solo para el profesor
            if ($rolNombre === 'profesor') {
                $profesor = Profesor::where('id_usuario', $user->id)->first();
                if ($profesor) {
                    $cantidadTutorados = $profesor->tutorados()->count();
                }
            }
        }
    @endphp

    <h1>Gestión de Tesis 🎓</h1>

    @if ($hasRoleAccess)
        {{-- ============================
             ADMINISTRADOR
             ============================ --}}
        @if ($rolNombre === 'administrador')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás administrar información sobre las facultades, estudiantes, carreras así como los cortes de tesis y profesores oponentes.</p>
            </div>

            {{-- ============================
                 BOTONES DE ACCIÓN DEL ADMINISTRADOR
                 ============================ --}}
            <div class="estudiante-acciones">

                <a href="{{ route('accionesRecientes') }}"
                   class="accion-card accion-recientes">
                    <div class="accion-icono">⚡</div>
                    <div class="accion-info">
                        <h3 class="accion-titulo">Acciones Recientes</h3>
                        <p class="accion-descripcion">
                            Consulta las subidas y actualizaciones de fundamentaciones
                            y cortes de los últimos días.
                        </p>
                    </div>
                    <span class="accion-flecha">→</span>
                </a>

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

                    <div class="stat-card">
                        <h3>Estudiantes de Año Culminante</h3>
                        <div class="students-stats">
                            <div class="student-stat-item">
                                <div class="stat-icon">🎓</div>
                                <div class="stat-info">
                                    <div class="stat-value" id="totalEstudiantesCulminante">0</div>
                                    <div class="stat-label">Total de Estudiantes</div>
                                </div>
                            </div>
                            <div class="student-stat-item">
                                <div class="stat-icon">❌</div>
                                <div class="stat-info">
                                    <div class="stat-value" id="estudiantesCulminanteSinTutor">0</div>
                                    <div class="stat-label">Estudiantes sin Tutor</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    try {
                        fetch('{{ route("estadisticas") }}')
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Error al cargar estadísticas');
                                }
                                return response.json();
                            })
                            .then(data => {
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

                                if (data.estudiantes_culminante) {
                                    document.getElementById('totalEstudiantesCulminante').textContent = data.estudiantes_culminante.total || 0;
                                    document.getElementById('estudiantesCulminanteSinTutor').textContent = data.estudiantes_culminante.sin_tutor || 0;
                                }

                                const ctxFund = document.getElementById('fundamentacionesChart');
                                if (ctxFund) {
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
                                                backgroundColor: ['#4CAF50', '#F44336', '#FFC107'],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { display: false } }
                                        }
                                    });
                                }

                                const ctxCortes = document.getElementById('cortesChart');
                                if (ctxCortes) {
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
                                                backgroundColor: ['#4CAF50', '#F44336', '#FFC107'],
                                                borderWidth: 1
                                            }]
                                        },
                                        options: {
                                            responsive: true,
                                            maintainAspectRatio: false,
                                            plugins: { legend: { display: false } }
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error al cargar estadísticas:', error);
                            });
                    } catch (error) {
                        console.error('Error en la inicialización del script:', error);
                    }
                });
            </script>
        @endif

        {{-- ============================
             ESTUDIANTE
             ============================ --}}
        @if ($rolNombre === 'estudiante')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás subir tu fundamentación y tus cortes de tesis.</p>
            </div>

            {{-- Botones de acción para el estudiante --}}
            @if ($estudiante)
                <div class="estudiante-acciones">

                    {{-- Crear / Cambiar Tesis (siempre visible) --}}
                    <a href="{{ route('cambiarTesis') }}"
                       class="accion-card accion-tesis">
                        <div class="accion-icono">📝</div>
                        <div class="accion-info">
                            <h3 class="accion-titulo">
                                @if ($tesisEstudiante)
                                    Cambiar Nombre de Tesis
                                @else
                                    Crear Tesis
                                @endif
                            </h3>
                            <p class="accion-descripcion">
                                @if ($tesisEstudiante)
                                    Actualiza el nombre de tu trabajo de diploma.
                                @else
                                    Registra el nombre de tu trabajo de diploma para comenzar.
                                @endif
                            </p>
                        </div>
                        <span class="accion-flecha">→</span>
                    </a>

                    {{-- Subir Fundamentación (solo si tiene tesis) --}}
                    @if ($tesisEstudiante)
                        <a href="{{ route('subirFundamentación') }}"
                           class="accion-card accion-fundamentacion">
                            <div class="accion-icono">📄</div>
                            <div class="accion-info">
                                <h3 class="accion-titulo">Subir Fundamentación</h3>
                                <p class="accion-descripcion">
                                    @if ($fundamentacionAprobada)
                                        Tu fundamentación está aprobada. Puedes consultar tus versiones.
                                    @else
                                        Sube tu fundamentación de tesis.
                                    @endif
                                </p>
                            </div>
                            <span class="accion-flecha">→</span>
                        </a>
                    @endif

                    {{-- Subir Corte (solo si la fundamentación está aprobada) --}}
                    @if ($tesisEstudiante && $fundamentacionAprobada)
                        <a href="{{ route('subirCorte') }}"
                           class="accion-card accion-corte">
                            <div class="accion-icono">📚</div>
                            <div class="accion-info">
                                <h3 class="accion-titulo">Subir Corte</h3>
                                <p class="accion-descripcion">
                                    Sube tus cortes de tesis.
                                </p>
                            </div>
                            <span class="accion-flecha">→</span>
                        </a>
                    @endif

                </div>
            @else
                <div class="alert alert-warning">
                    <p>No se encontró tu perfil de estudiante. Contacta al administrador del sistema.</p>
                </div>
            @endif
        @endif

        {{-- ============================
             PROFESOR
             ============================ --}}
        @if ($rolNombre === 'profesor')
            <div class="bienvenido">
                <p>Bienvenido al sitio web de gestión de trabajos de diploma de la Universidad de Ciego de Ávila "Máximo Gómez Báez". Aquí podrás revisar las fundamentaciones y los cortes de tesis de los estudiantes.</p>
            </div>

            {{-- Botones de acción para el profesor --}}
            @if ($profesor)
                <div class="estudiante-acciones">

                    {{-- Revisar Fundamentación --}}
                    <a href="{{ route('revisarFundamentación') }}"
                       class="accion-card accion-revisar-fundamentacion">
                        <div class="accion-icono">🔍</div>
                        <div class="accion-info">
                            <h3 class="accion-titulo">Revisar Fundamentación</h3>
                            <p class="accion-descripcion">
                                Consulta y revisa las fundamentaciones de tesis de los estudiantes.
                            </p>
                        </div>
                        <span class="accion-flecha">→</span>
                    </a>

                    {{-- Revisar Corte --}}
                    <a href="{{ route('revisarCorte') }}"
                       class="accion-card accion-revisar-corte">
                        <div class="accion-icono">🔎</div>
                        <div class="accion-info">
                            <h3 class="accion-titulo">Revisar Corte</h3>
                            <p class="accion-descripcion">
                                Consulta y revisa los cortes de tesis de los estudiantes.
                            </p>
                        </div>
                        <span class="accion-flecha">→</span>
                    </a>

                    {{-- Estudiantes Tutorados --}}
                    <a href="{{ route('estudiantesTutorados') }}"
                       class="accion-card accion-tutorados">
                        <div class="accion-icono">🧑‍🎓</div>
                        <div class="accion-info">
                            <h3 class="accion-titulo">Estudiantes Tutorados</h3>
                            <p class="accion-descripcion">
                                @if ($cantidadTutorados > 0)
                                    Tienes <strong>{{ $cantidadTutorados }}</strong>
                                    estudiante{{ $cantidadTutorados === 1 ? '' : 's' }} tutorado{{ $cantidadTutorados === 1 ? '' : 's' }}.
                                @else
                                    Aún no tienes estudiantes asignados como tutorados.
                                @endif
                            </p>
                        </div>
                        <span class="accion-flecha">→</span>
                    </a>

                </div>
            @else
                <div class="alert alert-warning">
                    <p>No se encontró tu perfil de profesor. Contacta al administrador del sistema.</p>
                </div>
            @endif
        @endif
    @else
        @if ($user && $user->roles()->count() === 0)
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