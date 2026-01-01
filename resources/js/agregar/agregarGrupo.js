document.addEventListener('DOMContentLoaded', function(){


const agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_grupo');

document.getElementById('formulario_grupo').addEventListener('submit', function(event){

if(!validarNúmero()){
    alert('Número de grupo no válido');
    event.preventDefault();
    return;
}






});




});


function validarNúmero(){

const numero = document.getElementById('número').value;

if(isNaN(numero) || numero.length==0 || parseInt(numero)<=0 || parseInt(numero)>999){
    return false;
}

return true;


}






