import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [

                //css generales
                'resources/css/app.css',
                'resources/css/inicio.css',
                'resources/css/login.css',
                'resources/css/perfil.css',
                'resources/css/sidebar.css',
                'resources/css/stats.css',
              
                

                //css profesor
                'resources/css/profesor/listado.css',
                'resources/css/profesor/revisar.css',


                //css estudiante
                'resources/css/estudiante/subirCorte.css',
                'resources/css/estudiante/subirFundamentación.css',

                //css consultas
                  'resources/css/consultas/consultas.css',
                  'resources/css/consultas/ejecutar_consultas.css',
                

                //css gestionar
                'resources/css/gestionar/fechaEntrega.css',
               
                //css gestionar facultad
                 'resources/css/gestionar/facultad/index.css',
                 'resources/css/gestionar/facultad/formulario.css',

                //css gestionar usuarios
                'resources/css/gestionar/usuario/detalles.css',
                'resources/css/gestionar/usuario/filtros.css',
                
                // === validaciones  ===
                'resources/js/validaciones/validarLogin.js',
        
                // Registrar admin
                'resources/css/registrar-admin.css',

                'resources/css/cambiarContraseña.css'
            
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});