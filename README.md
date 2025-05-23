# JobFinder

**JobFinder** es una aplicación web desarrollada en PHP y MySQL que permite la gestión de ofertas de empleo, postulaciones y administración de usuarios. Está pensada tanto para administradores como para usuarios que buscan empleo.  
Puedes ver una demostración en este video:  
[![Demo JobFinder](https://img.youtube.com/vi/g-zzQGo7UW4/0.jpg)](https://www.youtube.com/watch?v=g-zzQGo7UW4)

---

## Características principales

- Registro y login de usuarios (con roles: usuario y administrador)
- Publicación y gestión de vacantes por parte de administradores
- Solicitud de publicación de vacantes por usuarios (requiere aprobación)
- Postulación de usuarios a vacantes activas
- Panel de administración con estadísticas, gestión de usuarios, vacantes y categorías
- Subida de fotos de perfil y logos de empresa
- Filtros y búsqueda de vacantes por categoría y palabras clave
- Visualización de postulantes por vacante (solo admin)
- Seguridad básica (hash de contraseñas, validaciones, control de acceso)

---

## Requisitos

- PHP 7.4 o superior
- MySQL/MariaDB
- Servidor web (Apache recomendado)
- Composer (opcional, para dependencias externas)
- Extensión PDO habilitada en PHP

---

## Instalación

1. **Clona o descarga este repositorio en tu servidor local (ej: XAMPP, Laragon, WAMP, etc):**
    ```
    git clone https://github.com/tuusuario/jobfinder.git
    ```

2. **Crea la base de datos:**
    - Crea una base de datos llamada `ofertas_empleo` en tu servidor MySQL.
    - Importa el archivo SQL con la estructura y datos iniciales (debes crearlo si no está incluido).

3. **Configura la conexión a la base de datos:**
    - Edita el archivo `includes/config.php` si necesitas cambiar usuario, contraseña o nombre de la base de datos.

4. **Configura permisos de carpetas:**
    - Asegúrate de que las carpetas `uploads/fotos_usuarios/` y `uploads/logos_empresas/` tengan permisos de escritura.

5. **Accede a la aplicación:**
    - Abre tu navegador y entra a `http://localhost/ofertas-empleo/`

---

## Estructura de carpetas

- `/admin` — Panel de administración (vacantes, usuarios, categorías, solicitudes)
- `/includes` — Archivos de configuración, conexión, funciones y plantillas comunes
- `/uploads` — Carpeta para fotos de usuarios y logos de empresas
- Archivos principales: `index.php`, `login.php`, `registro.php`, `vacantes.php`, `perfil.php`, etc.

---

## Usuarios y roles

- **Usuario:** Puede buscar vacantes, postularse y solicitar publicación de nuevas vacantes.
- **Administrador:** Gestiona vacantes, categorías, usuarios y aprueba/rechaza solicitudes de publicación.

---

## Funcionalidades destacadas

- **Registro/Login:**  
  Los usuarios pueden registrarse con nombre, email, contraseña, foto y LinkedIn. El login distingue entre usuario y admin.

- **Vacantes:**  
  Los usuarios pueden buscar y filtrar vacantes. Los administradores pueden crear, editar y eliminar vacantes.

- **Postulaciones:**  
  Los usuarios pueden postularse a vacantes activas. Los administradores pueden ver los postulantes de cada vacante.

- **Solicitudes de vacantes:**  
  Los usuarios pueden solicitar la publicación de una vacante, que debe ser aprobada por un administrador.

- **Panel de administración:**  
  Estadísticas, gestión de usuarios, vacantes, categorías y solicitudes.

---

## Personalización

- Puedes modificar los estilos en los archivos CSS o directamente en las plantillas.
- Cambia los textos y logos en los archivos de plantilla (`header.php`, `footer.php`).

---

## Seguridad

- Las contraseñas se almacenan con hash seguro.
- Control de acceso por roles en todas las páginas sensibles.
- Validación de archivos subidos (tipo y tamaño).

---

## Créditos

Desarrollado por [Tu Nombre o Equipo].  
Demo en YouTube: [https://www.youtube.com/watch?v=g-zzQGo7UW4](https://www.youtube.com/watch?v=g-zzQGo7UW4)

---

## Licencia

Este proyecto es de uso educativo y puede ser adaptado libremente.