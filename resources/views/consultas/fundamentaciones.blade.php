@extends('layouts.app')

@section('content')

    <img src="img/UNICA.png" alt="Imagen de la UNICA" id="i1">
    <img src="img/tesis2.png" alt="Ícono de tesis" id="i4">
    

    <link rel="stylesheet" href="css/gestionar/gestionar.css">
    <link rel="stylesheet" href="css/consultas/consultas.css">
    
    <h1>Fundamentaciones</h1>


    <ul class="entidades">

        <li><a href="/fundamentacionesAprobadas">Fundamentaciones aprobadas</a></li>
        <li><a href="/fundamentacionesDesaprobadas">Fundamentaciones desaprobadas</a></li>
        <li><a href="/fundamentacionesPendientes">Fundamentaciones pendientes</a></li>


    </ul>



@endsection
