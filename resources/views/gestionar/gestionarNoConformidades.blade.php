@extends('layouts.crud')

@section('content')

@vite(['resources/css/gestionar/gestionarNoConformidades.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarNoConformidades.js'])
@vite(['resources/js/actualizar/actualizarNoConformidad.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar no conformidades</h1>

<form action="/agregarNoConformidades" id="formulario_no_conformidades" method="post">

@csrf

<input type="hidden" id="enviar_id" name="id">

<div class="campo" id="campo_deficiencias_detectadas">
<label for="deficiencias_detectadas">Deficiencia detectada</label>
<input type="text" id="deficiencias_detectadas" name="deficiencias_detectadas" class="atributo" required>
</div>



</form>


<form id="formEliminar" method="POST" action="/eliminarNoConformidades" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>



<table>

    <thead>

        <tr>

            <th>Deficiencia detectada</th>

        </tr>


    </thead>


<tbody>



@foreach ($ncs as $nc)

<tr id="{{ $nc->idNoConformidades }}">

    <td>{{$nc->Deficiencias_detectadas}}</td>
  


</tr>

@endforeach

@if (count($ncs)==0)
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
