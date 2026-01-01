document.addEventListener('DOMContentLoaded', function(){


document.querySelectorAll('tr').forEach(fila=>{


fila.addEventListener('click', function(){


document.querySelectorAll('tr').forEach(f=>f.classList.remove('seleccionado'));
this.classList.add('seleccionado');


});



});




});