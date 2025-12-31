document.addEventListener('DOMContentLoaded', function(){


agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_departamento');

document.getElementById('formulario_departamento').addEventListener('submit', function(event){

if(!validarNombre()){
    alert('Nombre no válido');
    event.preventDefault();
    return;
}




});




});


function validarNombre(){

nombre = document.getElementById('departamento').value;

if (nombre.length==0 || nombre.length>100 || nombre.length<10 || !isNaN(nombre)){
    return false;
}


return true;

}



