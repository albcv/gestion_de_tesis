@extends('layouts.app')

@section('title', 'Gestión de Fechas de Entrega')

<link rel="stylesheet" href="css/gestionar/fechaEntrega.css">

@section('content')
<div class="fechas-container fecha-fade-in">
    <h1 class="fechas-title">
        <i class="fas fa-calendar-alt"></i> Gestión de Fechas de Entrega
    </h1>

    @if (session('success'))
        <div class="fecha-alert fecha-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="fecha-alert fecha-alert-error">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    @if (session('info'))
        <div class="fecha-alert fecha-alert-info">
            <i class="fas fa-info-circle"></i> {{ session('info') }}
        </div>
    @endif

    <div class="fechas-grid">
        <!-- Fecha de Entrega de Fundamentación -->
        <div class="fechas-card">
            <div class="fechas-card-header">
                <h2 class="fechas-card-title">
                    <i class="fas fa-file-contract"></i> Fundamentación
                </h2>
                <p class="fechas-card-subtitle">Fecha límite para la entrega de fundamentaciones</p>
            </div>
            <div class="fechas-card-body">
                <!-- Fecha Actual -->
                <div class="fecha-info-current">
                    <span class="fecha-info-label">Fecha de entrega actual</span>
                    <div class="fecha-info-value">
                        @if ($fechaFundamentacion)
                            <i class="fas fa-calendar-check"></i>
                            {{ $fechaFundamentacion->fecha_entrega->format('d/m/Y') }}
                            
                            <!-- Estado de la fecha -->
                            @php
                                $hoy = now()->startOfDay();
                                $fechaFund = $fechaFundamentacion->fecha_entrega;
                                $diferencia = $hoy->diffInDays($fechaFund, false);
                            @endphp
                            
                            @if ($diferencia > 0)
                                <span class="fecha-status fecha-status-future">
                                    <i class="fas fa-clock"></i> En {{ $diferencia }} días
                                </span>
                            @elseif ($diferencia == 0)
                                <span class="fecha-status fecha-status-today">
                                    <i class="fas fa-exclamation-circle"></i> Hoy es la fecha límite
                                </span>
                            @else
                                <span class="fecha-status fecha-status-past">
                                    <i class="fas fa-history"></i> Vencida hace {{ abs($diferencia) }} días
                                </span>
                            @endif
                        @else
                            <span class="fecha-no-set">
                                <i class="fas fa-times-circle"></i> No establecida
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Formulario para establecer/actualizar fecha -->
                <form action="{{ route('fechas.fundamentacion.actualizar') }}" method="POST" class="fecha-form">
                    @csrf
                    @if ($fechaFundamentacion)
                        @method('PUT')
                        <input type="hidden" name="id" value="{{ $fechaFundamentacion->id }}">
                    @endif
                    
                    <div class="fecha-form-group">
                        <label for="fecha_fundamentacion" class="fecha-form-label">
                            <i class="fas fa-calendar-plus"></i> Nueva Fecha de Entrega
                        </label>
                        <input type="date" 
                               class="fecha-form-input" 
                               id="fecha_fundamentacion" 
                               name="fecha_entrega" 
                               value="{{ $fechaFundamentacion ? $fechaFundamentacion->fecha_entrega->format('Y-m-d') : '' }}"
                               required
                               min="{{ now()->format('Y-m-d') }}">
                        <span class="fecha-form-help">
                            <i class="fas fa-info-circle"></i> 
                            Selecciona la fecha límite para la entrega de fundamentaciones. No puede ser una fecha pasada.
                        </span>
                    </div>
                    
                    <button type="submit" class="fecha-btn fecha-btn-success">
                        <i class="fas fa-save"></i>
                        {{ $fechaFundamentacion ? 'Actualizar Fecha' : 'Establecer Fecha' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Fechas de Entrega de Cortes -->
        <div class="fechas-card">
            <div class="fechas-card-header">
                <h2 class="fechas-card-title">
                    <i class="fas fa-cut"></i> Cortes de Tesis
                </h2>
                <p class="fechas-card-subtitle">Fechas límite para cada corte de tesis</p>
            </div>
            <div class="fechas-card-body">
                @if ($fechasCortes->count() > 0)
                    <div class="fecha-info-current" style="margin-bottom: 10px;">
                        <span class="fecha-info-label">Resumen de fechas establecidas</span>
                        <div class="fecha-info-value">
                            <i class="fas fa-list-ol"></i>
                            {{ $fechasCortes->count() }} de 4 cortes configurados
                        </div>
                    </div>
                @endif

                <!-- Tabla de cortes -->
                <div class="fechas-cortes-table-container">
                    <table class="fechas-cortes-table">
                        <thead>
                            <tr>
                                <th>Corte</th>
                                <th>Fecha Actual</th>
                                <th>Estado</th>
                                <th>Nueva Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 4; $i++)
                                @php
                                    $corte = $fechasCortes->where('numero_corte', $i)->first();
                                    $hoy = now()->startOfDay();
                                    $fechaCorte = $corte ? $corte->fecha_entrega : null;
                                    $diferencia = $fechaCorte ? $hoy->diffInDays($fechaCorte, false) : null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="corte-badge">
                                            {{ $i }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($corte)
                                            <strong>{{ $corte->fecha_entrega->format('d/m/Y') }}</strong>
                                        @else
                                            <span class="fecha-no-set">No establecida</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($corte)
                                            @if ($diferencia > 0)
                                                <span class="fecha-status fecha-status-future">
                                                    <i class="fas fa-clock"></i> En {{ $diferencia }} días
                                                </span>
                                            @elseif ($diferencia == 0)
                                                <span class="fecha-status fecha-status-today">
                                                    <i class="fas fa-exclamation-circle"></i> Hoy
                                                </span>
                                            @else
                                                <span class="fecha-status fecha-status-past">
                                                    <i class="fas fa-history"></i> Vencida
                                                </span>
                                            @endif
                                        @else
                                            <span class="fecha-no-set">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('fechas.corte.actualizar', $i) }}" method="POST" class="fecha-corte-form">
                                            @csrf
                                            @if ($corte)
                                                @method('PUT')
                                                <input type="hidden" name="id" value="{{ $corte->id }}">
                                            @endif
                                            
                                            <input type="date" 
                                                   class="fecha-corte-input" 
                                                   name="fecha_entrega" 
                                                   value="{{ $corte ? $corte->fecha_entrega->format('Y-m-d') : '' }}"
                                                   required
                                                   min="{{ now()->format('Y-m-d') }}">
                                            <button type="submit" class="fecha-corte-btn">
                                                <i class="fas fa-{{ $corte ? 'sync' : 'plus' }}"></i>
                                                {{ $corte ? 'Actualizar' : 'Agregar' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <!-- Información adicional -->
                <div class="fecha-form-help" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                    <i class="fas fa-lightbulb"></i>
                    <strong>Nota:</strong> Las fechas de entrega deben establecerse en orden cronológico. 
                    Se recomienda que la fecha del Corte 1 sea posterior a la fecha de fundamentación, 
                    y cada corte posterior sea posterior al anterior.
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de información general -->
    <div class="fechas-card" style="margin-top: 20px;">
        <div class="fechas-card-header" style="background: linear-gradient(135deg, #27ae60, #229954);">
            <h2 class="fechas-card-title">
                <i class="fas fa-chart-line"></i> Resumen y Estadísticas
            </h2>
            <p class="fechas-card-subtitle">Información general sobre las fechas establecidas</p>
        </div>
        <div class="fechas-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="background: rgba(52, 152, 219, 0.05); padding: 20px; border-radius: 10px; border-left: 4px solid #3498db;">
                    <div style="font-size: 0.9rem; color: #7f8c8d; margin-bottom: 8px;">Fecha más próxima</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: #2c3e50;">
                        @php
                            $todasFechas = collect();
                            if ($fechaFundamentacion) {
                                $todasFechas->push([
                                    'tipo' => 'Fundamentación',
                                    'fecha' => $fechaFundamentacion->fecha_entrega,
                                    'numero' => null
                                ]);
                            }
                            foreach ($fechasCortes as $corte) {
                                $todasFechas->push([
                                    'tipo' => 'Corte ' . $corte->numero_corte,
                                    'fecha' => $corte->fecha_entrega,
                                    'numero' => $corte->numero_corte
                                ]);
                            }
                            
                            $fechaMasProxima = $todasFechas->sortBy('fecha')->first();
                        @endphp
                        
                        @if ($fechaMasProxima)
                            <i class="fas fa-calendar-day" style="color: #3498db; margin-right: 10px;"></i>
                            {{ $fechaMasProxima['tipo'] }}: 
                            {{ $fechaMasProxima['fecha']->format('d/m/Y') }}
                        @else
                            <span style="color: #95a5a6;">No hay fechas establecidas</span>
                        @endif
                    </div>
                </div>
                
                <div style="background: rgba(243, 156, 18, 0.05); padding: 20px; border-radius: 10px; border-left: 4px solid #f39c12;">
                    <div style="font-size: 0.9rem; color: #7f8c8d; margin-bottom: 8px;">Fechas vencidas</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: #2c3e50;">
                        @php
                            $fechasVencidas = $todasFechas->filter(function($item) {
                                return now()->greaterThan($item['fecha']);
                            })->count();
                        @endphp
                        <i class="fas fa-exclamation-triangle" style="color: #f39c12; margin-right: 10px;"></i>
                        {{ $fechasVencidas }} de {{ $todasFechas->count() }} fechas
                    </div>
                </div>
                
                <div style="background: rgba(39, 174, 96, 0.05); padding: 20px; border-radius: 10px; border-left: 4px solid #27ae60;">
                    <div style="font-size: 0.9rem; color: #7f8c8d; margin-bottom: 8px;">Cortes configurados</div>
                    <div style="font-size: 1.2rem; font-weight: 600; color: #2c3e50;">
                        <i class="fas fa-check-circle" style="color: #27ae60; margin-right: 10px;"></i>
                        {{ $fechasCortes->count() }} de 4 cortes
                    </div>
                </div>
            </div>
            
            <!-- Acciones masivas -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e6ed;">
                <h3 style="font-size: 1.1rem; color: #2c3e50; margin-bottom: 15px;">
                    <i class="fas fa-cogs"></i> Acciones Masivas
                </h3>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    
                    <form action="{{ route('fechas.reiniciar') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar todas las fechas? Esta acción no se puede deshacer.');" style="flex: 1; min-width: 200px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border: none; padding: 12px 20px; border-radius: 8px; cursor: pointer; width: 100%;">
                            <i class="fas fa-trash-alt"></i> Eliminar Todas las Fechas
                        </button>
                        <small style="display: block; color: #7f8c8d; margin-top: 5px;">
                            Elimina todas las fechas de entrega establecidas
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Validar que las fechas no sean pasadas
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        
        // Establecer min date para todos los inputs de fecha
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.min = today;
            
            // Si el input tiene una fecha pasada, mostrarla pero requerir cambio
            if (input.value && input.value < today) {
                input.style.borderColor = '#e74c3c';
                input.style.backgroundColor = 'rgba(231, 76, 60, 0.05)';
                
                // Agregar mensaje de error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'fecha-form-help';
                errorMsg.style.color = '#e74c3c';
                errorMsg.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Esta fecha ya pasó. Por favor, selecciona una fecha futura.';
                input.parentNode.insertBefore(errorMsg, input.nextSibling);
            }
        });
        
        // Validar formulario de fundamentación
        const formFundamentacion = document.querySelector('form[action*="fundamentacion"]');
        if (formFundamentacion) {
            formFundamentacion.addEventListener('submit', function(e) {
                const fechaInput = this.querySelector('input[type="date"]');
                if (fechaInput.value && new Date(fechaInput.value) < new Date(today)) {
                    e.preventDefault();
                    alert('La fecha de fundamentación no puede ser una fecha pasada.');
                    fechaInput.focus();
                }
            });
        }
        
        // Validar formularios de cortes
        const formsCortes = document.querySelectorAll('form[action*="corte"]');
        formsCortes.forEach(form => {
            form.addEventListener('submit', function(e) {
                const fechaInput = this.querySelector('input[type="date"]');
                if (fechaInput.value && new Date(fechaInput.value) < new Date(today)) {
                    e.preventDefault();
                    alert('La fecha del corte no puede ser una fecha pasada.');
                    fechaInput.focus();
                }
            });
        });
        
        // Confirmación para acciones importantes
        const deleteForms = document.querySelectorAll('form[onsubmit]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿Estás seguro de realizar esta acción? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
        
        // Mostrar/Ocultar ayuda contextual
        const ayudaBtn = document.createElement('button');
        ayudaBtn.type = 'button';
        ayudaBtn.innerHTML = '<i class="fas fa-question-circle"></i> Ayuda';
        ayudaBtn.style.position = 'fixed';
        ayudaBtn.style.bottom = '20px';
        ayudaBtn.style.right = '20px';
        ayudaBtn.style.background = 'linear-gradient(135deg, #3498db, #2980b9)';
        ayudaBtn.style.color = 'white';
        ayudaBtn.style.border = 'none';
        ayudaBtn.style.padding = '12px 20px';
        ayudaBtn.style.borderRadius = '50px';
        ayudaBtn.style.cursor = 'pointer';
        ayudaBtn.style.boxShadow = '0 4px 15px rgba(52, 152, 219, 0.3)';
        ayudaBtn.style.zIndex = '1000';
        ayudaBtn.style.display = 'flex';
        ayudaBtn.style.alignItems = 'center';
        ayudaBtn.style.gap = '8px';
        
        ayudaBtn.addEventListener('click', function() {
            alert('AYUDA:\n\n1. Fundamentación: Fecha límite para la entrega de fundamentaciones.\n2. Cortes: Fechas límite para cada corte de tesis (4 en total).\n3. Las fechas no pueden ser pasadas.\n4. Se recomienda establecer las fechas en orden cronológico.\n5. Puedes extender todas las fechas o eliminarlas masivamente.');
        });
        
        document.body.appendChild(ayudaBtn);
    });
</script>
@endpush
@endsection