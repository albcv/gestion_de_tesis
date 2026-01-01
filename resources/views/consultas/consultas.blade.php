@extends('layouts.app')


@section('content')

    <img src="{{ asset('img/UNICA.png') }}" alt="Imagen de la UNICA" id="i1">
    <img src="{{ asset('img/tesis2.png') }}" alt="Ícono de tesis" id="i4">
    
    @vite(['resources/css/gestionar/gestionar.css'])
    @vite(['resources/css/consultas/consultas.css'])
    @vite(['resources/css/consultas/consultas2.css'])

    
    
    <h1>Consultas</h1>

    <ul class="entidades">


        <li><a href="/estudiantes">Estudiantes</a></li>
        <li><a href="/profesores">Profesores</a></li>
    
        
        

    </ul>



@endsection
