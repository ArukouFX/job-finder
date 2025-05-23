<?php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/funciones.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Obtener ID de la vacante
$vacante_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener datos actuales de la vacante
$stmt = $db->prepare("SELECT * FROM vacantes WHERE id = ?");
$stmt->execute([$vacante_id]);
$vacante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vacante) {
    header('Location: vacantes.php');
    exit;
}

$categorias = obtenerCategorias();
$success = false;
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $empresa = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $salario = trim($_POST['salario']);
    $categoria_id = intval($_POST['categoria_id']);
    $contacto_email = trim($_POST['contacto_email']);
    $activa = isset($_POST['activa']) ? 1 : 0;

    // Manejar logo si se sube uno nuevo
    $logo_empresa = $vacante['logo_empresa'];
    if (isset($_FILES['logo_empresa']) && $_FILES['logo_empresa']['error'] === UPLOAD_ERR_OK) {
        $nuevo_logo = subirArchivo($_FILES['logo_empresa'], UPLOAD_DIR_EMPRESAS);
        if ($nuevo_logo) {
            $logo_empresa = $nuevo_logo;
        }
    }

    $stmt = $db->prepare("UPDATE vacantes SET titulo=?, descripcion=?, empresa=?, ubicacion=?, salario=?, categoria_id=?, contacto_email=?, logo_empresa=?, activa=? WHERE id=?");
    if ($stmt->execute([$titulo, $descripcion, $empresa, $ubicacion, $salario, $categoria_id, $contacto_email, $logo_empresa, $activa, $vacante_id])) {
        $success = true;
        // Refrescar datos
        $stmt = $db->prepare("SELECT * FROM vacantes WHERE id = ?");
        $stmt->execute([$vacante_id]);
        $vacante = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Error al actualizar la vacante.";
    }
}

include '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Editar Vacante</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">Vacante actualizada correctamente.</div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Título</label>
                <input type="text" class="form-control" name="titulo" required value="<?= htmlspecialchars($vacante['titulo']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Categoría</label>
                <select class="form-select" name="categoria_id" required>
                    <option value="">Seleccione una categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?= $categoria['id'] ?>" <?= $vacante['categoria_id'] == $categoria['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categoria['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="5" required><?= htmlspecialchars($vacante['descripcion']) ?></textarea>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Empresa</label>
                <input type="text" class="form-control" name="empresa" required value="<?= htmlspecialchars($vacante['empresa']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Ubicación</label>
                <input type="text" class="form-control" name="ubicacion" required value="<?= htmlspecialchars($vacante['ubicacion']) ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Salario</label>
                <input type="text" class="form-control" name="salario" value="<?= htmlspecialchars($vacante['salario']) ?>">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email de contacto</label>
                <input type="email" class="form-control" name="contacto_email" required value="<?= htmlspecialchars($vacante['contacto_email']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Logo de la empresa</label>
                <?php if ($vacante['logo_empresa']): ?>
                    <div class="mb-2">
                        <img src="../<?= UPLOAD_DIR_EMPRESAS . htmlspecialchars($vacante['logo_empresa']) ?>" alt="Logo" style="height:40px;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" name="logo_empresa" accept="image/jpeg, image/png">
            </div>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" name="activa" id="activa" <?= $vacante['activa'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="activa">Vacante activa (visible para usuarios)</label>
        </div>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="vacantes.php" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>