//actualizarFacultad.js
document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

    if(!validarFacultad()){
        alert('Facultad incorrecta');
        return;
}

    if(!validarSiglas()){
        alert('Siglas incorrectas');
        return;
}

        enviarModificacion();
    });
});

function obtenerIDFilaSeleccionada(){
    let filaSeleccionada = document.querySelector('tr.seleccionado');
    
    if (!filaSeleccionada) {
        alert('Selecciona una fila para modificar');
        return null;
    }
    
    const id=filaSeleccionada.getAttribute('id');
    
    return id;
}

function enviarModificacion(){
    const id = obtenerIDFilaSeleccionada();
    if (!id) return;

    // Asignar el ID al campo oculto
    document.getElementById('enviar_id').value = id;
    
    // Cambiar acción del formulario a modificar
    document.getElementById('formulario_facultad').action = '/modificarFacultad';
    
    // Enviar formulario
    document.getElementById('formulario_facultad').submit();

}


//Validaciones

function validarFacultad(){

let nombre = document.getElementById('nombre_facultad').value;

if (nombre.length==0 || nombre.length>100 || nombre.length<20 || !isNaN(nombre)){
    return false;
}

nombre = nombre.toLowerCase();

if(!nombre.includes('facultad')){
    return false;
}

return true;

}


function validarSiglas(){

const siglas = document.getElementById('siglas').value;

if(siglas.length==0 || siglas.length>10 || siglas.length<3){
    return false;
}

if(!(siglas===siglas.toUpperCase())){
    return false;
}

return true;

}
