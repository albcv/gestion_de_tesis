document.addEventListener('DOMContentLoaded', function(){

document.getElementById('formRegistrarAdmin').addEventListener('submit', function(event){


if(!validarEmail()){
    alert('Email incorrecto');
    event.preventDefault();
    return;
}

if(!validarPassword()){
    alert('Contraseña no válida');
    event.preventDefault();
    return;
}


if(!validarNombre()){
    alert('Nombre no válido');
    event.preventDefault();
    return;
}


});


});


function validarEmail(){

const email = document.getElementById('email');
const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;


if(email.value.length ==0){
    return false;
}


if (!emailPattern.test(email.value)) {
                return false
            }

return true;


}


function validarPassword(){

const p = document.getElementById('password').value;


if(p.length < 6){
    return false;
}

return true;


}


function validarNombre(){

const nombre = document.getElementById('name').value;


if(nombre.length < 3){
    return false;
}

return true;


}













