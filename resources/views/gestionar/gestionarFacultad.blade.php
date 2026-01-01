@extends('layouts.crud')

@section('content')

@vite(['resources/css/gestionar/gestionarFacultad.css'])
@vite(['resources/css/formulario.css'])
@vite(['resources/js/agregar/agregarFacultad.js'])
@vite(['resources/js/actualizar/actualizarFacultad.js'])
@vite(['resources/js/eliminar.js'])



<h1>Gestionar Facultad</h1>

<form action="/agregarFacultad" id="formulario_facultad" method="post">
    @csrf
    <input type="hidden" id="enviar_id" name="id">

    <div class="campo" id="campo_nombre_facultad">
        <label for="nombre_facultad">Nombre</label>
        <input type="text" id="nombre_facultad" list="facultad" name="nombre_facultad" 
               class="atributo" required minlength="20" maxlength="100"
               >

        <datalist id="facultad">
            <option value="Facultad de Ciencias Agropecuarias"></option>
            <option value="Facultad de Ciencias Técnicas"></option>
            <option value="Facultad de Ciencias Económicas y Empresariales"></option>
            <option value="Facultad de Ciencias Informáticas"></option>
            <option value="Facultad de Ciencias Sociales y Humanísticas"></option>
            <option value="Facultad de Ciencias Pedagógicas"></option>
            <option value="Facultad de Ciencias de la Cultura Física y el Deporte"></option>
        </datalist>
    </div>

    <div class="campo" id="campo_siglas">
        <label for="siglas">Siglas</label>
        <input type="text" id="siglas" list="list_siglas" name="siglas" 
               class="atributo" required minlength="3" maxlength="10">
        <datalist id="list_siglas">
            <option value="FCA"></option>
            <option value="FCT"></option>
            <option value="FCEE"></option>
            <option value="FICE"></option>
            <option value="FCSH"></option>
            <option value="FCP"></option>
            <option value="FCCFD"></option>
        </datalist>
    </div>
</form>

<form id="formEliminar" method="POST" action="/eliminarFacultad" style="display: none;">
    @csrf
    <input type="hidden" name="id" id="inputIdEliminar">
</form>

<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Siglas</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($facultades as $facultad)
        <tr id="{{ $facultad->idFacultad }}">
            <td>{{$facultad->Nombre_facultad}}</td>
            <td>{{$facultad->Siglas}}</td>
        </tr>
        @endforeach

        @if (count($facultades)==0)
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