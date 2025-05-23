<?php
// Configuración básica
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'ofertas_empleo');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/ofertas-empleo');

// Configuración de uploads
if (!defined('UPLOAD_DIR_USUARIOS')) define('UPLOAD_DIR_USUARIOS', 'uploads/fotos_usuarios/');
if (!defined('UPLOAD_DIR_EMPRESAS')) define('UPLOAD_DIR_EMPRESAS', 'uploads/logos_empresas/');
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
if (!defined('ALLOWED_TYPES')) define('ALLOWED_TYPES', ['image/jpeg', 'image/png']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>