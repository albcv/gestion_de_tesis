document.addEventListener('DOMContentLoaded', function(){

document.getElementById('b2').addEventListener('click', function(){

if (confirm('¿Está seguro de que desea eliminar esta fila?')){

    enviarID();

}


});

});

function obtenerIDFilaSeleccionada(){
    let filaSeleccionada = document.querySelector('tr.seleccionado');
    
    if (!filaSeleccionada) {
        alert('Selecciona la fila');
        return null;
    }

    const id = filaSeleccionada.getAttribute('id');

    return id;
}


function enviarID() {
    const id = obtenerIDFilaSeleccionada();
    if (!id) return;

    // Asignar el id al campo oculto
    document.getElementById('inputIdEliminar').value = id;

    // Enviar el formulario
    document.getElementById('formEliminar').submit();

}