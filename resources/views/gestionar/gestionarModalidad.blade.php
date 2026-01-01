@extends('layouts.crud')

@section('content')

@vite(['resources/css/gestionar/gestionarModalidad.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarModalidad.js'])
@vite(['resources/js/actualizar/actualizarModalidad.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar Modalidad</h1>


<form action="/agregarModalidad" id="formulario_modalidad" method="post">

<input type="hidden" id="enviar_id" name="id">

@csrf
<div class="campo" id="campo_nombre_modalidad">
<label for="nombre_modalidad">Nombre</label>
<input type="text" id="nombre_modalidad" list="modalidades" name="nombre_modalidad" class="atributo" minlength="10" maxlength="50" required>
<datalist id="modalidades">

<option value="Curso regular diurno"></option>
<option value="Curso por encuentro"></option>
<option value="Curso a distancia"></option>


</datalist>
</div>



</form>

<form id="formEliminar" method="POST" action="/eliminarModalidad" style="display: none;">
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



@foreach ($modalidades as $modalidad)

<tr id="{{ $modalidad->idModalidad }}">

    <td>{{$modalidad->Nombre_modalidad}}</td>
 


</tr>

@endforeach

@if (count($modalidades)==0)
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
