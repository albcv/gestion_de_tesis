@extends('layouts.crud')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/gestionar/gestionarGrupo.css">
<link rel="stylesheet" href="css/gestionar/gestionarRoles.css">
<link rel="stylesheet" href="css/gestionar/gestionarPermisos.css">
<script src="js/agregar/agregarPermiso.js"></script>
<script src="js/actualizar/actualizarPermiso.js"></script>
<script src="js/eliminar.js"></script>

<h1>Gestionar Permisos</h1>

<form action="/agregarPermiso" id="formulario_permiso" method="post">

    @csrf

    <input type="hidden" id="enviar_id" name="id">

    <div class="campo" id="campo_permiso">
    <label for="permiso">Permiso</label>
    <input type="text" id="permiso" name="permiso" class="atributo" required>
    </div>

</form>


<form id="formEliminar" method="POST" action="/eliminarPermiso" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<table>
    <thead>
        <tr>
            <th>Permiso</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($objetos as $objeto)
        <tr id="{{ $objeto->id }}">
            <td>{{ $objeto->permiso }}</td>
            
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