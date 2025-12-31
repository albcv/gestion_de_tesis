document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

   if(!validarPermiso()){
    alert('Permiso no válido');
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
    document.getElementById('formulario_permiso').action = '/modificarPermiso';
    
    // Enviar formulario
    document.getElementById('formulario_permiso').submit();
   
}





//Validaciones

function validarPermiso(){


permiso = document.getElementById('permiso').value;

if(permiso.length==0 || permiso.length>150 || permiso.length<3){

return false;

}

return true;

}