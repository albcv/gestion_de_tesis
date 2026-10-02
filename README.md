
<img src="img/UNICA_logo.png" alt="Formulario de login" >

# Sistema de Gestión de Trabajos de Diploma - UNICA

Sistema informático para la automatización de la gestión de los trabajos de diploma en la Universidad de Ciego de Ávila "Máximo Gómez Báez".  
Reemplaza el almacenamiento manuscrito tradicional por una plataforma digital que permite el acceso rápido, evaluación eficiente y seguimiento de las tesis de los estudiantes.

## 📝 Descripción

La mayoría de los datos relacionados con los trabajos de diploma se almacenaban en documentos manuscritos, lo que dificultaba el acceso rápido y la evaluación de las tesis. Este sistema web resuelve esa problemática al centralizar la información y ofrecer roles diferenciados para administradores, estudiantes y profesores.

El desarrollo siguió la metodología ágil **Extreme Programming (XP)**, permitiendo adaptarse a requisitos cambiantes y entregar valor de forma incremental.

## ✨ Características principales

### Módulo de Administrador
- Gestión de usuarios, roles y permisos.
- Gestión de facultades, carreras y modalidades de estudio.
- Asignación de tutor-estudiante.
- Gestión de fundamentaciones, revisión de fundamentaciones por profesor y recomendaciones.
- Gestión de cortes de evaluación, no conformidades y asignación de profesores a cortes.

### Módulo de Estudiante
- Creación y edición de fundamentaciones.
- Subida y gestión de cortes de tesis.

### Módulo de Profesor
- Revisión y retroalimentación de fundamentaciones.
- Evaluación de cortes presentados por estudiantes.

## 🛠️ Tecnologías utilizadas

| Capa          | Tecnologías                                      |
|---------------|--------------------------------------------------|
| Backend       | PHP 8, Laravel 12                                |
| Frontend      | HTML5, CSS3, JavaScript                          |
| Base de datos | MySQL 15.1                                       |
| Herramientas  | Visual Studio Code, DBDesigner (diagramas ER)    |


## Imágenes

**Login**  
<img src="img/login.png" alt="Formulario de login" >

**Registro**  
<img src="img/registro.png" alt="Formulario de registro" >

**Inicio**  
<img src="img/inicio.png" alt="Vista de inicio">

**Estadísticas**  
<img src="img/estadísticas.png" alt="Vista de inicio">

**Navegación**  
<img src="img/sidebar.png" alt="Navegación">

**Perfil de usuario**  
<img src="img/perfil.png" alt="Vista de perfil de usuario">

**Gestionar usuarios**  
<img src="img/gestionar_usuarios.png" alt="Vista de gestión de usuarios">

**Gestionar facultad**  
<img src="img/gestionar_facultad.png" alt="Vista de gestión de facultades">

**Gestionar carreras**  
<img src="img/gestionar_carreras.png" alt="Vista de gestión de carreras">

**Gestionar Tesis**  
<img src="img/gestionar_tesis.png" alt="Vista de gestión de tesis">

**Detalles de una Tesis**  
<img src="img/detalles_tesis.png" alt="Vista de detalles de una tesis">

**Gestionar Fechas de Entrega**  
<img src="img/fechas_entrega.png" alt="Vista de gestión de fechas de entregas">

## Rol de Estudiante
<img src="img/estudiante/inicio_estudiante.jpg" alt="Vista de inicio (rol estudiante)">

<img src="img/estudiante/menu_estudiante.jpg" alt="Navegación (rol estudiante)">

<img src="img/estudiante/subir_fundamentacion.jpg" alt="Subir fundamentación (rol estudiante)">


## Rol de Profesor
<img src="img/profesor/inicio_profesor.jpg" alt="Vista de inicio (rol profesor)">

<img src="img/profesor/menu_profesor.jpg" alt="Navegación (rol profesor)">

<img src="img/profesor/estudiantes_tutorados.jpg" alt="Estudiantes Tutorados (rol profesor)">


## Modelo lógico 
<img src="img/modelo_lógico.png" alt="Diagrama del modelo lógico de la aplicación">

