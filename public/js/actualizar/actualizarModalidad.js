document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

 if(!validarNombre()){
    alert('Nombre de modalidad incorrecto');
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
    
    id=filaSeleccionada.getAttribute('id');
    
    return id;
}

function enviarModificacion(){
    const id = obtenerIDFilaSeleccionada();
    if (!id) return;

    // Asignar el ID al campo oculto
    document.getElementById('enviar_id').value = id;
    
    // Cambiar acción del formulario a modificar
    document.getElementById('formulario_modalidad').action = '/modificarModalidad';
    
    // Enviar formulario
    document.getElementById('formulario_modalidad').submit();

}


//Validaciones

function validarNombre(){

nombre = document.getElementById('nombre_modalidad').value;

if (nombre.length==0 || nombre.length>50 || nombre.length<10 || !isNaN(nombre)){
    return false;
}

return true;

}


