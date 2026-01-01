document.addEventListener('DOMContentLoaded', function(){

const ruta = obtenerRuta();


document.getElementById('b6').addEventListener('click', function(){
    if (confirm('¿Estás seguro de que deseas eliminar todos los registros?')) {
        fetch(ruta, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); 
            }
        })
        .catch(error => console.error('Error:', error));
    }
});


});


function obtenerRuta(){

let ruta;
const formulario = document.querySelector('form');


if (formulario.getAttribute('id')=='formulario_facultad'){
    ruta = '/vaciarFacultad';
}

else if (formulario.getAttribute('id')=='formulario_carrera'){
    ruta = '/vaciarCarrera';
}

else if (formulario.getAttribute('id')=='formulario_modalidad'){
    ruta = '/vaciarModalidad';
}



else if (formulario.getAttribute('id')=='formulario_tesis'){
    ruta = '/vaciarTesis';
}

else if (formulario.getAttribute('id')=='formulario_cortes'){
    ruta = '/vaciarCortes';
}


else if (formulario.getAttribute('id')=='formulario_no_conformidades'){
    ruta = '/vaciarNoConformidades';
}




else if (formulario.getAttribute('id')=='formulario_departamento'){
    ruta = '/vaciarDepartamento';
}

else if (formulario.getAttribute('id')=='formulario_fundamentación'){
    ruta = '/vaciarFundamentaciones';
}



else if (formulario.getAttribute('id')=='formulario_grupo'){
    ruta = '/vaciarGrupos';
}



else if (formulario.getAttribute('id')=='formulario_usuario'){
    ruta = '/vaciarUsuarios';
}

else if (formulario.getAttribute('id')=='formulario_rol'){
    ruta = '/vaciarRoles';
}

else if (formulario.getAttribute('id')=='formulario_permiso'){
    ruta = '/vaciarPermisos';
}



return ruta;

}