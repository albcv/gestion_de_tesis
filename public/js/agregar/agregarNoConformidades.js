document.addEventListener('DOMContentLoaded', function(){


agregar = document.querySelector('.agregar');


agregar.setAttribute('form', 'formulario_no_conformidades');

document.getElementById('formulario_no_conformidades').addEventListener('submit', function(event){



if(!validarDeficienciasDetectadas()){
    alert('Introduzca las deficiencias detectadas');
    event.preventDefault();
    return;
}


});




});



function validarDeficienciasDetectadas(){


deficiencia = document.getElementById('deficiencias_detectadas').value;

if(deficiencia.length==0){

return false;

}

return true;

}