document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('form_login');
    
    form.addEventListener('submit', function(event){
        // Prevenir el envío inmediatamente
        event.preventDefault();
                
        if(!validarEmail()){
            alert('Email incorrecto');
            return;
        }

        if(!validarPassword()){
            alert('Contraseña no válida');
            return;
        }
        
        // Si pasa la validación, enviar el formulario
        this.submit();
    });
});

function validarEmail(){
    const email = document.getElementById('email');
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if(!email || email.value.length === 0){
        return false;
    }

    if (!emailPattern.test(email.value)) {
        return false;
    }

    return true;
}

function validarPassword(){
    const passwordInput = document.getElementById('password');
    
    if(!passwordInput){
        return false;
    }
    
    const p = passwordInput.value;
    return p.length >= 6;
}