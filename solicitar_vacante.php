<?php
require 'includes/config.php';
require 'includes/auth.php';
require 'includes/funciones.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$db = (new Database())->getConnection();
$categorias = obtenerCategorias();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $empresa = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $salario = trim($_POST['salario']);
    $categoria_id = intval($_POST['categoria_id']);
    $contacto_email = trim($_POST['contacto_email']);
    $logo_empresa = null;

    if (isset($_FILES['logo_empresa']) && $_FILES['logo_empresa']['error'] === UPLOAD_ERR_OK) {
        $logo_empresa = subirArchivo($_FILES['logo_empresa'], UPLOAD_DIR_EMPRESAS);
    }

    $stmt = $db->prepare("INSERT INTO solicitudes_vacantes (usuario_id, titulo, descripcion, empresa, ubicacion, salario, categoria_id, contacto_email, logo_empresa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], $titulo, $descripcion, $empresa, $ubicacion, $salario, $categoria_id, $contacto_email, $logo_empresa
    ]);
    $success = true;
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2>Solicitar publicación de vacante</h2>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">¡Solicitud enviada! Será revisada por un administrador.</div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" class="form-control" name="titulo" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select class="form-select" name="categoria_id" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Empresa</label>
            <input type="text" class="form-control" name="empresa" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Ubicación</label>
            <input type="text" class="form-control" name="ubicacion" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Salario</label>
            <input type="text" class="form-control" name="salario">
        </div>
        <div class="mb-3">
            <label class="form-label">Email de contacto</label>
            <input type="email" class="form-control" name="contacto_email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Logo de la empresa</label>
            <input type="file" class="form-control" name="logo_empresa" accept="image/jpeg, image/png">
        </div>
        <button type="submit" class="btn btn-primary">Enviar solicitud</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>