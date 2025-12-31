document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

   if(!validarDeficienciasDetectadas()){
    alert('Introduzca las deficiencias detectadas');
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
    document.getElementById('formulario_no_conformidades').action = '/modificarNoConformidades';
    
    // Enviar formulario
    document.getElementById('formulario_no_conformidades').submit();
   
}


//Validaciones

function validarDeficienciasDetectadas(){


deficiencia = document.getElementById('deficiencias_detectadas').value;

if(deficiencia.length==0){

return false;

}

return true;

}