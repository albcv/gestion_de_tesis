document.addEventListener('DOMContentLoaded', function(){


const agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_permiso');

document.getElementById('formulario_permiso').addEventListener('submit', function(event){



  if(!validarPermiso()){
    alert('Permiso no válido');
    event.preventDefault();
    return;
}



});




});



function validarPermiso(){


const permiso = document.getElementById('permiso').value;

if(permiso.length==0 || permiso.length>150 || permiso.length<3){

return false;

}

return true;

}