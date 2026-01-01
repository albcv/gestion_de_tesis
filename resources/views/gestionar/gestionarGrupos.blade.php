@extends('layouts.crud')

@section('content')

@vite(['resources/css/gestionar/gestionarGrupo.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarGrupo.js'])
@vite(['resources/js/actualizar/actualizarGrupo.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar Grupos</h1>

<form action="/agregarGrupo" id="formulario_grupo" method="post">
    @csrf
    <input type="hidden" id="enviar_id" name="id">

    <div class="campo" id="campo_número">
        <label for="número">Número del grupo</label>
        <input type="number" id="número" name="número" class="atributo" placeholder="301" required>
    </div>

</form>

<form id="formEliminar" method="POST" action="/eliminarGrupo" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<table>
    <thead>
        <tr>
            <th>Número del grupo</th>
        
        </tr>
    </thead>
    <tbody>
        @foreach ($objetos as $objeto)
        <tr id="{{ $objeto->id }}">
            <td>{{ $objeto->número }}</td>
            
        </tr>
        @endforeach

        @if (count($objetos) == 0)
            <tr>
                <td colspan="2" style="border: none; font-size: 30px; font-weight: bold; color: red; text-align: center;">
                    No hay registros
                </td>
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

@endsection