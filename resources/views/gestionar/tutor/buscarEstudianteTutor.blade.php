@extends('layouts.app')

@section('content')

@vite(['resources/css/gestionar/tutor/buscarEstudiante.css'])

<div class="main-content-wrapper">
    <div class="asignar-tutor-container">

        <div class="form-header">
            <h1>👨‍🏫 Asignar Tutor al Estudiante</h1>
            <p class="form-subtitle">Busca el estudiante al que deseas asignar uno o más tutores</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">
                <span class="alert-icon">❌</span> {{ session('error') }}
            </div>
        @endif

        <div class="form-container">
            <form id="formBuscarEstudiante" method="GET" action="">
                @csrf

                <div class="form-group">
                    <label for="estudiante_buscar" class="form-label">
                        <span class="label-icon">🔎</span> Buscar Estudiante
                    </label>

                    <input type="text"
                           id="estudiante_buscar"
                           class="select-profesor"
                           list="estudiante-list"
                           placeholder="Escribe nombre, apellidos o CI..."
                           autocomplete="off"
                           autofocus
                           required>

                    <datalist id="estudiante-list"></datalist>

                    <input type="hidden"
                           name="id_estudiante"
                           id="id_estudiante_hidden"
                           value="">

                    <small class="ayuda-campo">
                        Escribe al menos 2 caracteres. Solo se listan estudiantes con menos de
                        {{ \App\Http\Controllers\tutorEstudianteController::MAX_TUTORES_POR_ESTUDIANTE }}
                        tutores asignados.
                    </small>

                    <small id="estudiante_estado" style="display:none;"></small>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-asignar" id="btnContinuar" disabled>
                        <span class="btn-icon">→</span> Continuar
                    </button>
                    <a href="{{ route('gestionarUsuarios') }}" class="btn-cancelar">
                        <span class="btn-icon">←</span> Cancelar
                    </a>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputBuscar  = document.getElementById('estudiante_buscar');
    const datalist     = document.getElementById('estudiante-list');
    const inputHidden  = document.getElementById('id_estudiante_hidden');
    const estadoMsg    = document.getElementById('estudiante_estado');
    const form         = document.getElementById('formBuscarEstudiante');
    const btnContinuar = document.getElementById('btnContinuar');

    if (!inputBuscar || !datalist || !inputHidden) return;

    const urlBuscar = '{{ route("buscarEstudiantesTutor") }}';
    const urlBase   = '{{ url("/asignarTutor") }}';
    const mapLabels = new Map();
    let timeoutId = null;

    function setEstado(msg) {
        if (!estadoMsg) return;
        if (msg) {
            estadoMsg.textContent = msg;
            estadoMsg.style.display = 'inline-flex';
        } else {
            estadoMsg.style.display = 'none';
        }
    }

    function cargarEstudiantes(termino) {
        setEstado('Buscando...');

        const url = `${urlBuscar}?q=${encodeURIComponent(termino)}`;

        fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            datalist.innerHTML = '';
            mapLabels.clear();

            if (Array.isArray(data) && data.length) {
                data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.label;
                    datalist.appendChild(opt);
                    mapLabels.set(e.label, e.id);
                });

                setEstado(`${data.length} estudiante(s) encontrado(s)`);
            } else {
                setEstado('Sin estudiantes disponibles');
            }

            sincronizarHidden();
        })
        .catch(err => {
            console.error('Error al buscar estudiantes:', err);
            setEstado('Error al buscar');
        });
    }

    function sincronizarHidden() {
        const label = inputBuscar.value.trim();
        if (mapLabels.has(label)) {
            inputHidden.value = mapLabels.get(label);
            btnContinuar.disabled = false;
        } else {
            inputHidden.value = '';
            btnContinuar.disabled = true;
        }
    }

    inputBuscar.addEventListener('input', function () {
        clearTimeout(timeoutId);
        const termino = this.value.trim();

        if (mapLabels.has(termino)) {
            inputHidden.value = mapLabels.get(termino);
            btnContinuar.disabled = false;
            setEstado('');
            return;
        }

        inputHidden.value = '';
        btnContinuar.disabled = true;

        timeoutId = setTimeout(() => {
            cargarEstudiantes(termino);
        }, 250);
    });

    inputBuscar.addEventListener('blur', sincronizarHidden);
    inputBuscar.addEventListener('change', sincronizarHidden);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = inputHidden.value;
        if (!id) {
            alert('Por favor, selecciona un estudiante de la lista.');
            return;
        }
        window.location.href = urlBase + '/' + encodeURIComponent(id);
    });

    // Cargar opciones iniciales (los primeros 30 disponibles)
    cargarEstudiantes('');
});
</script>

@endsection