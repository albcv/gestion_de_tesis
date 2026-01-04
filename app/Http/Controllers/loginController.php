<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    
    public function login(Request $request)
    {
        try {
            // Validar los datos de entrada
            $validatedData = $request->validate([
                'email' => [
                    'required',
                    'max:255',
                    'string'
                ],
                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'max:255'
                ]
            ], [
                'email.required' => 'El correo electrónico es obligatorio',
                'email.email' => 'El correo electrónico debe ser válido',
                'email.max' => 'El correo electrónico no puede exceder los 255 caracteres',
                'password.required' => 'La contraseña es obligatoria',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres',
                'password.max' => 'La contraseña no puede exceder los 255 caracteres',
            ]);

            // Intentar autenticación
            $credentials = [
                'email' => $validatedData['email'],
                'password' => $validatedData['password']
            ];

            if (Auth::attempt($credentials)) {
                $request->session()->regenerate();
                return redirect()->intended(route('inicio'));
            }

            // Si la autenticación falla, lanzar excepción de validación
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas',
            ]);

        } catch (ValidationException $e) {
            // Relanzar excepción de validación para que Laravel la maneje
            throw $e;
            
        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en login: ' . $e->getMessage(), [
                'email' => $request->email ?? 'no-provided',
                'ip' => $request->ip(),
                'user-agent' => $request->userAgent()
            ]);
            
            // Redirigir con mensaje de error genérico
            return redirect('login')->withErrors([
                'general' => 'Ocurrió un error inesperado. Por favor, inténtelo de nuevo.'
            ])->withInput($request->except('password'));
        }
    }

    /**
     * Maneja el proceso de cierre de sesión
     */
    public function logout(Request $request)
    {
        try {
            // Verificar que el usuario esté autenticado antes de cerrar sesión
            if (!Auth::check()) {
                return redirect(route('login'));
            }

            // Obtener información del usuario para logs
            $user = Auth::user();
            $userId = $user ? $user->id : null;
            
            // Realizar logout
            Auth::logout();

            // Invalidar y regenerar tokens de sesión
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Log de la acción
            Log::info('Usuario cerró sesión', [
                'user_id' => $userId,
                'ip' => $request->ip(),
                'user-agent' => $request->userAgent()
            ]);

            return redirect(route('login'))->with('status', 'Sesión cerrada correctamente');

        } catch (\Exception $e) {
            // Log del error
            Log::error('Error en logout: ' . $e->getMessage(), [
                'user_id' => Auth::id() ?? 'no-authenticated',
                'ip' => $request->ip(),
                'user-agent' => $request->userAgent()
            ]);
            
            // Redirigir con mensaje de error
            return redirect()->back()->withErrors([
                'general' => 'Ocurrió un error al cerrar sesión. Por favor, inténtelo de nuevo.'
            ]);
        }
    }
}