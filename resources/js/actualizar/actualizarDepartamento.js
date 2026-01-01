document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('b5').addEventListener('click', function(){

if(!validarNombre()){
    alert('Nombre no válido');
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
    document.getElementById('formulario_departamento').action = '/modificarDepartamento';
    
    // Enviar formulario
    document.getElementById('formulario_departamento').submit();

}


//Validaciones

function validarNombre(){

const nombre = document.getElementById('departamento').value;

if (nombre.length==0 || nombre.length>100 || nombre.length<10 || !isNaN(nombre)){
    return false;
}


return true;

}