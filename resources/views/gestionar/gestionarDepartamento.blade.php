@extends('layouts.crud')

@section('content')


@vite(['resources/css/gestionar/gestionarDepartamento.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarDepartamento.js'])
@vite(['resources/js/actualizar/actualizarDepartamento.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar Departamento</h1>

<form action="/agregarDepartamento" id="formulario_departamento" method="post">

@csrf

<input type="hidden" id="enviar_id" name="id">

<div class="campo" id="campo_departamento">
<label for="departamento">Nombre del departamento</label>
<input type="text" id="departamento" name="departamento" class="atributo" minlength="10" maxlength="100" required>
</div>



</form>


<form id="formEliminar" method="POST" action="/eliminarDepartamento" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>



<table>

    <thead>

        <tr>

            <th>Nombre</th>

        </tr>


    </thead>


<tbody>



@foreach ($departamentos as $departamento)

<tr id="{{ $departamento->idDepartamento }}">

    <td>{{$departamento->Nombre_departamento}}</td>


</tr>

@endforeach

@if (count($departamentos)==0)
    <tr>
        <td style="border: none; font-size: 30px; font-weight: bold; color: red;">No hay registros</td>
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
