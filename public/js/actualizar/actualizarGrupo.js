document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

if(!validarNúmero()){
    alert('Número de grupo no válido');
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
    document.getElementById('formulario_grupo').action = '/modificarGrupo';
    
    // Enviar formulario
    document.getElementById('formulario_grupo').submit();
   
}


//Validaciones

function validarNúmero(){

numero = document.getElementById('número').value;

if(isNaN(numero) || numero.length==0 || parseInt(numero)<=0 || parseInt(numero)>999){
    return false;
}

return true;


}



