document.addEventListener('DOMContentLoaded', function() {
    // Seleccionar todos los botones de eliminar por la clase 'btn_eliminar'
    const botonesEliminar = document.querySelectorAll('.btn_eliminar');

    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function() {

            if(confirm('¿Está seguro de que desea eliminar este usuario?')){

            // Obtener la fila (tr) más cercana que contenga el id
            const fila = this.closest('tr');
            const id = fila.id; // Obtener el id del tr

            // Asignar el id al campo oculto del formulario
            document.getElementById('inputIdEliminar').value = id;

            // Enviar el formulario
            document.getElementById('formEliminar').submit();

            }

            
        });
    });
});