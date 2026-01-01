@extends('layouts.app')

@section('content')

    <img src="{{ asset('img/UNICA.png') }}" alt="Imagen de la UNICA" id="i1">
    <img src="{{ asset('img/tesis2.png') }}" alt="Ícono de tesis" id="i4">
    

    @vite(['resources/css/gestionar/gestionar.css'])
    @vite(['resources/css/consultas/consultas.css'])
    
    <h1>Estudiantes</h1>


    <ul class="entidades">

        <li><a href="/buscarEstudiante">Buscar estudiante por CI</a></li>
        <li><a href="/estudiantesCursoDiurno">Estudiantes del curso regular diurno</a></li>
        <li><a href="/estudiantesCursoEncuentro">Estudiantes del curso por encuentro</a></li>
        <li><a href="/estudiantes-facultad">Estudiantes de una facultad</a></li>
        <li><a href="/estudiantes_sin_tutor">Estudiantes sin tutor</a></li>
        <li><a href="/estudiantesAtrasadosFundamentación">Estudiantes atrasados en la fundamentación de la tesis</a></li>


    </ul>



@endsection
