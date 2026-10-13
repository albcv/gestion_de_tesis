<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2;url={{ route('login') }}">
    <title>Redirigiendo...</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f4f9ff;
            color: #2c3e50;
        }
        .box {
            text-align: center;
            padding: 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        h1 { font-size: 2rem; margin: 0 0 10px; }
        p { color: #6c757d; margin: 0 0 20px; }
        a {
            display: inline-block;
            padding: 10px 20px;
            background: #0d6efd;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>⏰ Sesión caducada</h1>
        <p>Redirigiendo al inicio de sesión...</p>
        <a href="{{ route('login') }}">Ir ahora</a>
    </div>
</body>
</html>