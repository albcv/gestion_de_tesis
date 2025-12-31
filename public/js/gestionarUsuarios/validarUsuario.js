document.addEventListener('DOMContentLoaded', function() {
    // Agregar estilos para errores
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = estiloError;
    document.head.appendChild(styleSheet);
    
    // Configurar validación en tiempo real 
    configurarValidacionEnTiempoReal();
    
    // Configurar validación al enviar el formulario
    const formulario = document.getElementById('formulario_usuario');
    if (formulario) {
        formulario.addEventListener('submit', function(event) {
            // Determinar si es creación o edición
            const esCreacion = !document.getElementById('editar');
           
            
            // Validar formulario completo
            const resultadoValidacion = validarFormularioCompleto(esCreacion);
            
            if (resultadoValidacion !== true) {
                event.preventDefault(); // Prevenir envío del formulario
                
                // Mostrar todos los errores en un alert
                let mensajeError = 'ERRORES DE VALIDACIÓN:\n\n';
                resultadoValidacion.forEach((error, index) => {
                    mensajeError += `${index + 1}. ${error}\n`;
                });
                
                alert(mensajeError);
                

                mostrarErroresEnPagina(resultadoValidacion);
            }
        });
    }
});


// Validación de CI 
function validarCI(ci) {
    if (!ci) return 'El carnet de identidad es requerido';
    if (ci.length !== 11) {
        return 'El CI debe tener exactamente 11 dígitos';
    }
    
    // Validar que sean solo números
    if (!/^\d+$/.test(ci)) {
        return 'El CI solo puede contener números';
    }
    
    // Validar formato yymmdd
    const fechaParte = ci.substring(0, 6);
    const year = parseInt(fechaParte.substring(0, 2));
    const month = parseInt(fechaParte.substring(2, 4));
    const day = parseInt(fechaParte.substring(4, 6));
    
    // Validar mes
    if (month < 1 || month > 12) {
        return 'Los dígitos 3-4 del CI deben representar un mes válido (01-12)';
    }
    
    // Validar día
    const diasPorMes = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (day < 1 || day > diasPorMes[month - 1]) {
        return 'Los dígitos 5-6 del CI deben representar un día válido para el mes';
    }
    
    return null;
}

// Validación de longitud de campos
function validarLongitud(valor, min, max, nombreCampo) {
    if (!valor) return `${nombreCampo} es requerido`;
    valor = valor.toString().trim();
    if (valor.length < min) {
        return `${nombreCampo} debe tener al menos ${min} caracteres`;
    }
    if (valor.length > max) {
        return `${nombreCampo} no puede exceder los ${max} caracteres`;
    }
    return null;
}

// Validación de email
function validarEmail(email) {
    if (!email) return 'El email es requerido';
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        return 'El formato del email no es válido';
    }
    return null;
}

// Validación de número entero positivo
function validarNumeroEntero(valor, nombreCampo, min = null, max = null) {
    if (!valor && valor !== 0) return `${nombreCampo} es requerido`;
    if (isNaN(valor)) return `${nombreCampo} debe ser un número`;
    if (!Number.isInteger(Number(valor))) return `${nombreCampo} debe ser un número entero`;
    if (Number(valor) < 0) return `${nombreCampo} debe ser un número positivo`;
    if (min !== null && Number(valor) < min) return `${nombreCampo} debe ser mayor o igual a ${min}`;
    if (max !== null && Number(valor) > max) return `${nombreCampo} debe ser menor o igual a ${max}`;
    return null;
}

// Validación de fecha
function validarFecha(fecha, nombreCampo) {
    if (!fecha) return `${nombreCampo} es requerido`;
    const fechaDate = new Date(fecha);
    if (isNaN(fechaDate.getTime())) {
        return `${nombreCampo} no es una fecha válida`;
    }
    // Validar que no sea fecha futura (opcional, puedes ajustar según tus necesidades)
    const hoy = new Date();
    if (fechaDate > hoy) {
        return `${nombreCampo} no puede ser una fecha futura`;
    }
    return null;
}

// Validación de rol
function validarRol(rolId) {
    if (!rolId) return 'El rol es requerido';
    return null;
}

// Validación de select
function validarSelect(valor, nombreCampo) {
    if (!valor) return `${nombreCampo} es requerido`;
    return null;
}

// Validación de password
function validarPassword(password, esCreacion = true) {
    if (esCreacion) {
        if (!password) return 'La contraseña es requerida';
        if (password.length < 6) return 'La contraseña debe tener al menos 6 caracteres';
    } else {
        // Para edición, la contraseña es opcional, pero si se ingresa, debe cumplir las reglas
        if (password && password.length < 6) return 'La contraseña debe tener al menos 6 caracteres';
    }
    if (password && password.length > 255) return 'La contraseña no puede exceder los 255 caracteres';
    return null;
}

// Validación completa del formulario
function validarFormularioCompleto(esCreacion = true) {
    let errores = [];
    
    // Obtener elementos del formulario
    const formulario = document.getElementById('formulario_usuario');
    if (!formulario) return null;
    
    // Validar campos básicos del usuario
    const nombre = document.getElementById('name')?.value || '';
    const email = document.getElementById('email')?.value || '';
    const rolSelect = document.getElementById('rol');
    const rolId = rolSelect?.value || '';
    const rolText = rolSelect?.options[rolSelect.selectedIndex]?.text.toLowerCase() || '';
    const password = document.getElementById('password')?.value || '';
    
    // Validar nombre de usuario
    const errorNombre = validarLongitud(nombre, 3, 40, 'El nombre de usuario');
    if (errorNombre) errores.push(errorNombre);
    
    // Validar email
    const errorEmail = validarEmail(email);
    if (errorEmail) errores.push(errorEmail);
    
    // Validar rol
    const errorRol = validarRol(rolId);
    if (errorRol) errores.push(errorRol);
    
    // Validar contraseña
    const errorPassword = validarPassword(password, esCreacion);
    if (errorPassword) errores.push(errorPassword);
    
    // Validar campos según el rol
    if (rolText.includes('estudiante')) {
        errores = errores.concat(validarCamposEstudiante());
    } else if (rolText.includes('profesor')) {
        errores = errores.concat(validarCamposProfesor());
    }
    
    // Si no hay errores, retornar true
    if (errores.length === 0) {
        return true;
    }
    
    // Si hay errores, retornar array de errores
    return errores;
}

// Validación de campos de estudiante
function validarCamposEstudiante() {
    let errores = [];
    
    // Obtener valores de los campos de estudiante
    const ciEstudiante = document.getElementById('ci_estudiante')?.value || '';
    const nombreEstudiante = document.getElementById('nombre_estudiante')?.value || '';
    const apellido1Estudiante = document.getElementById('apellido1_estudiante')?.value || '';
    const apellido2Estudiante = document.getElementById('apellido2_estudiante')?.value || '';
    const numeroEstudiante = document.getElementById('numero_estudiante')?.value || '';
    const sexoEstudiante = document.getElementById('sexo_estudiante')?.value || '';
    const fechaIngreso = document.getElementById('fecha_ingreso')?.value || '';
    const añoAcademico = document.getElementById('año_académico')?.value || '';
    const idGrupo = document.getElementById('id_grupo')?.value || '';
    const idModalidad = document.getElementById('id_modalidad')?.value || '';
    
    // Validar CI
    const errorCI = validarCI(ciEstudiante);
    if (errorCI) errores.push(`CI Estudiante: ${errorCI}`);
    
    // Validar nombre
    const errorNombreEst = validarLongitud(nombreEstudiante, 3, 40, 'Nombre del estudiante');
    if (errorNombreEst) errores.push(errorNombreEst);
    
    // Validar apellido1
    const errorApellido1Est = validarLongitud(apellido1Estudiante, 3, 40, 'Primer apellido del estudiante');
    if (errorApellido1Est) errores.push(errorApellido1Est);
    
    // Validar apellido2
    const errorApellido2Est = validarLongitud(apellido2Estudiante, 3, 40, 'Segundo apellido del estudiante');
    if (errorApellido2Est) errores.push(errorApellido2Est);
    
    // Validar número de estudiante
    const errorNumeroEst = validarNumeroEntero(numeroEstudiante, 'Número del estudiante', 1);
    if (errorNumeroEst) errores.push(errorNumeroEst);
    
    // Validar sexo
    const errorSexoEst = validarSelect(sexoEstudiante, 'Sexo del estudiante');
    if (errorSexoEst) errores.push(errorSexoEst);
    
    // Validar fecha de ingreso
    const errorFechaIngreso = validarFecha(fechaIngreso, 'Fecha de ingreso');
    if (errorFechaIngreso) errores.push(errorFechaIngreso);
    
    // Validar año académico
    const errorAnoAcademico = validarNumeroEntero(añoAcademico, 'Año académico', 1, 6);
    if (errorAnoAcademico) errores.push(errorAnoAcademico);
    
    // Validar grupo
    const errorGrupo = validarSelect(idGrupo, 'Grupo');
    if (errorGrupo) errores.push(errorGrupo);
    
    // Validar modalidad
    const errorModalidad = validarSelect(idModalidad, 'Modalidad');
    if (errorModalidad) errores.push(errorModalidad);
    
    return errores;
}

// Validación de campos de profesor
function validarCamposProfesor() {
    let errores = [];
    
    // Obtener valores de los campos de profesor
    const ciProfesor = document.getElementById('ci_profesor')?.value || '';
    const nombreProfesor = document.getElementById('nombre_profesor')?.value || '';
    const apellido1Profesor = document.getElementById('apellido1_profesor')?.value || '';
    const apellido2Profesor = document.getElementById('apellido2_profesor')?.value || '';
    const idDepartamento = document.getElementById('id_departamento')?.value || '';
    const categoriaDocente = document.getElementById('categoría_docente')?.value || '';
    const categoriaCientifica = document.getElementById('categoría_científica')?.value || '';
    
    // Validar CI
    const errorCI = validarCI(ciProfesor);
    if (errorCI) errores.push(`CI Profesor: ${errorCI}`);
    
    // Validar nombre
    const errorNombreProf = validarLongitud(nombreProfesor, 3, 40, 'Nombre del profesor');
    if (errorNombreProf) errores.push(errorNombreProf);
    
    // Validar apellido1
    const errorApellido1Prof = validarLongitud(apellido1Profesor, 3, 40, 'Primer apellido del profesor');
    if (errorApellido1Prof) errores.push(errorApellido1Prof);
    
    // Validar apellido2
    const errorApellido2Prof = validarLongitud(apellido2Profesor, 3, 40, 'Segundo apellido del profesor');
    if (errorApellido2Prof) errores.push(errorApellido2Prof);
    
    // Validar departamento
    const errorDepartamento = validarSelect(idDepartamento, 'Departamento');
    if (errorDepartamento) errores.push(errorDepartamento);
    
    // Validar categoría docente
    const errorCatDocente = validarLongitud(categoriaDocente, 3, 30, 'Categoría docente');
    if (errorCatDocente) errores.push(errorCatDocente);
    
    // Validar categoría científica
    const errorCatCientifica = validarLongitud(categoriaCientifica, 3, 30, 'Categoría científica');
    if (errorCatCientifica) errores.push(errorCatCientifica);
    
    return errores;
}

// Validación en tiempo real 
function configurarValidacionEnTiempoReal() {
    // Configurar validación para campos de CI
    const campoCIEstudiante = document.getElementById('ci_estudiante');
    const campoCIProfesor = document.getElementById('ci_profesor');
    
    if (campoCIEstudiante) {
        campoCIEstudiante.addEventListener('blur', function() {
            const error = validarCI(this.value);
            mostrarErrorCampo(this, error);
        });
    }
    
    if (campoCIProfesor) {
        campoCIProfesor.addEventListener('blur', function() {
            const error = validarCI(this.value);
            mostrarErrorCampo(this, error);
        });
    }
    
    // Configurar validación para campos de longitud
    const camposLongitud = [
        {id: 'name', min: 3, max: 40, nombre: 'Nombre de usuario'},
        {id: 'nombre_estudiante', min: 3, max: 40, nombre: 'Nombre'},
        {id: 'apellido1_estudiante', min: 3, max: 40, nombre: 'Primer apellido'},
        {id: 'apellido2_estudiante', min: 3, max: 40, nombre: 'Segundo apellido'},
        {id: 'nombre_profesor', min: 3, max: 40, nombre: 'Nombre'},
        {id: 'apellido1_profesor', min: 3, max: 40, nombre: 'Primer apellido'},
        {id: 'apellido2_profesor', min: 3, max: 40, nombre: 'Segundo apellido'},
        {id: 'categoría_docente', min: 3, max: 30, nombre: 'Categoría docente'},
        {id: 'categoría_científica', min: 3, max: 30, nombre: 'Categoría científica'},
    ];
    
    camposLongitud.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        if (elemento) {
            elemento.addEventListener('blur', function() {
                const error = validarLongitud(this.value, campo.min, campo.max, campo.nombre);
                mostrarErrorCampo(this, error);
            });
        }
    });
    
    // Configurar validación para email
    const campoEmail = document.getElementById('email');
    if (campoEmail) {
        campoEmail.addEventListener('blur', function() {
            const error = validarEmail(this.value);
            mostrarErrorCampo(this, error);
        });
    }
    
    // Configurar validación para contraseña
    const campoPassword = document.getElementById('password');
    if (campoPassword) {
        campoPassword.addEventListener('blur', function() {
            // Para validación en tiempo real, no requerimos contraseña si es edición
            const esCreacion = !document.getElementById('enviar_id')?.value;
            const error = validarPassword(this.value, esCreacion);
            mostrarErrorCampo(this, error);
        });
    }
}

// Mostrar error en un campo específico
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

// CSS para campos con error (puedes agregarlo a tu archivo CSS)
const estiloError = `
    .campo-error {
        border-color: red !important;
        border-width: 2px !important;
    }
    
    .error-campo {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        font-weight: bold;
    }
`;



// Función para mostrar errores en la página 
function mostrarErroresEnPagina(errores) {
    // Eliminar contenedor de errores anterior si existe
    const contenedorAnterior = document.getElementById('errores-validacion');
    if (contenedorAnterior) {
        contenedorAnterior.remove();
    }
    
    // Crear nuevo contenedor de errores
    const contenedorErrores = document.createElement('div');
    contenedorErrores.id = 'errores-validacion';
    contenedorErrores.style.backgroundColor = '#f8d7da';
    contenedorErrores.style.color = '#721c24';
    contenedorErrores.style.padding = '15px';
    contenedorErrores.style.margin = '20px 0';
    contenedorErrores.style.borderRadius = '5px';
    contenedorErrores.style.border = '1px solid #f5c6cb';
    
    // Título
    const titulo = document.createElement('h3');
    titrito.style.marginTop = '0';
    titulo.textContent = 'Se encontraron los siguientes errores:';
    contenedorErrores.appendChild(titulo);
    
    // Lista de errores
    const listaErrores = document.createElement('ul');
    listaErrores.style.marginBottom = '0';
    
    errores.forEach(error => {
        const itemError = document.createElement('li');
        itemError.textContent = error;
        listaErrores.appendChild(itemError);
    });
    
    contenedorErrores.appendChild(listaErrores);
    
    // Insertar el contenedor de errores al principio del formulario
    const formulario = document.getElementById('formulario_usuario');
    if (formulario) {
        formulario.insertBefore(contenedorErrores, formulario.firstChild);
        
        // Hacer scroll hacia los errores
        contenedorErrores.scrollIntoView({ behavior: 'smooth' });
    }
}


function esModoCreacion() {
    const idHidden = document.getElementById('enviar_id');
    return !idHidden || !idHidden.value;
}


function validarCampoPorId(campoId, tipoValidacion, parametros = {}) {
    const campo = document.getElementById(campoId);
    if (!campo) return null;
    
    const valor = campo.value || '';
    
    switch(tipoValidacion) {
        case 'longitud':
            return validarLongitud(valor, parametros.min, parametros.max, parametros.nombre);
        case 'ci':
            return validarCI(valor);
        case 'email':
            return validarEmail(valor);
        case 'numero':
            return validarNumeroEntero(valor, parametros.nombre, parametros.min, parametros.max);
        case 'fecha':
            return validarFecha(valor, parametros.nombre);
        case 'select':
            return validarSelect(valor, parametros.nombre);
        default:
            return null;
    }
}