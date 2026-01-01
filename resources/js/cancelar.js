document.addEventListener('DOMContentLoaded', function(){

const cancelar = document.getElementById('b4');

cancelar.addEventListener('click', function(){

vaciarCampos();
eliminarSelección();
mostrarBotonesIniciales();


});



});


function vaciarCampos(){


const elementos = document.querySelectorAll('input, select');

for (let i=0;i<elementos.length;i++){

const elemento = elementos[i];

elemento.value = '';

}



}

function eliminarSelección(){

document.querySelectorAll('tr').forEach(f=>f.classList.remove('seleccionado'));

}


function mostrarBotonesIniciales(){

document.getElementById('b5').style.display='none';
document.getElementById('b1').style.display='inline-block';
document.getElementById('b2').style.display='inline-block';
document.getElementById('b3').style.display='inline-block';
document.getElementById('b6').style.display='inline-block';


}