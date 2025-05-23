<?php
require 'config.php';

function subirArchivo($file, $directory) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Validar tipo y tamaño
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_TYPES)) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    // Crear directorio si no existe
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid() . '.' . $extension;
    $rutaDestino = $directory . $nombreArchivo;

    if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        return $nombreArchivo;
    }

    return false;
}

function obtenerCategorias() {
    $db = (new Database())->getConnection();
    $stmt = $db->query("SELECT id, nombre FROM categorias ORDER BY nombre");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerVacantes($categoria_id = null, $soloActivas = true) {
    $db = (new Database())->getConnection();
    
    $sql = "SELECT v.*, c.nombre as categoria_nombre 
            FROM vacantes v 
            JOIN categorias c ON v.categoria_id = c.id";
    
    $where = [];
    $params = [];
    
    if ($soloActivas) {
        $where[] = "v.activa = 1";
    }
    
    if ($categoria_id) {
        $where[] = "v.categoria_id = :categoria_id";
        $params[':categoria_id'] = $categoria_id;
    }
    
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY v.fecha_publicacion DESC";
    
    $stmt = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>