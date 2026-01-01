
document.addEventListener('DOMContentLoaded', function() {
    
    const agregar = document.querySelector('.agregar');
    
    if (!agregar) return; 
    
    agregar.setAttribute('form', 'formulario_facultad');
    
    const formulario = document.getElementById('formulario_facultad');
    
    if (formulario) {
        formulario.addEventListener('submit', function(event) {
            if (!validarFacultad()) {
                alert('Facultad incorrecta');
                event.preventDefault();
                return;
            }
            
            if (!validarSiglas()) {
                alert('Siglas incorrectas');
                event.preventDefault();
                return;
            }
        });
    }
    
    
    function validarFacultad() {
        const nombreInput = document.getElementById('nombre_facultad');
        if (!nombreInput) return false;
        
        let nombre = nombreInput.value;
        
        if (nombre.length === 0 || nombre.length > 100 || nombre.length < 20 || !isNaN(nombre)) {
            return false;
        }
        
        nombre = nombre.toLowerCase();
        
        if (!nombre.includes('facultad')) {
            return false;
        }
        
        return true;
    }
    
    function validarSiglas() {
        const siglasInput = document.getElementById('siglas');
        if (!siglasInput) return false;
        
        const siglas = siglasInput.value;
        
        if (siglas.length === 0 || siglas.length > 10 || siglas.length < 3) {
            return false;
        }
        
        if (!(siglas === siglas.toUpperCase())) {
            return false;
        }
        
        return true;
    }
});