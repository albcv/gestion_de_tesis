document.addEventListener('DOMContentLoaded', function(){


const agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_modalidad');

document.getElementById('formulario_modalidad').addEventListener('submit', function(event){

if(!validarNombre()){
    alert('Nombre de modalidad incorrecto');
    event.preventDefault();
    return;
}








});




});


function validarNombre(){

const nombre = document.getElementById('nombre_modalidad').value;

if (nombre.length==0 || nombre.length>50 || nombre.length<10 || !isNaN(nombre)){
    return false;
}

return true;

}


