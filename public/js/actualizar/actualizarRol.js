document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

   if(!validarRol()){
    alert('Rol de usuario incorrecto');
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
    document.getElementById('formulario_rol').action = '/modificarRol';
    
    // Enviar formulario
    document.getElementById('formulario_rol').submit();
   
}


//Validaciones

function validarRol(){


rol = document.getElementById('rol').value;

if(rol.length==0 || rol.length>120 || rol.length<3){

return false;

}

return true;

}