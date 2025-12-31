@extends('layouts.crud')

@section('content')

<link rel="stylesheet" href="css/formulario.css">
<link rel="stylesheet" href="css/gestionar/gestionarGrupo.css">
<link rel="stylesheet" href="css/gestionar/gestionarRoles.css">
<script src="js/agregar/agregarRol.js"></script>
<script src="js/modificarRol.js"></script>
<script src="js/actualizar/actualizarRol.js"></script>
<script src="js/eliminar.js"></script>

<h1>Gestionar Roles</h1>

<form action="/agregarRol" id="formulario_rol" method="post">
    @csrf
    <input type="hidden" id="enviar_id" name="id">

    <div class="campo" id="campo_rol">
        <label for="rol" id="label_rol">Rol</label>
        <input type="text" id="rol" name="rol" class="atributo" required>
    </div>


    <div class="campo" id="campo_permisos">
        <label id="label_permiso">Permisos</label>
        <div class="permisos-container">
            @foreach ($permisos as $permiso)
            <div class="permiso-item">
                <input type="checkbox" 
                       name="permisos[]" 
                       id="permiso_{{ $permiso->id }}" 
                       value="{{ $permiso->id }}"
                       class="permiso-checkbox">
                <label for="permiso_{{ $permiso->id }}" class="permiso-label">
                    {{ $permiso->permiso }}
                </label>
            </div>
            @endforeach
        </div>
    </div>
</form>

<form id="formEliminar" method="POST" action="/eliminarRol" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<table>
    <thead>
        <tr>
            <th>Rol</th>
            <th>Permisos</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($objetos as $objeto)
        <tr id="{{ $objeto->id }}">
            <td>{{ $objeto->rol }}</td>
            <td>
                @if($objeto->permisos->count() > 0)
                    <div class="permisos-list">
                        @foreach($objeto->permisos as $permiso)
                            <span class="permiso-badge">{{ $permiso->permiso }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="sin-permisos">Sin permisos asignados</span>
                @endif
            </td>
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