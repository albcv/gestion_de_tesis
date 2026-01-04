<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CambiarContraseñaController extends Controller
{
    /**
     * Muestra el formulario para cambiar contraseña
     */
    public function mostrarFormulario()
    {
        return view('cambiarContraseña');
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function cambiarContraseña(Request $request)
    {
        try {
            // Validar los datos
            $validator = Validator::make($request->all(), [
                'contrasena_actual' => [
                    'required',
                    'string',
                    'min:6',
                    function ($attribute, $value, $fail) {
                
                        $userPassword = DB::table('users')
                            ->where('id', Auth::id())
                            ->value('password');
                        
                        if (!$userPassword || !Hash::check($value, $userPassword)) {
                            $fail('La contraseña actual es incorrecta.');
                        }
                    }
                ],
                'nueva_contrasena' => [
                    'required',
                    'string',
                    'min:6',
                    'max:255',
                    'confirmed',
                    'different:contrasena_actual'
                ],
                'nueva_contrasena_confirmation' => [
                    'required',
                    'string'
                ]
            ], [
                'contrasena_actual.required' => 'La contraseña actual es obligatoria',
                'nueva_contrasena.required' => 'La nueva contraseña es obligatoria',
                'nueva_contrasena.min' => 'La nueva contraseña debe tener al menos 6 caracteres',
                'nueva_contrasena.max' => 'La nueva contraseña no puede exceder 255 caracteres',
                'nueva_contrasena.confirmed' => 'La confirmación de contraseña no coincide',
                'nueva_contrasena.different' => 'La nueva contraseña debe ser diferente a la actual',
                'nueva_contrasena_confirmation.required' => 'La confirmación de contraseña es obligatoria'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $updated = DB::table('users')
                ->where('id', Auth::id())
                ->update([
                    'password' => Hash::make($request->nueva_contrasena),
                    'updated_at' => now()
                ]);

            if (!$updated) {
                throw new \Exception('No se pudo actualizar la contraseña');
            }

            return redirect()->route('perfil')
                ->with('success', '¡Contraseña cambiada exitosamente!');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput($request->except('contrasena_actual', 'nueva_contrasena', 'nueva_contrasena_confirmation'));
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ocurrió un error al cambiar la contraseña. Por favor, inténtelo de nuevo.');
        }
    }
}