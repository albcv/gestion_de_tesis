@echo off

cd /d "%~dp0"

start /B php -S localhost:8000 -t public

:waitForServer
powershell -Command "Try { Invoke-WebRequest -Uri 'http://localhost:8000' -Method Head -TimeoutSec 1 } Catch { exit 1 }"
if %errorlevel% neq 0 (
    REM Si no está disponible, espera 1 segundo y vuelve a comprobar
    timeout /t 1 >nul
    goto waitForServer
)

start http://localhost:8000