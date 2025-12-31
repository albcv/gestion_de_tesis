document.addEventListener('DOMContentLoaded', function() {
    // Función para cargar los permisos de un rol
    async function cargarPermisosRol(rolId) {
        try {
            const response = await fetch(`/obtenerPermisosRol/${rolId}`);
            const data = await response.json();
            
            if (data.success) {
                // Desmarcar todos los checkboxes primero
                document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });
                
                // Marcar los checkboxes correspondientes a los permisos del rol
                data.permisos.forEach(permisoId => {
                    const checkbox = document.getElementById(`permiso_${permisoId}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }
        } catch (error) {
            console.error('Error al cargar permisos:', error);
        }
    }
    
    // Selección de fila en la tabla
    const filas = document.querySelectorAll('table tbody tr');
    let filaSeleccionada = null;
    
    filas.forEach(fila => {
        fila.addEventListener('click', function(e) {
            // Remover selección anterior
            if (filaSeleccionada) {
                filaSeleccionada.classList.remove('selected');
            }
            
            // Marcar nueva fila como seleccionada
            this.classList.add('selected');
            filaSeleccionada = this;
            
            // Actualizar el campo oculto de ID para eliminar
            document.getElementById('inputIdEliminar').value = this.id;
        });
    });
    
    // Botón Modificar
    const botonModificar = document.getElementById('b3');
    if (botonModificar) {
        botonModificar.addEventListener('click', function() {
            if (filaSeleccionada) {
                const id = filaSeleccionada.id;
                const rol = filaSeleccionada.cells[0].innerText;
                
                // Rellenar formulario
                document.getElementById('enviar_id').value = id;
                document.getElementById('rol').value = rol;
                
                // Cargar permisos del rol
                cargarPermisosRol(id);
            } else {
                alert('Por favor, seleccione un rol de la tabla');
            }
        });
    }
    
    // Botón Cancelar (vaciar formulario)
    const botonCancelar = document.getElementById('b4');
    if (botonCancelar) {
        botonCancelar.addEventListener('click', function() {
            document.getElementById('formulario_rol').reset();
            document.getElementById('enviar_id').value = '';
            
            // Desmarcar todos los checkboxes
            document.querySelectorAll('.permiso-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Deseleccionar fila
            if (filaSeleccionada) {
                filaSeleccionada.classList.remove('selected');
                filaSeleccionada = null;
            }
        });
    }
});