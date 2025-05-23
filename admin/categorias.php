<?php
require '../includes/config.php';
require '../includes/auth.php';

$auth = new Auth();

if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$db = (new Database())->getConnection();

// Procesar acciones: crear, editar, eliminar
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Crear nueva categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    $nombre = trim($_POST['nombre']);
    
    if (!empty($nombre)) {
        try {
            $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            $message = "Categoría creada exitosamente!";
        } catch (PDOException $e) {
            $error = "Error al crear categoría: " . $e->getMessage();
        }
    } else {
        $error = "El nombre de la categoría no puede estar vacío";
    }
}

// Editar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_categoria'])) {
    $nombre = trim($_POST['nombre']);
    $id = intval($_POST['id']);
    
    if (!empty($nombre) && $id > 0) {
        try {
            $stmt = $db->prepare("UPDATE categorias SET nombre = ? WHERE id = ?");
            $stmt->execute([$nombre, $id]);
            $message = "Categoría actualizada exitosamente!";
        } catch (PDOException $e) {
            $error = "Error al actualizar categoría: " . $e->getMessage();
        }
    } else {
        $error = "Datos inválidos para actualizar categoría";
    }
}

// Eliminar categoría
if ($action === 'delete' && $id > 0) {
    try {
        // Verificar si hay vacantes asociadas
        $stmt = $db->prepare("SELECT COUNT(*) FROM vacantes WHERE categoria_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "No se puede eliminar la categoría porque tiene vacantes asociadas";
        } else {
            $stmt = $db->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Categoría eliminada exitosamente!";
        }
    } catch (PDOException $e) {
        $error = "Error al eliminar categoría: " . $e->getMessage();
    }
}

// Obtener todas las categorías
$stmt = $db->query("SELECT * FROM categorias ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener categoría para editar
$categoria_editar = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM categorias WHERE id = ?");
    $stmt->execute([$id]);
    $categoria_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gestión de Categorías</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= $message ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Formulario para crear/editar categoría -->
                <div class="col-md-5 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><?= $categoria_editar ? 'Editar Categoría' : 'Nueva Categoría' ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if ($categoria_editar): ?>
                                    <input type="hidden" name="id" value="<?= $categoria_editar['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre de la categoría</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= $categoria_editar ? htmlspecialchars($categoria_editar['nombre']) : '' ?>" required>
                                </div>
                                
                                <button type="submit" name="<?= $categoria_editar ? 'editar_categoria' : 'crear_categoria' ?>" 
                                        class="btn btn-primary">
                                    <?= $categoria_editar ? 'Actualizar' : 'Crear' ?>
                                </button>
                                
                                <?php if ($categoria_editar): ?>
                                    <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Listado de categorías -->
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h5>Listado de Categorías</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categorias)): ?>
                                <div class="alert alert-info">No hay categorías registradas</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <tr>
                                                    <td><?= $categoria['id'] ?></td>
                                                    <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                                                    <td>
                                                        <a href="categorias.php?action=edit&id=<?= $categoria['id'] ?>" 
                                                           class="btn btn-sm btn-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="categorias.php?action=delete&id=<?= $categoria['id'] ?>" 
                                                           class="btn btn-sm btn-danger" title="Eliminar"
                                                           onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>