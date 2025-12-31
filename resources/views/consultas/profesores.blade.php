@extends('layouts.app')

@section('content')

    <img src="img/UNICA.png" alt="Imagen de la UNICA" id="i1">
    <img src="img/tesis2.png" alt="Ícono de tesis" id="i4">
    

    <link rel="stylesheet" href="css/gestionar/gestionar.css">
    <link rel="stylesheet" href="css/consultas/consultas.css">
    
    <h1>Profesores</h1>

    <ul class="entidades">

        <li><a href="/buscarProfesor">Buscar profesor por CI</a></li>
        <li><a href="/profesoresDepartamento">Profesores de un departamento</a></li>
        <li><a href="/profesoresNoTutores">Profesores no tutores de tesis</a></li>
        <li><a href="/profesoresDoctores">Profesores (Doctor en Ciencias)</a></li>
        <li><a href="/profesoresMáster">Profesores (Máster en Ciencias)</a></li>

    </ul>



@endsection
