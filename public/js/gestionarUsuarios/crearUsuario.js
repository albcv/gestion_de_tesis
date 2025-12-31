document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rol');
    const seccionEstudiante = document.getElementById('datos_estudiante');
    const seccionProfesor = document.getElementById('datos_profesor');
    
    function actualizarSecciones() {
        const opcionSeleccionada = rolSelect.options[rolSelect.selectedIndex];
        const nombreRol = opcionSeleccionada.textContent.trim().toLowerCase();
        
        // Ocultar todas las secciones primero
        seccionEstudiante.style.display = 'none';
        seccionProfesor.style.display = 'none';
        
        // Deshabilitar/limpiar campos no necesarios
        deshabilitarCampos(seccionEstudiante);
        deshabilitarCampos(seccionProfesor);
        limpiarCampos(seccionEstudiante);
        limpiarCampos(seccionProfesor);
        
        // Mostrar sección según el rol
        if (nombreRol === 'estudiante') {
            seccionEstudiante.style.display = 'block';
            habilitarCampos(seccionEstudiante);
        } else if (nombreRol === 'profesor') {
            seccionProfesor.style.display = 'block';
            habilitarCampos(seccionProfesor);
        }
    }
    
    function habilitarCampos(seccion) {
        const campos = seccion.querySelectorAll('input, select');
        campos.forEach(campo => {
            campo.disabled = false;
            campo.required = true;
        });
    }
    
    function deshabilitarCampos(seccion) {
        const campos = seccion.querySelectorAll('input, select');
        campos.forEach(campo => {
            campo.disabled = true;
            campo.required = false;
        });
    }
    
    function limpiarCampos(seccion) {
        const campos = seccion.querySelectorAll('input, select');
        campos.forEach(campo => {
            if (campo.tagName === 'INPUT') {
                campo.value = '';
            } else if (campo.tagName === 'SELECT') {
                campo.selectedIndex = 0;
            }
        });
    }
    
    // Escuchar cambios en el select de rol
    rolSelect.addEventListener('change', actualizarSecciones);
    
    // Ejecutar al cargar
    actualizarSecciones();
});