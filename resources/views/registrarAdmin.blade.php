<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Primer Administrador</title>
    @vite(['resources/css/registrar-admin.css'])
</head>
<body>
    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <h1 class="card-title">Registro del Primer Administrador</h1>
            <p class="card-subtitle">Complete el formulario para crear la cuenta de administrador del sistema</p>
            
            <form method="POST" action="{{ route('registrarAdmin.post') }}" class="register-form">
                @csrf
                
                <div class="form-group">
                    <label for="name">Nombre de Usuario *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <input type="password" id="password" name="password" required>
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Contraseña *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-primary">Registrar Administrador</button>
            </form>
            
            <div class="card-footer">
                <p><small>* Campos obligatorios</small></p>
            </div>
        </div>
    </div>
</body>
</html>