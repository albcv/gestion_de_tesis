@extends('layouts.crud')

@section('content')

@vite(['resources/css/gestionar/gestionarCortes.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarCorte.js'])
@vite(['resources/js/actualizar/actualizarCorte.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar versiones de fundamentaciones</h1>

<form action="agregarVersiónFundamentación" id="formulario_versión_fundamentación" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" id="enviar_id" name="id">
    
    <div class="campo" id="campo_id_tesis">
        <label for="id_tesis">Trabajo de diploma</label>
        <select id="id_tesis" name="id_tesis" class="atributo" required>
            <option value=""></option>
            @foreach($tesis as $tesisItem)
                <option value="{{ $tesisItem->id }}">
                    {{ $tesisItem->Nombre_trabajo }}
                </option>
            @endforeach
        </select>
    </div>
    
    <div class="campo" id="campo_número_corte">
        <label for="número_corte">Número de corte</label>
        <select id="número_corte" name="número_corte" class="atributo" required>
            <option value=""></option>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
        </select>
    </div>

    <div class="campo" id="campo_enlace">
        <label for="enlace">Enlace a GitHub</label>
        <input type="url" id="enlace" name="enlace" class="atributo" placeholder="https://ejemplo.com" required>
    </div>
    
    <div class="campo" id="campo_documento">
        <label for="documento">Documento</label>
        <input type="file" id="documento" name="documento" class="atributo" required>
    </div>
</form>

<form id="formEliminar" method="POST" action="/eliminarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<!-- Formularios ocultos para aprobar, desaprobar y revertir -->
<form id="formAprobarCorte" method="POST" action="/aprobarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdAprobarCorte">
</form>

<form id="formDesaprobarCorte" method="POST" action="/desaprobarCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdDesaprobarCorte">
</form>

<form id="formRevertirCorte" method="POST" action="/revertirCorte" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdRevertirCorte">
</form>

<table>
    <thead>
        <tr>
            <th>Trabajo de diploma</th>
            <th>Número_corte</th>
            <th>Enlace a GitHub</th>
            <th>Documento</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cortes as $corte)
        <tr id="{{ $corte->idCortes_de_tesis }}" data-tesis-id="{{ $corte->id_tesis }}">
            <td>
                @if($corte->tesis)
                    {{ $corte->tesis->Nombre_trabajo }}
                @else
                    <span style="color: #000;">Trabajo no encontrado</span>
                @endif
            </td>
            <td>{{ $corte->Numero_corte }}</td>
            <td>
                @if(!empty($corte->Enlace_GitHub))
                    <a href="{{ $corte->Enlace_GitHub }}" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       style="color: #f00; text-decoration: none; display: block; padding: 8px;font-weight: bold;">
                        {{ $corte->Enlace_GitHub }}
                    </a>
                @else
                    <span style="color: #000;">Sin enlace</span>
                @endif
            </td>
            <td>
                @if(!empty($corte->ruta_documento))
                    <a href="{{ route('ver-documento', $corte->idCortes_de_tesis) }}" 
                       class="doc-link" 
                       style="color: #000; text-decoration: none; display: inline-block; padding: 5px 10px; border: 1px solid #ccc; border-radius: 4px; background-color: #f8f9fa;">
                        📥 Descargar Documento
                    </a>
                @else
                    <span style="color: #000;">Sin documento</span>
                @endif
            </td>
            <td>
                @if($corte->aprobado)
                    <span style="color: green; font-weight: bold;">Aprobado</span>
                @elseif($corte->desaprobado)
                    <span style="color: red; font-weight: bold;">Desaprobado</span>
                @else
                    <span style="color: #000; font-weight: bold;">Pendiente</span>
                @endif
            </td>
            <td>
                @if(!$corte->aprobado && !$corte->desaprobado)
                    <!-- Estado Pendiente: Mostrar botones Aprobar y Desaprobar -->
                    <button type="button" class="btn-aprobar" onclick="aprobarCorte({{ $corte->idCortes_de_tesis }})" 
                            style="background-color:green; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">
                        Aprobar
                    </button>
                    <button type="button" class="btn-desaprobar" onclick="desaprobarCorte({{ $corte->idCortes_de_tesis }})" 
                            style="background-color: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                        Desaprobar
                    </button>
                @else
                    <!-- Estado Aprobado o Desaprobado: Mostrar botón Revertir -->
                    <button type="button" class="btn-revertir" onclick="revertirCorte({{ $corte->idCortes_de_tesis }})" 
                            style="background-color: lightblue; color: black; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                        Revertir a Pendiente
                    </button>
                @endif
            </td>
        </tr>
        @endforeach
        
        @if (count($cortes) == 0)
            <tr>
                <td colspan="6" style="border: none; font-size: 30px; font-weight: bold; color: red; text-align: center;">No hay registros</td>
            </tr>
            <script>
                document.getElementById('b2').style.display='none';
                document.getElementById('b3').style.display='none';
                document.getElementById('b4').style.display='none';
                document.getElementById('b6').style.display='none';
            </script>
        @else
            <script>
                document.getElementById('b2').style.display='inline-block';
                document.getElementById('b3').style.display='inline-block';
                document.getElementById('b4').style.display='inline-block';
                document.getElementById('b6').style.display='inline-block';
            </script>
        @endif
    </tbody>
</table>

<script>
function aprobarCorte(id) {
    if (confirm('¿Está seguro de que desea aprobar este corte?')) {
        document.getElementById('inputIdAprobarCorte').value = id;
        document.getElementById('formAprobarCorte').submit();
    }
}

function desaprobarCorte(id) {
    if (confirm('¿Está seguro de que desea desaprobar este corte?')) {
        document.getElementById('inputIdDesaprobarCorte').value = id;
        document.getElementById('formDesaprobarCorte').submit();
    }
}

function revertirCorte(id) {
    if (confirm('¿Está seguro de que desea revertir este corte a estado pendiente?')) {
        document.getElementById('inputIdRevertirCorte').value = id;
        document.getElementById('formRevertirCorte').submit();
    }
}
</script>

@endsection