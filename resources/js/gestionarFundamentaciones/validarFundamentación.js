document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formulario_fundamentacion');
    
    if (formulario) {
        formulario.addEventListener('submit', function(event) {
            const errores = validarFormularioFundamentacion();
            
            if (errores.length > 0) {
                event.preventDefault();
                
                let mensajeError = 'ERRORES DE VALIDACIÓN:\n\n';
                errores.forEach((error, index) => {
                    mensajeError += `${index + 1}. ${error}\n`;
                });
                
                alert(mensajeError);
            }
        });
    }
});

function validarFormularioFundamentacion() {
    let errores = [];
    
    // Obtener si es edición o creación
    const esEdicion = document.getElementById('editar') !== null;
    
    // Validar tesis
    const tesisSelect = document.getElementById('id_tesis');
    const tesisError = validarSelect(tesisSelect, 'Tesis');
    if (tesisError) errores.push(tesisError);
    
    // Validar documento (requerido solo en creación)
    const documentoInput = document.getElementById('documento');
    if (!esEdicion) {
        const documentoError = validarDocumento(documentoInput, true);
        if (documentoError) errores.push(documentoError);
    } else {
        // En edición, solo validar si se subió un archivo
        if (documentoInput.files.length > 0) {
            const documentoError = validarDocumento(documentoInput, false);
            if (documentoError) errores.push(documentoError);
        }
    }
    
    return errores;
}

function validarSelect(selectElement, nombreCampo) {
    if (!selectElement.value) {
        return `${nombreCampo} es requerido`;
    }
    return null;
}

function validarDocumento(documentoInput, esRequerido) {
    if (esRequerido && documentoInput.files.length === 0) {
        return 'El documento es requerido';
    }
    
    if (documentoInput.files.length > 0) {
        const file = documentoInput.files[0];
        const allowedExtensions = ['.pdf', '.doc', '.docx'];
        const fileName = file.name.toLowerCase();
        const isValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext));
        
        if (!isValidExtension) {
            return 'Solo se permiten archivos PDF, DOC y DOCX';
        }
        
        if (file.size > 10 * 1024 * 1024) { // 10MB
            return 'El archivo no puede exceder los 10MB';
        }
    }
    
    return null;
}

// Validación en tiempo real
function configurarValidacionEnTiempoReal() {
    const tesisSelect = document.getElementById('id_tesis');
    const documentoInput = document.getElementById('documento');
    
    if (tesisSelect) {
        tesisSelect.addEventListener('blur', function() {
            const error = validarSelect(this, 'Tesis');
            mostrarErrorCampo(this, error);
        });
    }
    
    if (documentoInput) {
        documentoInput.addEventListener('change', function() {
            const esEdicion = document.getElementById('editar') !== null;
            const esRequerido = !esEdicion;
            const error = validarDocumento(this, esRequerido);
            mostrarErrorCampo(this, error);
        });
    }
}

function mostrarErrorCampo(campo, mensajeError) {
    // Eliminar error anterior
    const errorAnterior = campo.parentElement.querySelector('.error-campo');
    if (errorAnterior) {
        errorAnterior.remove();
    }
    
    // Quitar clase de error del campo
    campo.classList.remove('campo-error');
    
    // Si hay error, mostrarlo
    if (mensajeError) {
        campo.classList.add('campo-error');
        
        const errorElemento = document.createElement('div');
        errorElemento.className = 'error-campo';
        errorElemento.style.color = 'red';
        errorElemento.style.fontSize = '12px';
        errorElemento.style.marginTop = '5px';
        errorElemento.textContent = mensajeError;
        
        campo.parentElement.appendChild(errorElemento);
    }
}

// Inicializar validación en tiempo real cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', configurarValidacionEnTiempoReal);