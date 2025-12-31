@extends('layouts.app')


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>
    <link rel="stylesheet" href="css/crud.css">
    <script src="js/fila_seleccionada.js"></script>
    <script src="js/cancelar.js"></script>
    <script src="js/modificar.js"></script>
    <script src="js/vaciar.js"></script>
</head>
<body>

    @php
        // Verificar si el usuario es estudiante para mostrar/ocultar botón vaciar
        $esEstudiante = false;
        $esAdministrador = false;
        if (Auth::check()) {
            $user = Auth::user();
            $esEstudiante = $user->tienePermiso('gestionar_fundamentacion_estudiante');
            $esAdministrador = $user->tienePermiso('admin_fundamentaciones');
        }
    @endphp

    <fieldset class="contenedor_botones">
        <legend>Botones</legend>

        <button class="agregar" id="b1" type="submit">Agregar</button>

        @if(!$esEstudiante || $esAdministrador)
            <button class="eliminar" id="b2">Eliminar</button>
            <button class="modificar" id="b3">Modificar</button>
        @endif
        
        <button class="cancelar" id="b4">Cancelar</button>
        <button class="actualizar" id="b5">Actualizar</button>
        
        @if(!$esEstudiante || $esAdministrador)
            <button class="vaciar" id="b6">Vaciar tabla</button>
        @endif
    </fieldset>

</body>
</html>