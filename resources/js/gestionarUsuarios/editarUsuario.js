document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formulario_usuario');
    
    // Validación antes de enviar
    formulario.addEventListener('submit', function(event) {
        const rolSelect = document.getElementById('rol');
        const opcionSeleccionada = rolSelect.options[rolSelect.selectedIndex];
        const nombreRol = opcionSeleccionada.textContent.trim().toLowerCase();
        
        // Validar campos según el rol
        if (nombreRol.includes('estudiante')) {
            const camposRequeridos = [
                'ci_estudiante', 'nombre_estudiante', 'apellido1_estudiante',
                'numero_estudiante', 'sexo_estudiante', 'fecha_ingreso',
                'año_académico', 'id_grupo', 'id_modalidad'
            ];
            
            let valido = true;
            camposRequeridos.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo && !campo.value.trim()) {
                    alert(`El campo ${campo.previousElementSibling.textContent} es requerido para estudiantes`);
                    valido = false;
                }
            });
            
            if (!valido) {
                event.preventDefault();
            }
            
        } else if (nombreRol.includes('profesor')) {
            const camposRequeridos = [
                'ci_profesor', 'nombre_profesor', 'apellido1_profesor',
                'id_departamento', 'categoría_docente', 'categoría_científica'
            ];
            
            let valido = true;
            camposRequeridos.forEach(campoId => {
                const campo = document.getElementById(campoId);
                if (campo && !campo.value.trim()) {
                    alert(`El campo ${campo.previousElementSibling.textContent} es requerido para profesores`);
                    valido = false;
                }
            });
            
            if (!valido) {
                event.preventDefault();
            }
        }
    });
});