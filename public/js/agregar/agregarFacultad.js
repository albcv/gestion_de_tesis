
document.addEventListener('DOMContentLoaded', function(){


agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_facultad');

document.getElementById('formulario_facultad').addEventListener('submit', function(event){

if(!validarFacultad()){
    alert('Facultad incorrecta');
    event.preventDefault();
    return;
}

if(!validarSiglas()){
    alert('Siglas incorrectas');
    event.preventDefault();
    return;
}



});




});


function validarFacultad(){

nombre = document.getElementById('nombre_facultad').value;

if (nombre.length==0 || nombre.length>100 || nombre.length<20 || !isNaN(nombre)){
    return false;
}

nombre = nombre.toLowerCase();

if(!nombre.includes('facultad')){
    return false;
}

return true;

}


function validarSiglas(){

siglas = document.getElementById('siglas').value;

if(siglas.length==0 || siglas.length>10 || siglas.length<3){
    return false;
}

if(!(siglas===siglas.toUpperCase())){
    return false;
}

return true;

}
