document.addEventListener('DOMContentLoaded', function(){


document.getElementById('b3').addEventListener('click', function(){


obtenerValoresFilaSeleccionada();


});




});

function obtenerValoresFilaSeleccionada(){

    const filaSeleccionada = document.querySelector('tr.seleccionado');
    let campos =  document.querySelectorAll('.atributo');
    

    if (!filaSeleccionada) {
        alert('Selecciona la fila');
        return;
    }

    document.getElementById('b1').style.display='none';
    document.getElementById('b2').style.display='none';
    document.getElementById('b3').style.display='none';
    document.getElementById('b6').style.display='none';
    document.getElementById('b5').style.display='block';


    const celdas = filaSeleccionada.cells;
    

    for (let i = 0; i < celdas.length; i++) {

    campos[i].value =  celdas[i].textContent;

    
    }


}



 






