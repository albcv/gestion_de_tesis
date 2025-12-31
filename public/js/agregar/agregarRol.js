document.addEventListener('DOMContentLoaded', function(){


agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_rol');

document.getElementById('formulario_rol').addEventListener('submit', function(event){



  if(!validarRol()){
    alert('Rol de usuario incorrecto');
    event.preventDefault();
    return;
}



});




});



function validarRol(){


rol = document.getElementById('rol').value;

if(rol.length==0 || rol.length>120 || rol.length<3){

return false;

}

return true;

}