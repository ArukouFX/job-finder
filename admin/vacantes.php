<?php
require '../includes/config.php';
require '../includes/auth.php';
require '../includes/funciones.php'; // Asegúrate de que la ruta sea correcta desde admin/

$auth = new Auth();

// Control de acceso de administrador
if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Procesar formulario para nueva vacante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_vacante'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $empresa = trim($_POST['empresa']);
    $ubicacion = trim($_POST['ubicacion']);
    $salario = trim($_POST['salario']);
    $categoria_id = intval($_POST['categoria_id']);
    $contacto_email = trim($_POST['contacto_email']);
    $activa = isset($_POST['activa']) ? 1 : 0;
    
    $logo_empresa = null;
    if (isset($_FILES['logo_empresa']) && $_FILES['logo_empresa']['error'] === UPLOAD_ERR_OK) {
        $logo_empresa = subirArchivo($_FILES['logo_empresa'], UPLOAD_DIR_EMPRESAS);
    }
    
    $stmt = $db->prepare("INSERT INTO vacantes (titulo, descripcion, empresa, ubicacion, salario, categoria_id, contacto_email, logo_empresa, activa, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$titulo, $descripcion, $empresa, $ubicacion, $salario, $categoria_id, $contacto_email, $logo_empresa, $activa, $_SESSION['user_id']]);
    
    header('Location: vacantes.php?success=1');
    exit;
}

// Procesar eliminación de vacante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_vacante_id'])) {
    $vacante_id = intval($_POST['eliminar_vacante_id']);
    // Eliminar postulaciones asociadas primero
    $stmt = $db->prepare("DELETE FROM postulaciones WHERE vacante_id = ?");
    $stmt->execute([$vacante_id]);
    // Ahora eliminar la vacante
    $stmt = $db->prepare("DELETE FROM vacantes WHERE id = ?");
    $stmt->execute([$vacante_id]);
    header('Location: vacantes.php?eliminada=1');
    exit;
}

// Obtener todas las vacantes
$stmt = $db->query("SELECT v.*, c.nombre as categoria_nombre FROM vacantes v JOIN categorias c ON v.categoria_id = c.id ORDER BY v.fecha_publicacion DESC");
$vacantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el select
$categorias = obtenerCategorias();

include '../includes/header.php';
?>

<div class="container mt-4">
    <h2>Gestión de Vacantes</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Vacante creada exitosamente!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['eliminada'])): ?>
        <div class="alert alert-success">Vacante eliminada correctamente.</div>
    <?php endif; ?>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Nueva Vacante</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" class="form-control" name="titulo" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?= $categoria['id'] ?>"><?= htmlspecialchars($categoria['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="5" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" class="form-control" name="empresa" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Ubicación</label>
                        <input type="text" class="form-control" name="ubicacion" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Salario</label>
                        <input type="text" class="form-control" name="salario">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email de contacto</label>
                        <input type="email" class="form-control" name="contacto_email" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Logo de la empresa</label>
                        <input type="file" class="form-control" name="logo_empresa" accept="image/jpeg, image/png">
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="activa" id="activa" checked>
                    <label class="form-check-label" for="activa">Vacante activa</label>
                </div>
                
                <button type="submit" name="crear_vacante" class="btn btn-primary">Publicar Vacante</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h5>Vacantes Publicadas</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacantes as $vacante): ?>
                            <tr>
                                <td><?= htmlspecialchars($vacante['titulo']) ?></td>
                                <td><?= htmlspecialchars($vacante['categoria_nombre']) ?></td>
                                <td><?= htmlspecialchars($vacante['empresa']) ?></td>
                                <td><?= htmlspecialchars($vacante['ubicacion']) ?></td>
                                <td>
                                    <span class="badge <?= $vacante['activa'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $vacante['activa'] ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_vacante.php?id=<?= $vacante['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                    <a href="solicitantes.php?id=<?= $vacante['id'] ?>" class="btn btn-sm btn-info">Ver Solicitantes</a>
                                    <form method="POST" action="" style="display:inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar esta vacante?');">
                                        <input type="hidden" name="eliminar_vacante_id" value="<?= $vacante['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>