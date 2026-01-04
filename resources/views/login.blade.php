@if($errors->any())
    <script>
        alert("{{ $errors->first() }}");
    </script>
@endif



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    @vite(['resources/css/formulario.css'])
    @vite(['resources/css/login.css'])
    @vite(['resources/js/validaciones/validarLogin.js'])

</head>
<body>

    <h1>Inicio de sesión</h1>

    <form action="{{route('inicioSesion')}}" method="post" id="form_login">

    @csrf

<div class="campo">
<label for="Email">Email</label>
<input type="email" id="email" name="email" required>
</div>

<div class="campo" id="campo_password">
<label for="password">Password</label>
<input type="password" id="password" name="password" required>
</div>

<input type="submit" id="btn_login" class="btn" value="Aceptar">

    </form>
    
</body>
</html>